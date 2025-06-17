<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupMenuPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_user_id',
        'menu_id',
        'can_view',
        'can_create',
        'can_edit',
        'can_delete'
    ];

    public function group()
    {
        return $this->belongsTo(GroupUser::class, 'group_user_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
