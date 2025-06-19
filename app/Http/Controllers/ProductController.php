<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Menu;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        // $products = Product::when($search, function($query, $search) {
        //         return $query->where('nama', 'like', "%$search%");
        //     })->latest()->paginate(10);

        $products = collect([
             (object) ['id' => 1, 'nama' => 'Produk A', 'harga' => 10000],
             (object) ['id' => 2, 'nama' => 'Produk B', 'harga' => 15000],
             (object) ['id' => 3, 'nama' => 'Produk C', 'harga' => 20000],
             (object) ['id' => 4, 'nama' => 'Produk D', 'harga' => 25000],
             (object) ['id' => 5, 'nama' => 'Produk E', 'harga' => 30000],
             (object) ['id' => 6, 'nama' => 'Produk F', 'harga' => 35000],
             (object) ['id' => 7, 'nama' => 'Produk G', 'harga' => 40000],
             (object) ['id' => 8, 'nama' => 'Produk H', 'harga' => 45000],
             (object) ['id' => 9, 'nama' => 'Produk I', 'harga' => 50000],
             (object) ['id' => 10, 'nama' => 'Produk J', 'harga' => 55000],
        ]);

        $menus = Menu::with('children')->whereNull('parent_id')->get();


        return view('admin.product.index', compact('products','menus', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.product.create');
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
