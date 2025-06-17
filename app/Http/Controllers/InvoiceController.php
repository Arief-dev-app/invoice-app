<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
    //    $invoices = Invoice::where('user_id', auth()->id())
    //         ->orderBy('created_at', 'desc')
    //         ->get();

        $search = $request->query('search');

        $invoices = collect([
            (object) [
                'id' => '1',
                'invoice_no' => 'INV-001',
                'trans_status' => '2',
                'user_id' => 1,
                'customer_name' => 'Budi Santoso',
                'total_harga' => 150000,
                'created_at' => Carbon::parse('2025-06-10'),
            ],
            (object) [
                'id' => '2',
                'invoice_no' => 'INV-002',
                'trans_status' => '1',
                'user_id' => 1,
                'customer_name' => 'Siti Aminah',
                'total_harga' => 220000,
                'created_at' => Carbon::parse('2025-06-11'),
            ],
            (object) [
                'id' => '3',
                'invoice_no' => 'INV-003',
                'trans_status' => '3',
                'user_id' => 1,
                'customer_name' => 'Andi Prasetyo',
                'total_harga' => 98000,
                'created_at' => Carbon::parse('2025-06-12'),
            ],
            (object) [
                'id' => '4',
                'invoice_no' => 'INV-004',
                'trans_status' => '3',
                'user_id' => 1,
                'customer_name' => 'Dewi Lestari',
                'total_harga' => 180000,
                'created_at' => Carbon::parse('2025-06-13'),
            ],
            (object) [
                'id' => '5',
                'invoice_no' => 'INV-005',
                'trans_status' => '3',
                'user_id' => 1,
                'customer_name' => 'Rudi Hartono',
                'total_harga' => 135000,
                'created_at' => Carbon::parse('2025-06-14'),
            ],
        ]);

        $products = collect([
             (object) ['id' => 1, 'nama' => 'Produk A', 'harga' => 10000],
             (object) ['id' => 2, 'nama' => 'Produk B', 'harga' => 15000],
             (object) ['id' => 3, 'nama' => 'Produk C', 'harga' => 20000],
             (object) ['id' => 4, 'nama' => 'Produk D', 'harga' => 25000],
             (object) ['id' => 5, 'nama' => 'Produk E', 'harga' => 30000],
             (object) ['id' => 6, 'nama' => 'Produk F', 'harga' => 35000],
             (object) ['id' => 7, 'nama' => 'Produk G', 'harga' => 40000],
             (object) ['id' => 8, 'nama' => 'Produk H', 'harga' => 45000],
             (object) ['id' => 9, 'nama' => 'Produk I', 'harga' => 50000],
             (object) ['id' => 10, 'nama' => 'Produk J', 'harga' => 55000],
        ]);

        return view('admin.invoice.index', compact('invoices','products','search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }
    
    public function print($id)
    {
        // $invoice = Invoice::with('items.product')->findOrFail($id);
        $invoice = collect([
            (object) [
                'id' => '1',
                'invoice_no' => 'INV-001',
                'trans_status' => '3',
                'user_id' => 1,
                'customer_name' => 'Budi Santoso',
                'total_harga' => 150000,
                'created_at' => Carbon::parse('2025-06-10'),
            ]
        ])->first();

        $pdf = Pdf::loadView('admin.invoice.print', compact('invoice'));
        return $pdf->stream('invoice-'.$invoice->invoice_no.'.pdf');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
