<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use Illuminate\Pagination\Paginator;
use App\Http\Controllers\BaseController;

class PurchaseOrderController extends BaseController
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
            'url' => '/purchase-order',
            'title' => 'Purchase Order',
            'kode' => 'MENU01-2'
        ];
    }

    public function index(Request $request)
    {
        $search   = $request->query('search');
        $perPage  = $request->query('per_page', 10); // default 10 jika tidak ada
        
        $purchase_order = PurchaseOrder::with('suplier')
            ->where('user_id', auth()->id()) // tetap filter berdasarkan user
            ->when($search, function ($query, $search) {
                return $query->where('nama', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage)
            ->appends(['search' => $search, 'per_page' => $perPage]); // agar pagination tetap bawa query string
        
        if ($request->wantsJson()) {
            return response()->json($purchase_order);
        }

        $products = Product::where('user_id', auth()->id())
        ->select('id', 'nama', 'stock')
        ->get();

        $suppliers = Supplier::all();

        $menus = $this->getAccessibleMenus();
        $default = $this->data();

        return view('admin.transaksi.purchase-order.index', array_merge($default, [
            'purchase_order'     => $purchase_order, 
            'search'       => $search, 
            'menu_header'  => $menus['menu_header'],
            'menu_detail'  => $menus['menu_detail'],
            'products'  => $products,
            'suppliers'  => $suppliers,
        ]));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request)
    {
        $permissions = $request->input('permissions', []);

        DB::beginTransaction();

        try {

            $purchaseNo = $this->generateAutoNumber(PurchaseOrder::class, 'purchase_no', 'PO');

            $po = PurchaseOrder::create([
                'purchase_no' => $purchaseNo,
                'transaction_date' => $request->trans_date,
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id()
            ]);

            $items = $request->input('items', []);

            $totalQty = 0;

            foreach ($items as $perm) {
                PurchaseOrderDetail::create([
                    'po_id'   => $po->id,
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
        $purchase_order = PurchaseOrder::with('detail.prd')->findOrFail($id);

        return response()->json($purchase_order);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $purchase_order = PurchaseOrder::with('detail.prd')->findOrFail($id);

        return response()->json($purchase_order);  
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $permissions = $request->input('permissions', []);

        DB::beginTransaction();

        try {

            $po = PurchaseOrder::findOrFail($id); // pastikan $id adalah ID PO yang akan diupdate

            // Update data utama PO
            $po->update([
                'transaction_date' => $request->trans_date,
                'supplier_id'      => $request->supplier_id,
                'user_id'          => auth()->id(),
            ]);

            // Hapus detail lama
            PurchaseOrderDetail::where('po_id', $po->id)->delete();

            // Ambil data baru dari form
            $items = $request->input('items', []);
            $totalQty = 0;

            // Simpan ulang detail & hitung total qty
            foreach ($items as $perm) {
                PurchaseOrderDetail::create([
                    'po_id'   => $po->id,
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
    
            $po = PurchaseOrder::findOrFail($id);
       
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
    public function confirm($id)
    {
        try {
            $purchase = PurchaseOrder::findOrFail($id);

            $purchase->po_status = 3;
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
