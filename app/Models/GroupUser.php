<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupUser extends Model
{
    use HasFactory;

    protected $fillable = ['name','description'];

    public function users()
    {
        return $this->hasMany(User::class, 'group_user_id');
    }

    public function menuPermissions()
    {
        return $this->hasMany(GroupMenuPermission::class, 'group_user_id');
    }
}
