<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use Illuminate\Pagination\Paginator;

class ProductController extends BaseController
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
            'url' => '/customer',
            'title' => 'Customer',
            'kode' => 'MENU01-2'
        ];
    }

    public function index(Request $request)
    {
        $search   = $request->query('search');
        $perPage  = $request->query('per_page', 10); // default 10 jika tidak ada
    
        $products = Product::query()
            ->where('user_id', auth()->id()) // tetap filter berdasarkan user
            ->when($search, function ($query, $search) {
                return $query->where('nama', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage)
            ->appends(['search' => $search, 'per_page' => $perPage]); // agar pagination tetap bawa query string
    
        if ($request->wantsJson()) {
            return response()->json($products);
        }
    
        
        $menus = $this->getAccessibleMenus();
        $default = $this->data();
    
        return view('admin.product.index',  array_merge($default, [
            'products', 
            'search', 
            'menu_header'  => $menus['menu_header'],
            'menu_detail'  => $menus['menu_detail'],
        ]));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [];

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $permissions = $request->input('permissions', []);

        DB::beginTransaction();

        try {
            // Simpan RoleUser utama
            $product = Product::create([
                'nama' => $request->nama,
                'harga_jual' => $request->harga_jual,
                'harga_beli' => $request->harga_beli,
                'stock' => 0,
                'user_id' => auth()->id()
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
    public function show(string $id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $product = Product::findOrFail($id);

            // Update data utama
            $product->update([
                'nama' => $request->nama,
                'harga_jual' => $request->harga_jual,
                'harga_beli' => $request->harga_beli,
                'user_id' => auth()->id()
            ]);
            

            DB::commit();

            return response()->json(['message' => 'Data berhasil diperbarui.']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
    
            $product = Product::findOrFail($id);
       
            $product->delete();
    
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
}
