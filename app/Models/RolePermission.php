<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'rolepermissions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'access_right_id',
        'access_right_name',

        'menu_id',
        'menu_name',
        'menu_sort',

        'sub_menu_id',
        'sub_menu_name',
        'sub_menu_sort',

        'record_status',
    ];
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'id'               => 'integer',
        'access_right_id'  => 'integer',
        'menu_id'          => 'integer',
        'sub_menu_id'      => 'integer',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];
}