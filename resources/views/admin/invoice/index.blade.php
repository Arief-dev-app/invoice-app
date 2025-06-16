@extends('layouts.app')

@section('content')
<div class="px-4 py-6">
    <div class="bg-white shadow rounded p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Daftar Invoice</h2>
            <button onclick="openInvoiceModal('create')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ Buat Invoice</button>
        </div>

        <table class="min-w-full border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1">No</th>
                    <th class="border px-2 py-1">Aksi</th>
                    <th class="border px-2 py-1">Status</th>
                    <th class="border px-2 py-1">Invoice</th>
                    <th class="border px-2 py-1">Tanggal</th>
                    <th class="border px-2 py-1">Customer</th>
                    <th class="border px-2 py-1">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $i => $invoice)
                <tr>
                    <td class="border px-2 py-1">{{ $i + 1 }}</td>
                    <td class="border px-2 py-1 space-x-2">
                        <a href="{{ route('invoice.print', $invoice->id) }}" target="_blank" class="text-blue-500">🖨️</a>
                        <button onclick='openInvoiceModal("view", {!! json_encode($invoice) !!})'>👁</button>
                       <button onclick='openInvoiceModal("edit", {!! json_encode($invoice) !!})'>✏️</button>
                       <form action="{{ route('product.destroy', $invoice->id) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button onclick="return confirm('Yakin hapus?')" class="text-red-500">🗑</button>
                        </form>
                    </td>
                    <td class="border px-2 py-1">{{ $invoice->trans_status == 1 ? 'Open' : ($invoice->trans_status == 2 ? 'Cancel' : ($invoice->trans_status == 3 ? 'Confirm' : 'Unknown')) }}</td>
                    <td class="border px-2 py-1">{{ $invoice->invoice_no }}</td>
                    <td class="border px-2 py-1">{{ \Carbon\Carbon::parse($invoice->created_at)->format('d-m-Y') }}</td>
                    <td class="border px-2 py-1">{{ $invoice->customer_name }}</td>
                    <td class="border px-2 py-1">Rp {{ number_format($invoice->total_harga, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL -->
<div id="invoiceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white p-6 rounded shadow-lg w-full max-w-5xl h-[90vh] flex flex-col">
    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold" id="modalTitle">Tambah Invoice</h3>
        <button onclick="closeInvoiceModal()" class="text-gray-500 hover:text-black">&times;</button>
    </div>

    <!-- Scrollable content -->
    <form id="invoiceForm" method="POST" class="flex-1 overflow-y-auto">
        @csrf
        <input type="hidden" name="_method" id="method" value="POST">
        <input type="hidden" name="user_id" value="{{ auth()->id() }}">

        <div class="mb-4">
            <label class="block text-sm font-medium">Invoice No</label>
            <input type="text" name="invoice_no" id="invoice_no" class="w-full border rounded px-3 py-2" readonly>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium">Customer</label>
            <input type="text" name="customer_name" id="customer_name" class="w-full border rounded px-3 py-2">
        </div>

        <!-- Tabel Produk -->
        <div class="mb-4">
            <div class="flex justify-between items-center mb-2">
                <label class="block text-sm font-medium">Produk</label>
                <button type="button" onclick="addProductRow()" class="bg-green-500 text-white text-sm px-2 py-1 rounded">+ Tambah Produk</button>
            </div>
            <table class="w-full border text-sm" id="productTable">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-2 py-1">Aksi</th>
                        <th class="border px-2 py-1">Qty Satuan</th>
                        <th class="border px-2 py-1">Produk</th>
                        <th class="border px-2 py-1">Qty</th>
                        <th class="border px-2 py-1">Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Baris dinamis dari JS -->
                </tbody>
            </table>
        </div>
    </form>

    <!-- Footer tetap di bawah -->
    <div class="flex justify-end space-x-2 mt-4 border-t pt-4" id="modalFooter">
        <button type="button" onclick="closeInvoiceModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Batal</button>
        <button type="submit" form="invoiceForm" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script>
   function openInvoiceModal(mode, data = null) {
        const modal = document.getElementById('invoiceModal');
        const title = document.getElementById('modalTitle');
        const form = document.getElementById('invoiceForm');
        const method = document.getElementById('method');
        const footer = document.getElementById('modalFooter');

        // Reset semua input
        form.reset();

        // Pastikan tombol muncul & input bisa diubah
        footer.classList.remove('hidden');
        form.querySelectorAll('input').forEach(input => input.removeAttribute('readonly'));

        if (mode === 'create') {
            title.innerText = 'Buat Invoice';
            form.action = '/invoice';
            method.value = 'POST';
            document.getElementById('invoice_no').value = 'INV' + Date.now();
        }

        if (mode === 'view') {
            title.innerText = 'Lihat Invoice';
            form.action = '#'; // tidak dikirim
            method.value = '';
            fillForm(data);
            console.log(data);
            footer.classList.add('hidden');
            form.querySelectorAll('input').forEach(input => input.setAttribute('readonly', true));
        }

        if (mode === 'edit') {
            title.innerText = 'Edit Invoice';
            form.action = `/invoice/${data.id}`;
            method.value = 'PUT';
            fillForm(data);
            console.log(data);
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fillForm(data) {
        if (!data) return;

        document.getElementById('invoice_no').value = data.invoice_no || '';
        document.getElementById('customer_name').value = data.customer_name || '';
        document.getElementById('total_harga').value = data.total_harga || '';
    }

    function closeInvoiceModal() {
        const modal = document.getElementById('invoiceModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    let products = @json($products); // ambil dari controller
    let rowCount = 0;

    function addProductRow() {
        const tbody = document.querySelector('#productTable tbody');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="border px-2 py-1 text-center">
                <button type="button" onclick="this.closest('tr').remove()" class="text-red-500">🗑</button>
            </td>
            <td class="border px-2 py-1">
                <select name="items[${rowCount}][product_id]" class="w-full border rounded px-2 py-1" onchange="updateTotal(this)">
                    <option value="">-- Pilih Produk --</option>
                    ${products.map(p => `<option value="${p.id}" data-harga="${p.harga}">${p.nama}</option>`).join('')}
                </select>
            </td>
             <td class="border px-2 py-1">
                <input type="number" name="items[${rowCount}][harga]" class="w-full border rounded px-2 py-1" readonly>
            </td>
            <td class="border px-2 py-1">
                <input type="number" name="items[${rowCount}][qty]" value="1" class="w-full border rounded px-2 py-1" onchange="updateTotal(this)">
            </td>
            <td class="border px-2 py-1">
                <input type="number" name="items[${rowCount}][total]" class="w-full border rounded px-2 py-1" readonly>
            </td>
        `;
        tbody.appendChild(row);
        rowCount++;
    }

    function updateTotal(el) {
        const row = el.closest('tr');
        const select = row.querySelector('select');
        const hargaInput = row.querySelector('input[name$="[harga]"]');
        const qtyInput = row.querySelector('input[name$="[qty]"]');
        const totalInput = row.querySelector('input[name$="[total]"]');

        const harga = parseInt(select.selectedOptions[0]?.dataset?.harga || 0);
        const qty = parseInt(qtyInput.value || 0);
        const total = harga * qty;

        hargaInput.value = harga;
        totalInput.value = total;
    }
</script>
@endpush
