<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $table = 'transaksi_penjualan';

    protected $fillable = ['trans_no', 'transaction_date','customer_id','trans_status', 'total' ,'user_id'];
    
    public function detail()
    {
        return $this->hasMany(PenjualanDetail::class, 'trans_id');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
