<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'qty',
        'harga_satuan',
        'subtotal',
    ];

    /**
     * Relasi ke invoice induk
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Relasi ke produk (opsional jika ada tabel produk)
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
