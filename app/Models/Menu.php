<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends Model
{
    use HasFactory;
    protected $table = 'menus';

    protected $fillable = [
        'id',
        'menu_name',
        'menu_sort',
        'menu_routes',
        'record_status',
        'enterprise',
        'standard',
        'express',
        'project_enterprise',
        'project_standard',
        'project_express',
    ];
}
