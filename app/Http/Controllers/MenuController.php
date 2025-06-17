<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $menus = Menu::all();

        return view('admin.menu.index', compact('menus'));
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
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'slug' => ['required', 'string', 'max:255'],
            
            ]);


            $user = Menu::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'flag_active' => true, // Jika kolom ini ada
            ]);
    
            return redirect()->route('menu.index')->with('success', 'Group user berhasil dibuat!');

        } catch (\Exception $e) {
            // Log error kalau perlu: Log::error($e);
            return back()->withInput()->withErrors([
                'error' => 'Gagal menyimpan data. Silakan coba lagi.',
            ]);
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
    public function edit(string $id)
    {
        //
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
