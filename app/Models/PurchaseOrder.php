<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_order';

    protected $fillable = ['purchase_no', 'transaction_date','supplier_id','po_status', 'total' ,'user_id'];

    public function detail()
    {
        return $this->hasMany(PurchaseOrderDetail::class, 'po_id');
    }
    public function suplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
