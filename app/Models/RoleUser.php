<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    protected $table = 'role_user';

    protected $fillable = ['group_id'];

    public function groupUser()
    {
        return $this->belongsTo(GroupUser::class, 'group_id');
    }

    public function details()
    {
        return $this->hasMany(RoleUserDetail::class);
    }
}
