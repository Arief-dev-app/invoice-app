<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    protected $table = 'purchase_detail';

    protected $fillable = ['trans_id', 'prd_id', 'harga','qty' ,'user_id'];

    public function header()
    {
        return $this->belongsTo(Purchase::class);
    }
    public function prd()
    {
        return $this->belongsTo(Product::class, 'prd_id');
    }
}
