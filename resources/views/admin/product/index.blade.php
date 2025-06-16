@extends('layouts.app')

@section('content')
<div class="px-4 py-6">
    <div class="bg-white shadow rounded p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Daftar Produk</h2>
            <button onclick="openGlobalModal('Tambah Produk')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ Tambah</button>
        </div>

        <form method="GET" class="mb-4">
            <input type="text" name="search" placeholder="Cari nama produk..." value="{{ $search }}" class="border rounded px-3 py-2 w-full sm:w-64">
        </form>

        <table class="min-w-full border mt-4">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="p-2 border">No</th>
                    <th class="p-2 border">Action</th>
                    <th class="p-2 border">Nama Produk</th>
                    <th class="p-2 border">Harga</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $i => $product)
                <tr>
                    <td class="p-2 border">{{ $i + 1 }}</td>
                    <td class="p-2 border space-x-2">
                        <a href="javascript:void(0)" onclick="openGlobalModal('Lihat Produk', {{ json_encode($product) }}, 'view')" class="text-green-500">👁</a>
                        <a href="javascript:void(0)" onclick="openGlobalModal('Edit Produk', {{ json_encode($product) }}, 'edit')" class="text-yellow-500">✏️</a>
                        <form action="{{ route('product.destroy', $product->id) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button onclick="return confirm('Yakin hapus?')" class="text-red-500">🗑</button>
                        </form>
                    </td>
                    <td class="p-2 border">{{ $product->nama }}</td>
                    <td class="p-2 border">Rp {{ number_format($product->harga, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-2 text-center">Tidak ada produk.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="globalModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold" id="globalModalTitle">Tambah Data</h3>
            <button onclick="closeGlobalModal()" class="text-gray-500 hover:text-black">&times;</button>
        </div>
        <form id="productForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="user_id" value="{{ auth()->id() }}">

            <div class="mb-4">
                <label for="formNama" class="block text-sm font-medium">Nama Produk</label>
                <input type="text" name="nama" id="formNama" class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label for="formHarga" class="block text-sm font-medium">Harga</label>
                <input type="number" name="harga" id="formHarga" class="w-full border rounded px-3 py-2">
            </div>

            <div class="flex justify-end space-x-2" id="formButtons">
                <button type="button" onclick="closeGlobalModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Tutup</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openGlobalModal(title = 'Tambah Produk', product = null, mode = 'create') {
        const modal = document.getElementById('globalModal');
        const form = document.getElementById('productForm');
        const methodInput = document.getElementById('formMethod');
        const namaInput = document.getElementById('formNama');
        const hargaInput = document.getElementById('formHarga');
        const buttons = document.getElementById('formButtons');
        const simpanBtn = buttons.querySelector('button[type="submit"]');

        document.getElementById('globalModalTitle').innerText = title;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        if (mode === 'edit' && product) {
            form.action = `/product/${product.id}`;
            methodInput.value = 'PUT';
            namaInput.value = product.nama ?? '';
            hargaInput.value = product.harga ?? '';
            namaInput.readOnly = false;
            hargaInput.readOnly = false;
            simpanBtn.classList.remove('hidden');
        } else if (mode === 'view' && product) {
            form.action = '#';
            methodInput.value = 'GET';
            namaInput.value = product.nama ?? '';
            hargaInput.value = product.harga ?? '';
            namaInput.readOnly = true;
            hargaInput.readOnly = true;
            simpanBtn.classList.add('hidden');
        } else {
            // mode create
            form.action = `/product`;
            methodInput.value = 'POST';
            namaInput.value = '';
            hargaInput.value = '';
            namaInput.readOnly = false;
            hargaInput.readOnly = false;
            simpanBtn.classList.remove('hidden');
        }
    }

    function closeGlobalModal() {
        const modal = document.getElementById('globalModal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }
</script>
@endpush

