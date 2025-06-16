<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
   public function index()
    {
        // $company = Company::first();
        $company = (object)[
            'nama_usaha' => 'PT Contoh Usaha',
            'nama_person' => 'Budi Santoso',
            'alamat' => 'Jl. Merdeka No. 123, Jakarta',
            'no_hp' => '081234567890',
            'email' => 'budi@contohusaha.com'
        ];

        return view('admin.company.index', compact('company'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_usaha' => 'required|string|max:255',
            'nama_person' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        Company::updateOrCreate(['id' => 1], $validated);

        return redirect()->route('company.index')->with('success', 'Data perusahaan berhasil disimpan.');
    }
}
