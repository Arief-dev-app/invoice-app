<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\RoleUser;
use App\Models\RoleUserDetail;
use App\Models\GroupUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 

class RoleUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $menus = Menu::with('children')->whereNull('parent_id')->get();

        $menu_header = Menu::where('header_id', 1)->get();
        $menu_detail = Menu::whereNotNull('parent_id')->get();

        $group = GroupUser::all();
        $roles = RoleUser::all();

        return view('admin.role-user.index', compact('menu_header','menu_detail','roles','group','menus', 'search'));
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
            $roleUser = RoleUser::create([
                'group_id' => $request->user_group_id
            ]);

            // Simpan detail permission
            foreach ($permissions as $menuId => $perm) {
                RoleUserDetail::create([
                    'role_user_id' => $roleUser->id,
                    'menu_id'      => $menuId,
                    'can_create'   => !empty($perm['can_create']),
                    'can_view'     => !empty($perm['can_view']),
                    'can_update'   => !empty($perm['can_update']),
                    'can_delete'   => !empty($perm['can_delete']),
                ]);
            }

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
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $role = RoleUser::with(['groupUser', 'details.menu'])->findOrFail($id);
        $mode = $request->query('mode', 'edit');

        $structuredDetails = $role->details->map(function ($detail) {
            return [
                'menu_id' => $detail->menu_id,
                'menu_name' => $detail->menu->name ?? '',
                'can_create' => (bool) $detail->can_create,
                'can_view' => (bool) $detail->can_view,
                'can_update' => (bool) $detail->can_update,
                'can_delete' => (bool) $detail->can_delete,
            ];
        });

        return response()->json([
            'menus' => [
                'id' => $role->id,
                'group_user_id' => $role->group_id,
                'menu_id' => 1,
                'details' => $structuredDetails,
            ],
            'disabled' => $mode
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        DB::beginTransaction();

        try {
            $role = RoleUser::findOrFail($id);

            // Update data utama
            $role->update([
                'group_user_id' => $request->user_group_id,
            ]);

            // Hapus dulu semua permission detail lama
            RoleUserDetail::where('role_user_id', $role->id)->delete();

            // Simpan ulang semua permission baru
            foreach ($request->permissions as $menuId => $perm) {
                RoleUserDetail::create([
                    'role_user_id' => $role->id,
                    'menu_id'      => $menuId,
                    'can_create'   => !empty($perm['can_create']),
                    'can_view'     => !empty($perm['can_view']),
                    'can_update'   => !empty($perm['can_update']),
                    'can_delete'   => !empty($perm['can_delete']),
                ]);
            }

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
        try {
            DB::beginTransaction();
    
            $role = RoleUser::findOrFail($id);
    
            // Hapus detail terkait
            $role->details()->delete(); // asumsi relasi: $this->hasMany(RoleUserDetail::class, 'role_user_id')
    
            // Hapus role
            $role->delete();
    
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

    public function getMenusByGroup($id)
    {
    
        $menus = Menu::where('parent_id',$id)->get();

        return response()->json($menus);
    }
}
