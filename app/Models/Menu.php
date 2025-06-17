<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menu';

    protected $fillable = ['name', 'slug', 'url'];

    public function permissions()
    {
        return $this->hasMany(GroupMenuPermission::class);
    }
}
