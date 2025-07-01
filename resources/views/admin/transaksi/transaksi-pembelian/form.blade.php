<div class="px-4 py-6">
    <div class="bg-white shadow rounded p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Daftar {{ $title }}</h2>
            <button onclick="handleAddGroupClick('Tambah {{ $title }}')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ Tambah {{ $title }}</button>
        </div>

        @if (session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    showSuccessAlert(@json(session('success')));
                });
            </script>
        @endif

        @if (session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    showErrorAlert(@json(session('error')));
                });
            </script>
        @endif


        <div class="flex items-center justify-between mb-4">
            <form method="GET" class="flex gap-2 items-center">
                <input type="text" id="searchInput" name="search" placeholder="Cari No {{ $title }}..." class="border rounded px-3 py-2 w-full sm:w-64">
                <select id="perPageSelect" class="border rounded px-4 py-2 text-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </form>
        </div>

        <table class="min-w-full border mt-4">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="p-2 border">No</th>
                    <th class="p-2 border">Action</th>
                    <th class="p-2 border">Trans No</th>
                    <th class="p-2 border">Trans Status</th>
                    <th class="p-2 border">Date</th>
                    <th class="p-2 border">No Po</th>
                    <th class="p-2 border">Suplier</th>
                    <th class="p-2 border">Total</th>
                </tr>
            </thead>
            <tbody id="productTableBody">
                <tr>
                    <td colspan="5" class="text-center text-gray-500">Memuat data...</td>
                </tr>
            </tbody>
        </table>
        <div class="mt-4 flex items-center justify-between" id="paginationWrapper">
            <div id="dataSummary" class="text-sm text-gray-600"></div>
            <div id="paginationLinks" class="flex flex-wrap gap-2"></div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="confirmTransModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-[9999] flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h2 class="text-lg font-semibold mb-4">Konfirmasi Transaksi</h2>
        <p class="text-sm text-gray-600 mb-6">Apakah Anda yakin ingin mengonfirmasi transaksi ini?</p>
        <div class="flex justify-end space-x-2">
            <button onclick="cancelConfirm()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded">Batal</button>
            <button onclick="confirmTransaction()" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded">Ya, Konfirmasi</button>
        </div>
    </div>
</div>


<div id="confirmDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[9999]">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h2 class="text-lg font-semibold mb-4">Konfirmasi Hapus</h2>
        <p class="text-sm text-gray-600 mb-6">Apakah Anda yakin ingin menghapus item ini?</p>
        <div class="flex justify-end space-x-2">
            <button onclick="cancelDelete()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded">Batal</button>
            <button onclick="confirmDelete()" class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded">Hapus</button>
        </div>
    </div>
</div>

<div id="fullscreenLoader" class="fixed inset-0 bg-black bg-opacity-50 hidden z-[9999] flex flex-col items-center justify-center">
    <div class="w-12 h-12 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
    <div class="text-white mt-4 text-lg">Memuat data...</div>
</div>

<div id="globalModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-5xl h-[90vh] flex flex-col">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold" id="globalModalTitle">Tambah {{ $title }}</h3>
            <button onclick="closeGlobalModal()" class="text-gray-500 hover:text-black">&times;</button>
        </div>
        <div class="overflow-auto flex-grow pr-1">
            <form id="productForm" method="POST">
                @csrf
                <meta name="csrf-token" content="{{ csrf_token() }}">
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="user_id" value="{{ auth()->id() }}">

                <div class="mb-4">
                    <label for="formTrans" class="block text-sm font-medium">Trans No</label>
                    <input type="text" name="trans" id="formTrans" class="w-full border rounded px-3 py-2" readonly>
                </div>

                <div class="mb-4">
                    <label for="formTransDate" class="block text-sm font-medium">Trans Date</label>
                    <input type="date" name="trans_date" id="formTransDate" class="w-full border rounded px-3 py-2" value="{{ date('Y-m-d') }}">
                </div>

                <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <label for="user_group_select" class="block text-sm font-medium">No Po</label>
                        </div>
                        <select id="po_select" name="po_id" class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="">-- Pilih Po No --</option>
                            @foreach($pos as $po)
                                <option value="{{ $po->id }}" {{ old('po_id') == $po->id ? 'selected' : '' }}>{{ $po->purchase_no }}</option>
                            @endforeach
                        </select>
                </div>

                <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <label for="user_group_select" class="block text-sm font-medium">Supplier</label>
                        </div>
                        <select id="supplier_select" name="supplier_id" class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="">-- Pilih Group --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                </div>
                

                <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-medium">Produk</label>
                    <button type="button" onclick="addProductRow()" class="bg-green-500 text-white text-sm px-2 py-1 rounded">+ Tambah Produk</button>
                </div>
                    <table class="w-full border text-sm" id="productTable">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border px-2 py-1">Aksi</th>
                                <th class="border px-2 py-1">Produk</th>
                                <th class="border px-2 py-1">Stock</th>
                                <th class="border px-2 py-1">Qty</th>
                                <th class="border px-2 py-1">Harga</th>
                            </tr>
                        </thead>
                        <tbody id ="productDetailTableBody">
                            <!-- Baris dinamis dari JS -->
                        </tbody>
                    </table>
                </div>

            </form>
        </div>
        <div class="flex justify-end space-x-2" id="formButtons">
            <button type="button" id="btnConfirm" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Confirm</button>
            <button type="button" onclick="closeGlobalModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Tutup</button>
            <button type="submit" form="productForm" id="btnSimpan" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
        </div>
    </div>
</div>