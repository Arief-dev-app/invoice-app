@extends('layouts.app')

@section('content')
<div class="px-4 py-6">
    <div class="bg-white shadow rounded p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Daftar Menu</h2>
            <button id="addGroupBtn" onclick="handleAddGroupClick()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                + Tambah Menu</button>
            </div>

        @if (session('success'))
            <div id="successAlert" class="fixed top-4 left-50 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded shadow-lg flex items-center space-x-2 z-50 transition-opacity duration-500">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>

            <script>
                setTimeout(() => {
                    const alert = document.getElementById('successAlert');
                    if (alert) {
                        alert.classList.add('opacity-0');
                        setTimeout(() => alert.remove(), 500);
                    }
                }, 2000);
            </script>
        @endif

        <table class="min-w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1 w-12 text-center">No</th>
                    <th class="border px-2 py-1">Aksi</th>
                    <th class="border px-2 py-1">Nama Menu</th>
                    <th class="border px-2 py-1">URL</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($menus as $i => $menu)
                <tr>
                    <td class="border px-2 py-1 text-center">{{ $i + 1 }}</td>
                    <td class="border px-2 py-1 space-x-2">
                        <button onclick='handleModalAction("view", {!! json_encode($menu) !!})'>👁</button>
                        <button onclick='handleModalAction("edit", {!! json_encode($menu) !!})'>✏️</button>
                        <form action="{{ route('menu.destroy', $menu->id) }}" method="POST" class="inline" onsubmit="handleDeleteAction(event, this)">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500">🗑</button>
                        </form>
                    </td>
                    <td class="border px-2 py-1">{{ $menu->name }}</td>
                    <td class="border px-2 py-1">{{ $menu->slug }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="fullscreenLoader" class="fixed inset-0 bg-black bg-opacity-50 hidden z-[9999] flex flex-col items-center justify-center">
    <div class="w-12 h-12 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
    <div class="text-white mt-4 text-lg">Memuat data...</div>
</div>

<div id="menuModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded shadow-lg w-full max-w-[1400px] h-[90vh] flex flex-col">
       <!-- Header -->
    <div class="flex justify-between items-center px-6 py-4 border-b">
        <h3 class="text-lg font-bold" id="modalTitle">Tambah Menu</h3>
        <button onclick="closeMenuModal()" class="text-gray-500 hover:text-black text-2xl">&times;</button>
    </div>

    <form id="menuForm" method="POST" onsubmit="handleFormSubmit(event)" class="flex flex-col flex-grow overflow-hidden">
        @csrf
        <input type="hidden" name="_method" id="method" value="POST">
        <input type="hidden" name="id" id="menu_id" value="{{ old('id') }}">

        <!-- Scrollable Content -->
        <div class="flex-grow overflow-y-auto px-6 py-4">
            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-medium">Menu</label>
                    <button type="button" onclick="addProductRow()" class="bg-green-500 text-white text-sm px-2 py-1 rounded">+ Tambah Menu</button>
                </div>
                <table class="w-full border text-sm" id="productTable">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-2 py-1">Aksi</th>
                            <th class="border px-2 py-1">Nama Menu</th>
                            <th class="border px-2 py-1">Kode</th>
                            <th class="border px-2 py-1">Slug</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Baris dinamis dari JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sticky Footer -->
        <div class="flex justify-end space-x-2 px-6 py-4 border-t bg-white" id="modalFooter">
            <button type="button" onclick="closeMenuModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Batal</button>
            <button type="submit" id="submitBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
        </div>
    </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
    function openMenuModal(mode, data = null) {
        const modal = document.getElementById('menuModal');
        const title = document.getElementById('modalTitle');
        const form = document.getElementById('menuForm');
        const method = document.getElementById('method');
        const footer = document.getElementById('modalFooter');

        form.reset();
        document.getElementById('menu_id').value = '';
        form.querySelectorAll('input[type="text"]').forEach(i => i.removeAttribute('readonly'));
        footer.classList.remove('hidden');

        if (mode === 'create') {
            title.innerText = 'Tambah Menu';
            form.action = '/menu';
            method.value = 'POST';

            addProductRow();
        }

        if (mode === 'view') {
            title.innerText = 'Lihat Menu';
            form.action = '#';
            method.value = '';
            fillForm(data);
            form.querySelectorAll('input[type="text"]').forEach(i => i.setAttribute('readonly', true));
            footer.classList.add('hidden');
        }

        if (mode === 'edit') {
            title.innerText = 'Edit Menu';
            form.action = `/menu/${data.id}`;
            method.value = 'PUT';
            fillForm(data);
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeMenuModal() {
        const modal = document.getElementById('menuModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function fillForm(data) {
        if (!data) return;
        document.getElementById('menu_id').value = data.id || '';
        document.getElementById('name').value = data.name || '';
        document.getElementById('slug').value = data.slug || '';
    }

    function showFullscreenLoader() {
        const loader = document.getElementById('fullscreenLoader');
        loader.classList.remove('hidden');
    }

    function hideFullscreenLoader() {
        const loader = document.getElementById('fullscreenLoader');
        loader.classList.add('hidden');
    }

    function handleAddGroupClick() {
        showFullscreenLoader();
        setTimeout(() => {
            hideFullscreenLoader();
            openMenuModal('create');
        }, 2000);
    }

    function handleModalAction(mode, data = null) {
        showFullscreenLoader();
        setTimeout(() => {
            hideFullscreenLoader();
            openMenuModal(mode, data);
        }, 1000);
    }

    function handleFormSubmit(event) {
        event.preventDefault();

        showFullscreenLoader();
        
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true; // Disable agar tidak bisa diklik berkali-kali

        showFullscreenLoader(); // Tampilkan loading seperti yang digunakan di seluruh app

        // Tunggu sedikit agar loader sempat terlihat
        setTimeout(() => {
            event.target.submit(); 
        }, 1500); 
    }

    function handleDeleteAction(e, form) {
        if (confirm('Yakin nonaktifkan group ini?')) {
            showFullscreenLoader();
            form.submit();
        } else {
            e.preventDefault();
        }
    }

    function handleRestoreAction(e, form) {
        if (confirm('Aktifkan kembali group ini?')) {
            showFullscreenLoader();
            form.submit();
        } else {
            e.preventDefault();
        }
    }

    function generateRandomCode(length = 6) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let code = '';
        for (let i = 0; i < length; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return code;
    }

    let rowCount = 0;

    function addProductRow(button = null) {
        const tbody = document.querySelector('#productTable tbody');
        const row = document.createElement('tr');

        let kode = '';
        let namePlaceholder = 'Nama Menu';

        // Jika tidak ada tombol (berarti baris awal)
        if (!button) {
            kode = generateRandomCode();
        } else {
            // Baris sub-menu (klik dari tombol ➕)
            const parentRow = button.closest('tr');
            const parentCodeInput = parentRow.querySelector('input[name$="[code]"]');
            const parentCode = parentCodeInput.value;

            // Hitung jumlah sub dari parent yang ada
            const subRows = Array.from(tbody.querySelectorAll('input[name$="[code]"]'))
                .filter(input => input.value.startsWith(parentCode + '-'));

            const subIndex = subRows.length + 1;
            kode = `${parentCode}-${subIndex}`;
            namePlaceholder = 'Nama Sub Menu';
        }

        row.innerHTML = `
            <td class="border px-2 py-1 text-center space-x-1">
                <button type="button" onclick="addProductRow(this)" class="text-green-600 text-xl" title="Tambah Sub Menu">➕</button>
                <button type="button" onclick="this.closest('tr').remove()" class="text-red-500" title="Hapus">🗑</button>
            </td>
            <td class="border px-2 py-1">
                <input type="text" name="items[${rowCount}][name]" class="w-full border rounded px-2 py-1" placeholder="${namePlaceholder}">
            </td>
            <td class="border px-2 py-1">
                <input type="text" name="items[${rowCount}][code]" class="w-full border rounded px-2 py-1" value="${kode}" readonly>
            </td>
            <td class="border px-2 py-1">
                <input type="text" name="items[${rowCount}][slug]" class="w-full border rounded px-2 py-1" placeholder="Slug">
            </td>
        `;

        // Tambahkan di akhir
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

@if ($errors->any())
<script>
    window.addEventListener('DOMContentLoaded', () => {
        openMenuModal("{{ old('_method') === 'PUT' ? 'edit' : 'create' }}", {
            id: '{{ old("id") }}',
            name: '{{ old("name") }}',
            slug: '{{ old("slug") }}'
        });
    });
</script>
@endif
@endpush
