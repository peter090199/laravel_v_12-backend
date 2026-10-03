<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessRight extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'accessrights';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'access_right_name',
        'record_status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'id'         => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}