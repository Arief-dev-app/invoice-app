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
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold" id="modalTitle">Tambah Menu</h3>
            <button onclick="closeMenuModal()" class="text-gray-500 hover:text-black">&times;</button>
        </div>

        <form id="menuForm" method="POST"  onsubmit="handleFormSubmit(event)">
            @csrf
            <input type="hidden" name="_method" id="method" value="POST">
            <input type="hidden" name="id" id="menu_id" value="{{ old('id') }}">

            <div class="mb-2">
                <label class="block text-sm font-medium">Nama Menu</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-2">
                <label class="block text-sm font-medium">URL</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" class="w-full border rounded px-3 py-2 @error('slug') border-red-500 @enderror">
                @error('slug')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
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

            <div class="flex justify-end space-x-2 mt-4 border-t pt-4" id="modalFooter">
                <button type="button" onclick="closeMenuModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Batal</button>
                <button type="submit" form="menuForm" id="submitBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
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
