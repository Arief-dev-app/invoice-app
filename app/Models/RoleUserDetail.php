<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleUserDetail extends Model
{
    protected $table = 'role_user_details';

    protected $fillable = ['menu_id', 'role_user_id','can_view','can_create', 'can_update', 'can_delete'];

    public function roleUser()
    {
        return $this->belongsTo(RoleUser::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
