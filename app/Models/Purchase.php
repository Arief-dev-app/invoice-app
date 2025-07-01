<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $table = 'purchase';

    protected $fillable = ['trans_no', 'transaction_date','po_id','supplier_id','trans_status', 'total' ,'user_id'];
    
    public function detail()
    {
        return $this->hasMany(PurchaseDetail::class, 'trans_id');
    }
    public function suplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
    public function purchase()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
