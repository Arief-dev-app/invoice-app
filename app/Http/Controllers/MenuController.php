<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $menus = [
            (object)[
                'id' => 1,
                'nama' => 'Dashboard',
                'url' => '/dashboard',
                'icon' => '🏠',
                'urutan' => 1,
            ],
            (object)[
                'id' => 2,
                'nama' => 'User',
                'url' => '/user',
                'icon' => '👤',
                'urutan' => 2,
            ],
            (object)[
                'id' => 3,
                'nama' => 'Product',
                'url' => '/product',
                'icon' => '📦',
                'urutan' => 3,
            ],
            (object)[
                'id' => 4,
                'nama' => 'Invoice',
                'url' => '/invoice',
                'icon' => '🧾',
                'urutan' => 4,
            ],
        ];

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
        //
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
