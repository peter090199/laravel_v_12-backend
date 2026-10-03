<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatabaseBackup extends Model
{
    protected $table = 'database_backups';

    protected $fillable = [
        'file_name',
        'file_path',
        'file_size',
        'type',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'file_size'  => 'integer',
        'created_by' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}