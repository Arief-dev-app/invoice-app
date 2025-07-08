<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Product;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use Illuminate\Pagination\Paginator;
use App\Http\Controllers\BaseController;
use Spatie\SimpleExcel\SimpleExcelWriter;

use Pdf;

class LaporanPenjualanController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function boot(): void
    {
        Paginator::useTailwind();
    }

    public function data(){
        return [
            'url' => '/laporan-penjualan',
            'title' => 'Laporan Penjualan',
            'kode' => 'MENU01-2'
        ];
    }
    public function index(Request $request)
    {
        $search   = $request->query('search');
        $perPage  = $request->query('per_page', 10); // default 10 jika tidak ada
        
        $penjualan = Penjualan::with('customer')
            ->where('user_id', auth()->id()) // tetap filter berdasarkan user
            ->when($search, function ($query, $search) {
                return $query->where('trans_no', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage)
            ->appends(['search' => $search, 'per_page' => $perPage]); // agar pagination tetap bawa query string
        
        if ($request->wantsJson()) {
            return response()->json($penjualan);
        }

        $products = Product::where('user_id', auth()->id())
        ->select('id', 'nama', 'harga_jual')
        ->get();

        
        $customer = Customer::all();

        $menus = $this->getAccessibleMenus();
        $default = $this->data();

        return view('admin.transaksi.laporan-penjualan.index', array_merge($default, [
            'penjualan'     => [], 
            'search'       => [], 
            'menu_header'  => $menus['menu_header'],
            'menu_detail'  => $menus['menu_detail'],
            'products'  => [],
            'customers'  => [],
        ]));
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

    public function printPdf(Request $request)
    {
        
        $start = $request->query('start');
        $end = $request->query('end');

        // ambil data berdasarkan tanggal
        $data = PenjualanDetail::select('prd_id', DB::raw('SUM(qty) as total_qty'))
        ->whereHas('header', function ($query) use ($start, $end) {
            $query->whereBetween('transaction_date', [$start, $end])->where('user_id', auth()->id());
        })
        ->groupBy('prd_id')
        ->with('prd') // relasi ke produk
        ->get();

        $pdf = Pdf::loadView('admin.transaksi.laporan-penjualan.print', compact('data', 'start', 'end'));

        return $pdf->stream('laporan.pdf', ['Attachment' => false]);
    }

    public function printExcel(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');

        $data = PenjualanDetail::select(
            'prd_id',
            DB::raw('SUM(qty) as total_qty')
        )
        ->whereHas('header', function ($query) use ($start, $end) {
            $query->whereBetween('transaction_date', [$start, $end])->where('user_id', auth()->id());
        })
        ->groupBy('prd_id')
        ->with('prd') // relasi ke produk yang punya harga_jual
        ->get()
        ->map(function ($item) {
            $harga = $item->prd->harga_jual ?? 0;
            return (object)[
                'nama_produk' => $item->prd->nama ?? '-',
                'total_qty' => $item->total_qty,
                'harga_jual' => $harga,
                'total_harga' => $item->total_qty * $harga,
            ];
        });

        // Konversi ke array yang bisa diekspor
        $export = $data->map(function ($item, $i) {
            return [
                'No' => $i + 1,
                'Nama Produk' => $item->nama_produk ,
                'Qty' => $item->total_qty,
                'Total Harga' => number_format($item->harga_jual, 0, ',', '.'),
                'Total Harga' => number_format($item->total_harga, 0, ',', '.'),
            ];
        });

        $filename = 'laporan-penjualan-' . now()->format('Ymd_His') . '.csv';

        $tempPath = storage_path($filename);
        SimpleExcelWriter::create($tempPath)
            ->addRows($export->toArray());

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }
}
