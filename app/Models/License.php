<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $table = 'licenses';

    protected $fillable = ['license_key', 'company_name', 'email', 'activated_at', 'expires_at'];

    protected $casts = [
        'activated_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    /** The active (activated and not expired) license, if any. */
    public static function current(): ?self
    {
        return static::whereNotNull('activated_at')
            ->whereNotNull('license_key')
            ->where('license_key', '!=', '')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest('activated_at')
            ->first();
    }
}
