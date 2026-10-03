<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubMenu extends Model
{
    protected $table = 'submenus';

    protected $fillable = [
        'menu_id',
        'menu_name',
        'sub_menu_name',
        'sub_menu_routes',
        'icon',
        'sub_menu_sort',
        'record_status',
    ];

    protected $casts = [
        'menu_id'       => 'integer',
        'sub_menu_sort' => 'integer',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}