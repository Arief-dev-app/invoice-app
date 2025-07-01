<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturPurchase extends Model
{
    protected $table = 'retur_purchase';

    protected $fillable = ['trans_no', 'transaction_date','purchase_id','supplier_id','trans_status', 'total' ,'user_id'];
    
    public function detail()
    {
        return $this->hasMany(ReturPurchaseDetail::class, 'trans_id');
    }
    public function suplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
