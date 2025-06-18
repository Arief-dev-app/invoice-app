<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
    public function index()
    {
        $menus = Menu::with('children')->whereNull('parent_id')->get();

        return view('admin.menu.index', compact('menus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [
            'menus' => [
                'id' => null,
                'name' => '',
                'slug' => '',
                'code' => '',
                'seq' => 1,
            ],
            'disabled' => ''
        ];

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
            ]);

            $parentItem = $request->items[0];
        

            $parent = Menu::create([
                'name' => $parentItem['name'],
                'seq' => $parentItem['seq'],
                'code' => $parentItem['code'],
                'slug' => $parentItem['slug'],
                'parent_id' => null,
            ]);

            $items = $request->items;
        
            for ($i = 1; $i < count($items); $i++) {
                Menu::create([
                    'name' => $items[$i]['name'],
                    'seq' => $items[$i]['seq'],
                    'code' => $items[$i]['code'],
                    'slug' => $items[$i]['slug'],
                    'parent_id' => $parent->id, // mengacu pada parent
                ]);
            }

            DB::commit();

            return response()->json(['success' => 'Menu berhasil ditambah.']);

        } catch (\Exception $e) {
            
            DB::rollBack();

            // Logging
            Log::error('Menu Update Error: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            // Kirim pesan error ke tampilan
            return response()->json(['errors' => $validator->errors()], 422);
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
    public function edit($id, Request $request)
    {

        $menu = Menu::with('children')->findOrFail($id);
        $userGroups = \App\Models\GroupUser::select('id', 'name')->get(); // atau sesuai model kamu

        $mode = $request->query('mode', 'edit');

        $data = [
            'menus' => [
                'id' => $menu->id,
                'name' => $menu->name,
                'code' => $menu->code,
                'slug' => $menu->slug,
                'seq' => $menu->seq,
                'children' => $menu->children->map(function ($child) {
                    return [
                        'name' => $child->name,
                        'code' => $child->code,
                        'slug' => $child->slug,
                        'seq' => $child->seq,
                    ];
                })->toArray()
            ],
            'disabled' => $mode,
            'userGroups' => $userGroups
        ];

        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();

        try {
            $items = $request->items;
    
            if (empty($items) || !isset($items[0])) {
                return back()->with('error', 'Data menu tidak lengkap.');
            }
    
            // Ambil parent
            $parentItem = $items[0];
            $parent = Menu::findOrFail($id);
    
            // Update parent
            $parent->update([
                'name' => $parentItem['name'],
                'seq' => $parentItem['seq'],
                'code' => $parentItem['code'],
                'slug' => $parentItem['slug'],
                'parent_id' => null,
            ]);
    
            // Hapus semua submenu lama
            Menu::where('parent_id', $parent->id)->delete();
    
            // Simpan ulang submenu baru
            for ($i = 1; $i < count($items); $i++) {
                Menu::create([
                    'name' => $items[$i]['name'],
                    'seq' => $items[$i]['seq'],
                    'code' => $items[$i]['code'],
                    'slug' => $items[$i]['slug'],
                    'parent_id' => $parent->id,
                ]);
            }
    
            DB::commit();

            return response()->json(['success' => 'Menu berhasil diubah.']);
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            // Log detail error untuk debugging
            Log::error('Menu Update Error: '.$e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
    
            return response()->json(['errors' => $validator->errors()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            Menu::where('parent_id', $id)->delete();
            Menu::destroy($id);
            
            DB::commit();
            return response()->json(['message' => 'Menu berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghapus menu.', 'error' => $e->getMessage()], 500);
        }
    }
}
