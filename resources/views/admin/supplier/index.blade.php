@extends('layouts.app')

@section('content')
    @include('admin.supplier.form')
@endsection

@push('scripts')
<script>
    // ===============================
    // 1. VARIABEL GLOBAL
    // ===============================
    let perPage = 10;
    let deleteCallback = null;

    const tableBody = document.getElementById('productTableBody');
    const searchInput = document.getElementById('searchInput');
    const perPageSelect = document.getElementById('perPageSelect');

    // ===============================
    // 2. UTILITY FUNCTIONS
    // ===============================
    function showLoader() {
        document.getElementById('fullscreenLoader').classList.remove('hidden');
    }

    function hideLoader() {
        document.getElementById('fullscreenLoader').classList.add('hidden');
    }

    function showSuccessAlert(message) {
        const alert = document.createElement('div');
        alert.id = 'successAlert';
        alert.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded shadow-lg flex items-center space-x-2 z-50 transition-opacity duration-500';
        alert.innerHTML = `
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span>${message}</span>
        `;
        document.body.appendChild(alert);
        setTimeout(() => {
            alert.classList.add('opacity-0');
            setTimeout(() => alert.remove(), 500);
        }, 2000);
    }

    function renderDataSummary(data) {
        const summaryEl = document.getElementById('dataSummary');
        if (data.total > 0) {
            const start = data.from ?? 1;
            const end = data.to ?? data.data.length;
            const total = data.total;
            summaryEl.textContent = `Menampilkan ${start} - ${end} dari ${total} data`;
        } else {
            summaryEl.textContent = 'Tidak ada data';
        }
    }

    // ===============================
    // 3. MODAL HANDLING
    // ===============================
    function openGlobalModal(title = 'Tambah Produk', data = null, mode = 'create') {
        const modal = document.getElementById('globalModal');
        const form = document.getElementById('productForm');
        const methodInput = document.getElementById('formMethod');
        const namaInput = document.getElementById('formNama');
        const emailInput = document.getElementById('formEmail');
        const phoneInput = document.getElementById('formPhone');
        const alamatInput = document.getElementById('formAlamat');
        const simpanBtn = form.querySelector('button[type="submit"]');

        document.getElementById('globalModalTitle').innerText = title;
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        if (mode === 'edit' && data) {
            form.action = `/supplier/${data.id}`;
            methodInput.value = 'PUT';
            namaInput.value = data.name ?? '';
            emailInput.value = data.email ?? '';
            phoneInput.value = data.phone ?? '';
            alamatInput.value = data.address ?? '';
            simpanBtn.classList.remove('hidden');
            namaInput.readOnly = false;
            emailInput.readOnly = false;
            phoneInput.readOnly = false;
            alamatInput.readOnly = false;
        } else if (mode === 'view' && data) {
            form.action = '#';
            methodInput.value = 'GET';
            namaInput.value = data.name ?? '';
            emailInput.value = data.email ?? '';
            phoneInput.value = data.phone ?? '';
            alamatInput.value = data.address ?? '';
            simpanBtn.classList.add('hidden');
            namaInput.readOnly = true;
            emailInput.readOnly = true;
            phoneInput.readOnly = true;
            alamatInput.readOnly = true;
        } else {
            form.action = `/supplier`;
            methodInput.value = 'POST';
            namaInput.value = '';
            emailInput.value = '';
            phoneInput.value = '';
            alamatInput.value = '';
            simpanBtn.classList.remove('hidden');
            namaInput.readOnly = false;
            emailInput.readOnly = false;
            phoneInput.readOnly = false;
            alamatInput.readOnly = false;
        }
    }

    function closeGlobalModal() {
        const modal = document.getElementById('globalModal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    // ===============================
    // 4. FORM SUBMIT
    // ===============================
    document.addEventListener('DOMContentLoaded', () => {
        loadProducts();

        let debounce;
        searchInput.addEventListener('input', () => {
            clearTimeout(debounce);
            debounce = setTimeout(() => {
                loadProducts(searchInput.value.trim());
            }, 300);
        });

        perPageSelect.value = perPage;
        perPageSelect.addEventListener('change', () => {
            perPage = perPageSelect.value;
            loadProducts(searchInput.value.trim());
        });

        const form = document.getElementById('productForm');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            showLoader();

            const data = {
                name: form.name.value,
                email: form.email.value,
                phone: form.phone.value,
                address: form.address.value,
            };

            const method = document.getElementById('formMethod').value;
            const url = form.action;

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(data),
                });

                if (!res.ok) throw await res.json();

                const result = await res.json();
                closeGlobalModal();
                showSuccessAlert(result.message);
                setTimeout(() => location.reload(), 500);

            } catch (err) {
                alert(err.message || 'Gagal menyimpan data.');
            } finally {
                hideLoader();
            }
        });
    });

    // ===============================
    // 5. DATA FETCHING
    // ===============================
    function loadProducts(search = '') {
        const url = `/supplier?search=${search}&per_page=${perPage}`;
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                renderProducts(data);
                renderPagination(data);
                renderDataSummary(data);
            })
            .catch(err => {
                tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-red-500">Gagal memuat data</td></tr>`;
                console.error(err);
            });
    }

    function loadProductsByUrl(url) {
        const finalUrl = url.includes('?') ? `${url}&per_page=${perPage}` : `${url}?per_page=${perPage}`;
        fetch(finalUrl, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                renderProducts(data);
                renderPagination(data);
                renderDataSummary(data);
            })
            .catch(err => {
                tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-red-500">Gagal memuat data</td></tr>`;
                console.error(err);
            });
    }

    function renderProducts(data) {
        let html = '';
        data.data.forEach((data, index) => {
            html += `
                <tr>
                    <td class="p-2 border">${index + 1}</td>
                    <td class="p-2 border space-x-2">
                        <button onclick="fetchMenuAndOpenModal('Lihat Produk', ${data.id}, 'view')">👁</button>
                        <button onclick="fetchMenuAndOpenModal('Edit Produk', ${data.id}, 'edit')">✏️</button>
                        <button onclick="handleDeleteAction(() => deleteMenu(${data.id}))">🗑</button>
                    </td>
                    <td class="p-2 border">${data.name}</td>
                    <td class="p-2 border">${(data.email)}</td>
                    <td class="p-2 border">${(data.phone)}</td>
                </tr>
            `;
        });
        tableBody.innerHTML = html || `<tr><td colspan="5" class="text-center text-gray-400">Tidak ada data</td></tr>`;
    }

    function renderPagination(data) {
        const paginationContainer = document.getElementById('paginationLinks');
        const current = data.current_page;
        const last = data.last_page;
        let html = '';

        const addButton = (page, label = null, active = false, disabled = false) => {
            html += `
                <button
                    class="px-3 py-1 rounded border text-sm ${active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-200'} ${disabled ? 'cursor-not-allowed bg-gray-100 text-gray-400' : ''}"
                    ${!disabled ? `onclick="loadProductsByUrl('/supplier?page=${page}&per_page=${perPage}')"` : 'disabled'}>
                    ${label ?? page}
                </button>
            `;
        };

        const addEllipsis = () => {
            html += `<span class="px-2 text-gray-500 select-none">...</span>`;
        };

        // ← Previous
        addButton(current - 1, '←', false, current === 1);

        if (last <= 5) {
            for (let i = 1; i <= last; i++) {
                addButton(i, i, i === current);
            }
        } else if (current <= 2) {
            // 1 2 3 ... last
            for (let i = 1; i <= 3; i++) {
                addButton(i, i, i === current);
            }
            addEllipsis();
            addButton(last, last, current === last);
        } else if (current >= last - 1) {
            // 1 ... last-2 last-1 last
            addButton(1, 1);
            addEllipsis();
            for (let i = last - 2; i <= last; i++) {
                addButton(i, i, i === current);
            }
        } else if (current === last - 2) {
            // 1 ... current current+1 current+2
            addButton(1, 1);
            addEllipsis();
            addButton(current, current, true);
            addButton(current + 1, current + 1);
            addButton(current + 2, current + 2);
        } else {
            // current-1 current current+1 ... last
            addButton(current - 1, current - 1);
            addButton(current, current, true);
            addButton(current + 1, current + 1);
            addEllipsis();
            addButton(last, last);
        }

        // → Next
        addButton(current + 1, '→', false, current === last);

        paginationContainer.innerHTML = html;
    }   

    // ===============================
    // 6. DELETE HANDLING
    // ===============================
    function handleAddGroupClick() {
        showLoader();
        setTimeout(() => {
            hideLoader();
            openGlobalModal('Tambah Produk');
        }, 300);
    }

    async function fetchMenuAndOpenModal(title, id, mode = 'view') {
        try {
            showLoader();
            const res = await fetch(`/supplier/${id}`);
            if (!res.ok) throw new Error('Gagal mengambil data produk');
            const product = await res.json();
            hideLoader();
            openGlobalModal(title, product, mode);
        } catch (err) {
            hideLoader();
            alert(err.message);
        }
    }

    function handleDeleteAction(callback) {
        deleteCallback = callback;
        document.getElementById('confirmDeleteModal').classList.remove('hidden');
        document.getElementById('confirmDeleteModal').classList.add('flex');
    }

    function cancelDelete() {
        deleteCallback = null;
        document.getElementById('confirmDeleteModal').classList.add('hidden');
        document.getElementById('confirmDeleteModal').classList.remove('flex');
    }

    function confirmDelete() {
        if (typeof deleteCallback === 'function') {
            deleteCallback();
        }
        cancelDelete();
    }

    async function deleteMenu(id) {
        showLoader();
        try {
            const res = await fetch(`/supplier/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ _method: 'DELETE' })
            });
            const result = await res.json();
            if (res.ok) {
                showSuccessAlert(result.message || 'Produk berhasil dihapus');
                setTimeout(() => window.location.reload(), 500);
            } else {
                alert(result.message || 'Gagal menghapus produk.');
            }
        } catch (err) {
            console.error('Gagal hapus:', err);
            alert('Terjadi kesalahan saat menghapus.');
        } finally {
            hideLoader();
        }
    }
</script>
@endpush


