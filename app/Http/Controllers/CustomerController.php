<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Menu;
use App\Models\User;
use App\Models\RoleUser;
use App\Models\RoleUserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use Illuminate\Pagination\Paginator;
use App\Http\Controllers\BaseController;

class CustomerController extends BaseController
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
        
        $customer = Customer::query()
            ->where('user_id', auth()->id()) // tetap filter berdasarkan user
            ->when($search, function ($query, $search) {
                return $query->where('nama', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage)
            ->appends(['search' => $search, 'per_page' => $perPage]); // agar pagination tetap bawa query string
        
        if ($request->wantsJson()) {
            return response()->json($customer);
        }


        $menus = $this->getAccessibleMenus();
        $default = $this->data();

        return view('admin.customer.index', array_merge($default, [
            'customer'     => $customer, 
            'search'       => $search, 
            'menu_header'  => $menus['menu_header'],
            'menu_detail'  => $menus['menu_detail'],
        ]));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // 
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $permissions = $request->input('permissions', []);

        DB::beginTransaction();

        try {
            // Simpan RoleUser utama
            $customer = Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
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
        $customer = Customer::findOrFail($id);
        return response()->json($customer);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $customer = Customer::findOrFail($id);
        return response()->json($customer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $permissions = $request->input('permissions', []);

        DB::beginTransaction();

        try {
            $customer = Customer::findOrFail($id);

            // Update data utama
            $customer->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return response()->json(['message' => 'Data berhasil diubah.'], 200);

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
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
    
            $customer = Customer::findOrFail($id);
       
            $customer->delete();
    
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
