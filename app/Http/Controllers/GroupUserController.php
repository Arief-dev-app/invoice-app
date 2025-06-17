<?php

namespace App\Http\Controllers;

use App\Models\GroupUser;
use Illuminate\Http\Request;

class GroupUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $groupUsers = GroupUser::all();
        return view('admin.group_user.index', compact('groupUsers'));
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
                'description' => ['required', 'string', 'max:255'],
            
            ]);

            $user = GroupUser::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);
    
            return redirect()->route('group-user.index')->with('success', 'Menu berhasil dibuat!');

        } catch (\Throwable $th) {
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
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
        ]);
    
        $groupUser = GroupUser::findOrFail($id);
        $groupUser->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('group-user.index')->with('success', 'Group user berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $groupUser = GroupUser::findOrFail($id);

        // Ubah flag_active menjadi false (nonaktif)
        $groupUser->flag_active = false;
        $groupUser->save();

        return redirect()->route('group-user.index')->with('success', 'Group user berhasil dinonaktifkan.');
    }

    public function restore($id)
    {
        $groupUser = GroupUser::findOrFail($id);
        $groupUser->flag_active = true;
        $groupUser->save();

        return redirect()->route('group-user.index')->with('success', 'Group user berhasil diaktifkan kembali.');
    }
}
