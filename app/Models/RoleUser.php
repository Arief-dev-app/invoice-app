<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    protected $table = 'role_user';

    protected $fillable = ['name'];

    public function details()
    {
        return $this->hasMany(RoleUserDetail::class);
    }
}
