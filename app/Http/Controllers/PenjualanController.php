<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Product;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use Illuminate\Pagination\Paginator;
use App\Http\Controllers\BaseController;

use Barryvdh\DomPDF\Facade\Pdf;

class PenjualanController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function boot()
    {
        Paginator::useTailwind();
    }

    public function data(){
        return [
            'url' => '/transaksi-penjualan',
            'title' => 'Transaksi Penjualan',
            'kode' => 'MENU01-2'
        ];
    }
    public function index(Request $request)
    {
        $search   = $request->query('search');
        $perPage  = $request->query('per_page', 10); // default 10 jika tidak ada
        
        $penjualan = Penjualan::with('customer')
            ->where('user_id', auth()->id()) // tetap filter berdasarkan user
            ->when($search, function ($query, $search) {
                return $query->where('trans_no', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage)
            ->appends(['search' => $search, 'per_page' => $perPage]); // agar pagination tetap bawa query string
        
        if ($request->wantsJson()) {
            return response()->json($penjualan);
        }

        $products = Product::where('user_id', auth()->id())
        ->select('id', 'nama', 'harga_jual')
        ->get();

        
        $customer = Customer::all();

        $menus = $this->getAccessibleMenus();
        $default = $this->data();

        return view('admin.transaksi.transaksi-penjualan.index', array_merge($default, [
            'penjualan'     => $penjualan, 
            'search'       => $search, 
            'menu_header'  => $menus['menu_header'],
            'menu_detail'  => $menus['menu_detail'],
            'products'  => $products,
            'customers'  => $customer,
        ]));
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
        $request->validate([
            'trans_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.harga_jual' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            $penjualanNo = $this->generateAutoNumber(Penjualan::class, 'trans_no', 'TRS');

            $penjualan = Penjualan::create([
                'trans_no' => $penjualanNo,
                'transaction_date' => $request->trans_date,
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id()
            ]);

            $items = $request->input('items', []);

            $totalQty = 0;

            foreach ($items as $perm) {
                PenjualanDetail::create([
                    'trans_id'   => $penjualan->id,
                    'prd_id'  => $perm['product_id'],
                    'harga_jual'     => $perm['harga_jual'],
                    'qty'     => $perm['qty'],
                    'total'     => $perm['total'],
                    'user_id' => auth()->id()
                ]);

                $totalQty += (int) $perm['harga_jual'];
            }

            $penjualan->update([
                'total' => $totalQty
            ]);


            DB::commit();

            return response()->json(['message' => 'Data berhasil disimpan.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Gagal simpan role user: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data.',
                'error_detail' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $penjualan = Penjualan::with('customer','detail.prd')->findOrFail($id);
        // dd($penjualan);
        return response()->json($penjualan);  
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $penjualan = Penjualan::with('customer','detail.prd')->findOrFail($id);

        return response()->json($penjualan); 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'trans_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.harga_jual' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            $penjualan = Penjualan::findOrFail($id); // pastikan $id adalah ID penjualan yang akan diupdate

            // Update data utama penjualan
            $penjualan->update([
                'transaction_date' => $request->trans_date,
                'customer_id'      => $request->customer_id,
                'user_id'          => auth()->id(),
            ]);

            // Hapus detail lama
            PenjualanDetail::where('trans_id', $penjualan->id)->delete();

            // Ambil data baru dari form
            $items = $request->input('items', []);
            $totalQty = 0;

            foreach ($items as $perm) {
                PenjualanDetail::create([
                    'trans_id'      => $penjualan->id,
                    'prd_id'        => $perm['product_id'],
                    'harga_jual'    => $perm['harga_jual'],
                    'qty'           => $perm['qty'],
                    'total'         => $perm['total'],
                    'user_id'       => auth()->id()
                ]);

                $totalQty += (int) $perm['harga_jual'];
            }

            // Update total qty ke kolom `total`
            $penjualan->update([
                'total' => $totalQty
            ]);


            DB::commit();

            return response()->json(['message' => 'Data berhasil diupdate.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Gagal simpan role user: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data.',
                'error_detail' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
    
            $penjualan = Penjualan::findOrFail($id);
       
            $penjualan->delete();
    
            DB::commit();
    
            return response()->json([
                'message' => 'Data berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus data.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function cekAkses(Request $request)
    {

        try {
            $this->cekAccessMenu($request);
    
            return response()->json([
                'status' => 'ok',
                'message' => 'Akses diperbolehkan',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    public function searchPo($id)
    {
        $penjualan = Penjualan::with(['detail.prd', 'customers'])->find($id);

        if (!$penjualan) {
            return response()->json(['message' => 'Purchase Order tidak ditemukan'], 404);
        }

        return response()->json([
            'customer_id' => $penjualan->customer_id,
            'detail' => $penjualan->detail
        ]);
    }

    public function confirm($id)
    {
        try {
            $penjualan = Penjualan::findOrFail($id);
            
            
            // update stock ke product
            $details = PenjualanDetail::where('trans_id', $penjualan->id)->get();

            foreach ($details as $value) {
                $prd = Product::where('id', $value->prd_id)->first();
                if ($prd) {
                    $oldStock = $prd->stock;
                    $newQty = $value->qty;
            
                    // Update stok dan harga
                    $newStock = $oldStock - $newQty;
            
                    $prd->fill([
                        'stock' => $newStock,
                    ])->save();
                }
            }
            

            $penjualan->trans_status = 3;
            $penjualan->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil dikonfirmasi.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengonfirmasi transaksi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function printPdf($id)
    {
        $transaksi = Penjualan::with(['customer', 'detail.prd'])->findOrFail($id);

        $filename = 'invoice_' . str_replace(['/', '\\'], '-', $transaksi->trans_no) . '.pdf';
    
        $pdf = Pdf::loadView('admin.transaksi.transaksi-penjualan.print', compact('transaksi'));
    
        return $pdf->stream($filename, ['Attachment' => false]);
    }
}
