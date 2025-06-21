<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
    protected $table = 'purchase_order_detail';

    protected $fillable = ['po_id', 'prd_id','harga_pcs','harga_total', 'qty' ,'user_id'];

    public function header()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
