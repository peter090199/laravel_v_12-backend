<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\SharedRoutine\BaseController;
use App\Models\DatabaseBackup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;

class DatabaseBackupController extends BaseController
{
    // GET /api/auth/database/backups
    public function index(): JsonResponse
    {
        try {
            $rows = DatabaseBackup::with('creator:id,username')
                ->orderByDesc('id')
                ->get()
                ->map(function (DatabaseBackup $b) {
                    $data = $b->toArray();
                    $data['created_by_name'] = $b->creator?->username;
                    unset($data['creator'], $data['file_path']);
                    return $data;
                })
                ->values();

            return $this->getResponse($rows, 'Backups retrieved successfully');
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    // POST /api/auth/database/backup
    public function backup(Request $request): JsonResponse
    {
        $request->validate(['notes' => ['nullable', 'string', 'max:255']]);

        try {
            $record = $this->createBackup('backup', $request->input('notes'));

            return $this->getResponse($record, 'Backup created successfully');
        } catch (Throwable $e) {
            return $this->sendServerError($e, 'Backup failed: ' . $e->getMessage());
        }
    }

    // POST /api/auth/database/import  (multipart: file, notes)
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file'  => ['required', 'file', 'extensions:sql', 'max:' . config('backup.max_upload_kb')],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            // Safety copy of the current database before it is overwritten
            $this->createBackup('auto', 'Automatic backup before import');

            $file = $request->file('file');
            $name = 'import_' . now()->format('Ymd_His') . '.sql';
            $path = $file->storeAs(config('backup.directory'), $name, 'local');

            if (!$path) {
                throw new RuntimeException('Could not save the uploaded file.');
            }

            $record = DatabaseBackup::create([
                'file_name'  => $file->getClientOriginalName(),
                'file_path'  => $path,
                'file_size'  => $file->getSize(),
                'type'       => 'import',
                'status'     => 'failed',
                'notes'      => $request->input('notes'),
                'created_by' => Auth::id(),
            ]);

            $this->runImport($path);

            $record->update(['status' => 'completed']);

            return $this->getResponse($record, 'Database imported successfully');
        } catch (Throwable $e) {
            return $this->sendServerError($e, 'Import failed: ' . $e->getMessage());
        }
    }

    // POST /api/auth/database/backups/{id}/restore
    public function restore(string $id): JsonResponse
    {
        $backup = DatabaseBackup::find($id);

        if (!$backup || !Storage::disk('local')->exists($backup->file_path)) {
            return $this->sendNotFound('Backup file not found');
        }

        try {
            $this->createBackup('auto', 'Automatic backup before restore');
            $this->runImport($backup->file_path);

            return $this->getResponse(null, 'Database restored successfully');
        } catch (Throwable $e) {
            return $this->sendServerError($e, 'Restore failed: ' . $e->getMessage());
        }
    }

    // GET /api/auth/database/backups/{id}/download
    public function download(string $id): BinaryFileResponse|JsonResponse
    {
        $backup = DatabaseBackup::find($id);

        if (!$backup || !Storage::disk('local')->exists($backup->file_path)) {
            return $this->sendNotFound('Backup file not found');
        }

        return response()->download(
            $this->absolutePath($backup->file_path),
            $backup->file_name
        );
    }

    // DELETE /api/auth/database/backups/{id}
    public function destroy(string $id): JsonResponse
    {
        $backup = DatabaseBackup::find($id);

        if (!$backup) {
            return $this->sendNotFound('Backup not found');
        }

        try {
            Storage::disk('local')->delete($backup->file_path);
            $backup->delete();

            return $this->sendMessage('Backup deleted successfully');
        } catch (Throwable $e) {
            return $this->sendServerError($e);
        }
    }

    /* ---------------- Helpers ---------------- */

    /** Real path on disk, whatever the Laravel version's "local" disk root is */
    private function absolutePath(string $relative): string
    {
        return Storage::disk('local')->path($relative);
    }

    private function connection(): array
    {
        $conn = config('database.connections.' . config('database.default'));

        return [
            'host'     => $conn['host'] ?? '127.0.0.1',
            'port'     => (string) ($conn['port'] ?? '3306'),
            'username' => $conn['username'] ?? 'root',
            'password' => (string) ($conn['password'] ?? ''),
            'database' => $conn['database'],
        ];
    }

    /**
     * Finds the mysqldump / mysql executable and fails with a clear message.
     * $key is "dump_binary" or "client_binary".
     */
    private function binary(string $key): string
    {
        $bin = (string) config("backup.{$key}");
        $env = $key === 'dump_binary' ? 'DB_DUMP_BINARY' : 'DB_CLIENT_BINARY';

        // Full path given: it must exist
        if (preg_match('#[\\\\/]#', $bin)) {
            if (!is_file($bin)) {
                throw new RuntimeException("Executable not found at: {$bin}. Check {$env} in .env, then run php artisan config:clear.");
            }
            return $bin;
        }

        // Plain name: look it up on the PATH
        $found = (new ExecutableFinder())->find($bin);

        if (!$found) {
            throw new RuntimeException("'{$bin}' was not found on the PATH. Set the full path in {$env} in .env, then run php artisan config:clear.");
        }

        return $found;
    }

    private function createBackup(string $type, ?string $notes = null): DatabaseBackup
    {
        $conn     = $this->connection();
        $dump     = $this->binary('dump_binary');
        $fileName = $conn['database'] . '_' . now()->format('Ymd_His') . '.sql';
        $relative = config('backup.directory') . '/' . $fileName;
        $full     = $this->absolutePath($relative);

        File::ensureDirectoryExists(dirname($full));

        $process = new Process([
            $dump,
            '--host=' . $conn['host'],
            '--port=' . $conn['port'],
            '--user=' . $conn['username'],
            '--single-transaction',
            '--routines',
            '--triggers',
            '--no-tablespaces',
            '--default-character-set=utf8mb4',
            // Keep the list of backups out of the dump, so a restore doesn't erase it
            '--ignore-table=' . $conn['database'] . '.database_backups',
            // Let mysqldump write the file itself (safest on Windows)
            '--result-file=' . $full,
            $conn['database'],
        ], null, ['MYSQL_PWD' => $conn['password']]);

        $process->setTimeout(null);
        $process->run();

        if (!$process->isSuccessful() || !is_file($full)) {
            File::delete($full);
            throw new RuntimeException(trim($process->getErrorOutput()) ?: 'mysqldump failed');
        }

        return DatabaseBackup::create([
            'file_name'  => $fileName,
            'file_path'  => $relative,
            'file_size'  => filesize($full),
            'type'       => $type,
            'status'     => 'completed',
            'notes'      => $notes,
            'created_by' => Auth::id(),
        ]);
    }

    private function runImport(string $relative): void
    {
        $conn   = $this->connection();
        $client = $this->binary('client_binary');
        $path   = $this->absolutePath($relative);

        $input = fopen($path, 'rb');

        if ($input === false) {
            throw new RuntimeException("Cannot read the SQL file: {$path}");
        }

        try {
            $process = new Process([
                $client,
                '--host=' . $conn['host'],
                '--port=' . $conn['port'],
                '--user=' . $conn['username'],
                '--default-character-set=utf8mb4',
                $conn['database'],
            ], null, ['MYSQL_PWD' => $conn['password']]);

            $process->setTimeout(null);
            $process->setInput($input);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new RuntimeException(trim($process->getErrorOutput()) ?: 'mysql import failed');
            }
        } finally {
            fclose($input);
        }
    }
}