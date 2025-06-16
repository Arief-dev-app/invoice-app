<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'user_id',
        'customer_name',
        'total_harga',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public static function generateInvoiceNo()
    {
        $last = self::orderBy('id', 'desc')->first();
        $number = $last ? ((int) substr($last->invoice_no, 3)) + 1 : 1;
        return 'INV' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
