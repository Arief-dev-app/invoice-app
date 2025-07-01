<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\ReturPurchase;
use App\Models\ReturPurchaseDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use Illuminate\Pagination\Paginator;
use App\Http\Controllers\BaseController;

class ReturPurchaseController extends BaseController
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
            'url' => '/retur-pembelian',
            'title' => 'Retur Pembelian',
            'kode' => 'MENU01-2'
        ];
    }

    public function index(Request $request)
    {
        $search   = $request->query('search');
        $perPage  = $request->query('per_page', 10); // default 10 jika tidak ada
        
        $purchase = ReturPurchase::with('suplier', 'purchase')
            ->where('user_id', auth()->id()) // tetap filter berdasarkan user
            ->when($search, function ($query, $search) {
                return $query->where('nama', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage)
            ->appends(['search' => $search, 'per_page' => $perPage]); // agar pagination tetap bawa query string
        
        if ($request->wantsJson()) {
            return response()->json($purchase);
        }

        $products = Product::where('user_id', auth()->id())
        ->select('id', 'nama', 'stock')
        ->get();

        $pos = Purchase::where('trans_status', 3)->get();
        $suppliers = Supplier::all();

        $menus = $this->getAccessibleMenus();
        $default = $this->data();

        return view('admin.transaksi.retur-pembelian.index', array_merge($default, [
            'purchase'     => $purchase, 
            'search'       => $search, 
            'menu_header'  => $menus['menu_header'],
            'menu_detail'  => $menus['menu_detail'],
            'products'  => $products,
            'suppliers'  => $suppliers,
            'pos'  => $pos,
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
            'supplier_id' => 'required|exists:suppliers,id',
            'po_id' => 'nullable|exists:purchase_order,id',
            'items' => 'required|array|min:1',
            'items.*.qty' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();

        try {

            $purchaseNo = $this->generateAutoNumber(ReturPurchase::class, 'trans_no', 'RPC');

            $po = ReturPurchase::create([
                'trans_no' => $purchaseNo,
                'purchase_id' => $request->po_id,
                'transaction_date' => $request->trans_date,
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id()
            ]);

            $items = $request->input('items', []);

            $totalQty = 0;

            foreach ($items as $perm) {
                ReturPurchaseDetail::create([
                    'trans_id'   => $po->id,
                    'prd_id'  => $perm['product_id'],
                    'qty'     => $perm['qty'],                  
                    'user_id' => auth()->id()
                ]);

                $totalQty += (int) $perm['qty'];
            }

            $po->update([
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
        $purchase = ReturPurchase::with('detail.prd')->findOrFail($id);

        return response()->json($purchase);  
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $purchase = ReturPurchase::with('detail.prd')->findOrFail($id);

        return response()->json($purchase);  
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'trans_date' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'po_id' => 'nullable|exists:purchase_order,id',
            'items' => 'required|array|min:1',
            'items.*.qty' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();

        try {

            $po = ReturPurchase::findOrFail($id); // pastikan $id adalah ID PO yang akan diupdate

            // Update data utama PO
            $po->update([
                'transaction_date' => $request->trans_date,
                'supplier_id'      => $request->supplier_id,
                'purchase_id'             => $request->po_id,
                'user_id'          => auth()->id(),
            ]);

            // Hapus detail lama
            ReturPurchaseDetail::where('trans_id', $po->id)->delete();

            // Ambil data baru dari form
            $items = $request->input('items', []);
            $totalQty = 0;

            foreach ($items as $perm) {
                ReturPurchaseDetail::create([
                    'trans_id'   => $po->id,
                    'prd_id'  => $perm['product_id'],
                    'qty'     => $perm['qty'],
                    'user_id' => auth()->id()
                ]);

                $totalQty += (int) $perm['qty'];
            }

            // Update total qty ke kolom `total`
            $po->update([
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
    
            $po = ReturPurchase::findOrFail($id);
       
            $po->delete();
    
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
        $po = Purchase::with(['detail.prd', 'suplier'])->find($id);

        if (!$po) {
            return response()->json(['message' => 'Purchase Order tidak ditemukan'], 404);
        }

        return response()->json([
            'supplier_id' => $po->supplier_id,
            'detail' => $po->detail
        ]);
    }

    public function confirm($id)
    {
        try {
            $purchase = ReturPurchase::findOrFail($id);
            
            
            // update stock ke product
            $details = ReturPurchaseDetail::where('trans_id', $purchase->id)->get();

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
            

            $purchase->trans_status = 3;
            $purchase->save();

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
}
