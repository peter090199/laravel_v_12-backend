<?php

namespace App\Http\Controllers\Api\SharedRoutine;
use App\Models\ControlNumber;
use Illuminate\Support\Facades\DB;

class ControlNumberRoutine
{
    public function next(string $code, ?string $prefix = null, int $padding = 6): string
    {
        return DB::transaction(function () use ($code, $prefix, $padding) {
            $row = ControlNumber::where('code', $code)->lockForUpdate()->first()
                ?? ControlNumber::create([
                    'code'        => $code,
                    'prefix'      => $prefix,
                    'padding'     => $padding,
                    'last_number' => 0,
                ]);

            $row->increment('last_number');

            return $this->format($row);
        });
    }

    /** What the next number will be, without reserving it */
    public function preview(string $code, ?string $prefix = null, int $padding = 6): string
    {
        $row = ControlNumber::where('code', $code)->first();

        $next = new ControlNumber([
            'code'        => $code,
            'prefix'      => $row->prefix  ?? $prefix,
            'padding'     => $row->padding ?? $padding,
            'last_number' => ($row->last_number ?? 0) + 1,
        ]);

        return $this->format($next);
    }

    public function current(string $code): ?string
    {
        $row = ControlNumber::where('code', $code)->first();

        return $row ? $this->format($row) : null;
    }

    public function set(string $code, int $lastNumber): ControlNumber
    {
        return ControlNumber::updateOrCreate(
            ['code' => $code],
            ['last_number' => $lastNumber]
        );
    }

    private function format(ControlNumber $row): string
    {
        return $row->prefix . str_pad($row->last_number, $row->padding, '0', STR_PAD_LEFT);
    }
}