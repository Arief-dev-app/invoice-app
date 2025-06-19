<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\User;
use App\Models\RoleUser;
use App\Models\RoleUserDetail;
use App\Models\GroupUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $menus = Menu::with('children')->whereNull('parent_id')->get();

        $group = GroupUser::all();
        $roles = RoleUser::all();
        $users = User::all();

        return view('admin.user.index', compact('users','roles','group','menus', 'search'));
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
            $User = User::create([
                'name' => $request->username,
                'email' => $request->email,
                'group_id' => $request->user_group_id,
                'password' => Hash::make($request->password),
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $user = User::with(['group'])->findOrFail($id);
        $mode = $request->query('mode', 'edit');


        return response()->json([
            'menus' => [
                'id' => $user->id,
                'group_user_id' => $user->group_id,
                'username' => $user->name,
                'email' => $user->email,
            ],
            'disabled' => $mode
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();

        try {
            $role = User::findOrFail($id);

            // Update data utama
            $role->update([
                'name' => $request->username,
                'email' => $request->email,
                'group_id' => $request->user_group_id,
                'password' => Hash::make($request->password),
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
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();
    
            $user = User::findOrFail($id);

            $user->delete();
    
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
