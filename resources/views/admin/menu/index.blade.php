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

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4" role="alert">
                <strong class="font-bold">Gagal:</strong> {{ session('error') }}
                @if (session('error_detail'))
                    <br><span class="text-xs text-gray-600">Detail: {{ session('error_detail') }}</span>
                @endif
            </div>
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
                        <!-- <button onclick='handleModalAction("view", {!! json_encode($menu) !!})'>👁</button> -->
                        <!-- <button onclick='handleModalAction("edit", {!! json_encode($menu) !!})'>✏️</button> -->
                        <button onclick="fetchMenuAndOpenModal({{ $menu->id }}, 'view')">👁</button>
                        <button onclick="fetchMenuAndOpenModal({{ $menu->id }}, 'edit')">✏️</button>
                        <button onclick="handleDeleteAction(() => deleteMenu({{ $menu->id }}))">🗑</button>
                        
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
                    <!-- <button type="button" onclick="addProductRow()" class="bg-green-500 text-white text-sm px-2 py-1 rounded">+ Tambah Menu</button> -->
                </div>
                <table class="w-full border text-sm" id="productTable">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-2 py-1">Aksi</th>
                            <th class="border px-2 py-1">Nama Menu</th>
                            <th class="border px-2 py-1" style="width: 5%;">
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
<div id="alertContainer" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-md"></div>
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

            document.querySelector('#productTable tbody').innerHTML = '';
            rowCount = 0;

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
        const tbody = document.querySelector('#productTable tbody');
        tbody.innerHTML = '';
        rowCount = 0;

        const parent = {
            name: data.name,
            seq: data.seq ?? 1,
            code: data.code,
            slug: data.slug,
        };

        appendMenuRow(parent);

        if (Array.isArray(data.children)) {
            data.children.forEach(child => {
                appendMenuRow(child, parent.code, parent.seq);
            });
        }
    }

    function appendMenuRow(item, parentCode = null, parentSeq = 1) {
        const tbody = document.querySelector('#productTable tbody');
        const row = document.createElement('tr');

        let isSubmenu = parentCode !== null;

        let code = item.code || generateRandomCode();
        let seq = item.seq || rowCount + 1;

        row.innerHTML = `
            <td class="border px-2 py-1 text-center space-x-1">
                ${!isSubmenu ? `<button type="button" onclick="addProductRow(this)" class="text-green-600 text-xl" title="Tambah Sub Menu">➕</button>` : ''}
                ${isSubmenu ? `<button type="button" onclick="this.closest('tr').remove()" class="text-red-500" title="Hapus">🗑</button>` : ''}
            </td>
            <td class="border px-2 py-1">
                <input type="text" name="items[${rowCount}][name]" class="w-full border rounded px-2 py-1" value="${item.name || ''}">
            </td>
            <td class="border px-2 py-1">
                <input type="number" name="items[${rowCount}][seq]" class="w-full border rounded px-2 py-1" value="${seq}" readonly>
            </td>
            <td class="border px-2 py-1">
                <input type="text" name="items[${rowCount}][code]" class="w-full border rounded px-2 py-1" value="${code}" readonly>
            </td>
            <td class="border px-2 py-1">
                <input type="text" name="items[${rowCount}][slug]" class="w-full border rounded px-2 py-1" value="${item.slug || ''}">
            </td>
        `;

        tbody.appendChild(row);
        rowCount++;
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
        fetch('/menu/create')
        .then(res => {
            if (!res.ok) throw new Error('Gagal memuat data');
            return res.json();
        })
        .then(data => {
            hideFullscreenLoader();
            openMenuModal('create', data.menus); // ← tampilkan modal isi default (jika ada)
        })
        .catch(err => {
            console.error(err);
            hideFullscreenLoader();
            alert('Gagal membuka form tambah menu.');
        });
    }

    function handleModalAction(mode, data = null) {
        showFullscreenLoader();
        setTimeout(() => {
            hideFullscreenLoader();
            openMenuModal(mode, data);
        }, 1000);
    }

    function showSuccessAlert(message) {
        const container = document.getElementById('alertContainer');

        const alert = document.createElement('div');
        alert.className = 'bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded shadow-lg flex items-center space-x-2 transition-opacity duration-500';
        alert.innerHTML = `
            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span class="flex-1">${message}</span>
        `;

        container.appendChild(alert);

        setTimeout(() => {
            alert.classList.add('opacity-0');
            setTimeout(() => alert.remove(), 500);
        }, 2000);
    }

    async function handleFormSubmit(event) {
        event.preventDefault();

        const form = event.target;
        const url = form.action;
        const method = document.getElementById('method').value || 'POST';

        showFullscreenLoader();
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;

        const formData = new FormData(form);

        try {

            const response = await fetch(url, {
                method: method === 'POST' ? 'POST' : 'POST', // tetap pakai POST, Laravel baca _method
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: formData
            });

            const result = await response.json();

            if (response.ok) {
                closeMenuModal();
                showSuccessAlert('Menu berhasil disimpan!');
            } else if (response.status === 422) {
                // Gagal validasi
                showValidationErrors(result.errors);
                hideFullscreenLoader();
                submitBtn.disabled = false;
            } else {
                alert('Terjadi kesalahan server.');
                hideFullscreenLoader();
                submitBtn.disabled = false;
            }

        } catch (error) {
            console.error('Error:', error);
            alert('Gagal mengirim data.');
            hideFullscreenLoader();
            submitBtn.disabled = false;
        } finally {
            hideFullscreenLoader(); // ✅ PASTIKAN spinner selalu dimatikan
            submitBtn.disabled = false;
        }

        
    }

    async function fetchMenuAndOpenModal(menuId, mode = 'edit') {
        showFullscreenLoader();

        try {
            const response = await fetch(`/menu/${menuId}/edit`);
            console.log(response);
            if (!response.ok) throw new Error('Gagal memuat data');

            const data = await response.json();

            const menuData = data.menus;

            hideFullscreenLoader();
            openMenuModal(mode, menuData);

        } catch (error) {
            console.error(error);
            hideFullscreenLoader();
            alert('Terjadi kesalahan saat memuat data menu.');
        }
    }

    function showValidationErrors(errors) {
        const fields = document.querySelectorAll('#menuForm input');

        fields.forEach(input => {
            const name = input.getAttribute('name');
            const errorMessage = errors[name];
            input.classList.remove('border-red-500');

            // Tambahkan pesan error
            let errorEl = input.parentNode.querySelector('.error-text');
            if (errorEl) errorEl.remove();

            if (errorMessage) {
                input.classList.add('border-red-500');
                const small = document.createElement('small');
                small.classList.add('error-text', 'text-red-600', 'block', 'mt-1');
                small.textContent = errorMessage[0];
                input.parentNode.appendChild(small);
            }
        });
    }

    let deleteCallback = null;

    function handleDeleteAction(callback) {
        deleteCallback = callback;
        const modal = document.getElementById('confirmDeleteModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }


    function cancelDelete() {
        deleteCallback = null;
        const modal = document.getElementById('confirmDeleteModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function confirmDelete() {
        if (typeof deleteCallback === 'function') {
            deleteCallback();
        }
        cancelDelete(); // Close modal
    }

    async function deleteMenu(menuId) {
        showFullscreenLoader();

        try {
            const response = await fetch(`/menu/${menuId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ _method: 'DELETE' })
            });

            const result = await response.json();

            if (response.ok) {
                showSuccessAlert(result.message || 'Menu berhasil dihapus');
                setTimeout(() => window.location.reload(), 1000); // Atau refresh tabel pakai AJAX
            } else {
                alert(result.message || 'Gagal menghapus menu.');
            }
        } catch (err) {
            console.error('Gagal hapus:', err);
            alert('Terjadi kesalahan saat menghapus.');
        } finally {
            hideFullscreenLoader();
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
        let seq = '';
        let namePlaceholder = 'Nama Menu';
        let showAddButton = true;

        // Jika tidak ada tombol (berarti baris awal)
        if (!button) {
            kode = generateRandomCode();
            seq = 1;
            showAddButton = true;
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
            seq = parseInt(parentRow.querySelector('input[name$="[seq]"]').value + '' + subIndex);
            namePlaceholder = 'Nama Sub Menu';
            showAddButton = false;
        }

        row.innerHTML = `
            <td class="border px-2 py-1 text-center space-x-1">
                ${showAddButton ? `<button type="button" onclick="addProductRow(this)" class="text-green-600 text-xl" title="Tambah Sub Menu">➕</button>` : ''}
                ${!showAddButton ? `<button type="button" onclick="this.closest('tr').remove()" class="text-red-500" title="Hapus">🗑</button>` : ''}
            </td>
            <td class="border px-2 py-1">
                <input type="text" name="items[${rowCount}][name]" class="w-full border rounded px-2 py-1" placeholder="${namePlaceholder}">
            </td>
            <td class="border px-2 py-1">
                <input type="number" name="items[${rowCount}][seq]" class="w-full border rounded px-2 py-1" value="${seq}" readonly>
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


</script>

@if ($errors->any())
<script>
    window.addEventListener('DOMContentLoaded', () => {
        const oldItems = @json(old('items', []));
        let data = {
            id: '{{ old("id") }}',
            name: oldItems[0]?.name || '',
            code: oldItems[0]?.code || '',
            slug: oldItems[0]?.slug || '',
            seq: oldItems[0]?.seq || 1,
            children: []
        };

        // Asumsikan item pertama adalah parent
        for (let i = 1; i < oldItems.length; i++) {
            data.children.push(oldItems[i]);
        }

        openMenuModal("{{ old('_method') === 'PUT' ? 'edit' : 'create' }}", data);
    });
</script>
@endif
@endpush
