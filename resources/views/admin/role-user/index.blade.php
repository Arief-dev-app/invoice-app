@extends('layouts.app')

@section('content')
<div class="px-4 py-6">
    <div class="bg-white shadow rounded p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Daftar Role</h2>
            <button id="addGroupBtn" onclick="handleAddGroupClick()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                + Tambah Role</button>
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
                    <th class="border px-2 py-1">Nama Group User</th>
                    
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $i => $role)
                <tr>
                    <td class="border px-2 py-1 text-center">{{ $i + 1 }}</td>
                    <td class="border px-2 py-1 space-x-2">
                        <button onclick="fetchMenuAndOpenModal({{ $role->id }}, 'view')">👁</button>
                        <button onclick="fetchMenuAndOpenModal({{ $role->id }}, 'edit')">✏️</button>
                        <button onclick="handleDeleteAction(() => deleteMenu({{ $role->id }}))">🗑</button>
                        
                    </td>
                    <td class="border px-2 py-1">{{ $role->groupUser->name }}</td>
                    
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
<div class="bg-white p-6 rounded shadow-lg w-full max-w-5xl h-[90vh] flex flex-col">
       <!-- Header -->
    <div class="flex justify-between items-center px-6 py-4 border-b">
        <h3 class="text-lg font-bold" id="modalTitle">Tambah Role</h3>
        <button onclick="closeMenuModal()" class="text-gray-500 hover:text-black text-2xl">&times;</button>
    </div>

    <form id="menuForm" method="POST" onsubmit="handleFormSubmit(event)" class="flex flex-col flex-grow overflow-hidden">
        @csrf
        <input type="hidden" name="_method" id="method" value="POST">
        <input type="hidden" name="id" id="menu_id" value="{{ old('id') }}">

        <!-- Scrollable Content -->
        <div class="flex-grow overflow-y-auto px-6 py-4">
            <div class="mb-4">
               
                 

                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <label for="user_group_select" class="block text-sm font-medium">Pilih Group User</label>
                    </div>
                    <select id="user_group_select" name="user_group_id" class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">-- Pilih Group --</option>
                        @foreach($group as $user_group)
                            <option value="{{ $user_group->id }}" {{ old('user_group_id') == $user_group->id ? 'selected' : '' }}>{{ $user_group->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <label for="menu_select" class="block text-sm font-medium">Pilih Menu</label>
                    </div>
                    <select id="menu_select" name="menu_id" class="w-full border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">-- Pilih Group --</option>
                        @foreach($menus as $menu)
                            <option value="{{ $menu->id }}" {{ old('menu_id') == $menu->id ? 'selected' : '' }}>{{ $menu->name }}</option>
                        @endforeach
                    </select>
                </div>

                <table class="w-full border text-sm" id="productTable">
                    <thead class="bg-gray-100 text-center">
                        <tr>
                            <th class="border px-2 py-1 text-left">Nama Menu</th>
                            <th class="border px-2 py-1">Create</th>
                            <th class="border px-2 py-1">View</th>
                            <th class="border px-2 py-1">Update</th>
                            <th class="border px-2 py-1">Delete</th>
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
            title.innerText = 'Tambah Role';
            form.action = '/role-user';
            method.value = 'POST';

            document.querySelector('#productTable tbody').innerHTML = '';
            

        }

        if (mode === 'view') {
            title.innerText = 'Lihat Role';
            form.action = '#';
            method.value = '';
            data.disabled = 'view';
            fillForm(data);
            form.querySelectorAll('input[type="text"]').forEach(i => i.setAttribute('readonly', true));
            footer.classList.add('hidden');
        }

        if (mode === 'edit') {
            title.innerText = 'Edit Role';
            form.action = `/role-user/${data.id}`;
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
        const disabled = data.disabled === 'view'; // true jika view mode   

        // Set group select value
        document.getElementById('user_group_select').value = data.group_user_id;
        document.getElementById('menu_select').value = data.group_user_id;

        // Jika perlu disable select juga
        document.getElementById('user_group_select').disabled = disabled;
        document.getElementById('menu_select').disabled = disabled;

        const tbody = document.querySelector('#productTable tbody');
        tbody.innerHTML = '';

        data.details.forEach((item, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="border px-2 py-1">${item.menu_name}</td>
                <td class="border px-2 py-1 text-center">
                    <input type="checkbox" name="permissions[${item.menu_id}][can_create]" ${item.can_create ? 'checked' : ''} ${disabled ? 'disabled' : ''}>
                </td>
                <td class="border px-2 py-1 text-center">
                    <input type="checkbox" name="permissions[${item.menu_id}][can_view]" ${item.can_view ? 'checked' : ''} ${disabled ? 'disabled' : ''}>
                </td>
                <td class="border px-2 py-1 text-center">
                    <input type="checkbox" name="permissions[${item.menu_id}][can_update]" ${item.can_update ? 'checked' : ''} ${disabled ? 'disabled' : ''}>
                </td>
                <td class="border px-2 py-1 text-center">
                    <input type="checkbox" name="permissions[${item.menu_id}][can_delete]" ${item.can_delete ? 'checked' : ''} ${disabled ? 'disabled' : ''}>
                </td>
            `;
            tbody.appendChild(row);
        });
        

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
        fetch('/role-user/create')
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
            const response = await fetch(`/role-user/${menuId}/edit`);
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
            const response = await fetch(`/role-user/${menuId}`, {
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

    let rowCount = 0;


    document.getElementById('menu_select').addEventListener('change', async function () {
        const groupId = this.value;

        if (!groupId) {
            document.querySelector("#productTable tbody").innerHTML = '';
            return;
        }

        showFullscreenLoader();

        try {
            const response = await fetch(`/role-user/${groupId}/menus`);
            if (!response.ok) throw new Error('Gagal ambil data menu');

            const menus = await response.json();
            populateMenuTableFromGroup(menus); // Panggil fungsi buat render
        } catch (error) {
            alert('Terjadi kesalahan saat memuat menu');
            console.error(error);
        } finally {
            hideFullscreenLoader();
        }
    });

    function populateMenuTableFromGroup(menus) {
        const tbody = document.querySelector("#productTable tbody");
        tbody.innerHTML = "";

        menus.forEach(menu => {
            const id = menu.id;

            const row = document.createElement("tr");
            row.classList.add("text-center");

            row.innerHTML = `
                <td class="border px-2 py-1 text-left">
                    <input type="hidden" name="permissions[${id}][menu_id]" value="${id}">
                    ${menu.name}
                </td>
                <td class="border px-2 py-1">
                    <input type="checkbox" name="permissions[${id}][can_create]" value="1">
                </td>
                <td class="border px-2 py-1">
                    <input type="checkbox" name="permissions[${id}][can_view]" value="1">
                </td>
                <td class="border px-2 py-1">
                    <input type="checkbox" name="permissions[${id}][can_update]" value="1">
                </td>
                <td class="border px-2 py-1">
                    <input type="checkbox" name="permissions[${id}][can_delete]" value="1">
                </td>
            `;

            tbody.appendChild(row);
        });
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
