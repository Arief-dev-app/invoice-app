<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    protected $table = 'transaksi_penjualan_detail';

    protected $fillable = ['trans_id', 'prd_id', 'harga','qty' ,'user_id'];

    public function header()
    {
        return $this->belongsTo(Penjualan::class, 'trans_id');
    }
    public function prd()
    {
        return $this->belongsTo(Product::class, 'prd_id');
    }
}
