<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
     use HasFactory;

    protected $fillable = [
        'nama_usaha',
        'nama_person',
        'alamat',
        'no_hp',
        'email',
    ];
}
