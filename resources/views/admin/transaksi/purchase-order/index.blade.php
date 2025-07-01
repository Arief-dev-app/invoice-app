@extends('layouts.app')

@section('content')
    @include('admin.transaksi.purchase-order.form')
@endsection

@push('scripts')
<script>
    // ===============================
    // 1. VARIABEL GLOBAL
    // ===============================
    let url = "{{ $url }}";
    let perPage = 10;
    let deleteCallback = null;

    const statusMap = {
        1: 'Open',
        2: 'Cancel',
        3: 'Confirm',
        4: 'Partial',
        5: 'Finish'
    };

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
    
    function showErrorAlert(message) {
        const alert = document.createElement('div');
        alert.id = 'errorAlert';
        alert.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded shadow-lg flex items-center space-x-2 z-50 transition-opacity duration-500';
        alert.innerHTML = `
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
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

    function renderDetailItems(details) {
        const tbody = document.querySelector('#productTable tbody');
        tbody.innerHTML = '';
        rowCount = 0;

        details.forEach((item) => {
            const product = products.find(p => p.id === item.product_id);
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="border px-2 py-1 text-center">
                    <button type="button" onclick="this.closest('tr').remove()" class="text-red-500">🗑</button>
                </td>
                <td class="border px-2 py-1">
                    <select name="items[${rowCount}][product_id]" class="w-full border rounded px-2 py-1" onchange="updateTotal(this)">
                        <option value="">-- Pilih Produk --</option>
                        ${products.map(p => `
                            <option value="${p.id}" data-harga="${p.harga}" ${p.id === item.prd_id ? 'selected' : ''}>${p.nama}</option>
                        `).join('')}
                    </select>
                </td>
                <td class="border px-2 py-1">
                    <input type="number" name="items[${rowCount}][stock]" value="${item.prd?.stock ?? 0}" class="w-full border rounded px-2 py-1" onchange="updateTotal(this)">
                </td>
                <td class="border px-2 py-1">
                    <input type="number" name="items[${rowCount}][qty]" value="${item.qty}" class="w-full border rounded px-2 py-1" onchange="updateTotal(this)">
                </td>
            `;
            tbody.appendChild(row);
            rowCount++;
        });
    }

    // ===============================
    // 3. MODAL HANDLING
    // ===============================
    async function openGlobalModal(title = 'Tambah {{ $title }}', data = null, mode = 'create') {
        const modal = document.getElementById('globalModal');
        const form = document.getElementById('productForm');
        const methodInput = document.getElementById('formMethod');
        const formTrans = document.getElementById('formTrans');
        const formTransDate = document.getElementById('formTransDate');
        const alamatInput = document.getElementById('formAlamat');
        const simpanBtn = document.getElementById('btnSimpan');
        const btnConfirm = document.getElementById('btnConfirm');
        const formSupplier = document.getElementById('supplier_select');     

       

        document.getElementById('globalModalTitle').innerText = title;
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        if (mode === 'edit' && data) {

            let postData = {
                url: "{{ $url }}", 
                kode: "{{ 'edit' }}"   
            };


            const { response, data: aksesData } = await cekAkses(`${postData.url}/cekAkses`, postData);
            if (simpanBtn) {
                if (mode === 'edit' && aksesData.status !== 'error') {
                    simpanBtn.classList.remove('hidden');
                } else {
                    simpanBtn.classList.add('hidden');
                }
            }
            window.currentTransId = data.id;

            if (btnConfirm) {
                if (data.po_status < 2) {
                    btnConfirm.classList.remove('hidden');
                } else {
                    btnConfirm.classList.add('hidden');
                }
            }

            form.action = `${url}/${data.id}`;
            methodInput.value = 'PUT';
            formTrans.value = data.purchase_no ?? '';
            formTransDate.value = data.transaction_date ?? '';            
            formSupplier.value = data.supplier_id ?? '';
            formTrans.readOnly = true;
            formTransDate.readOnly = false;
            formSupplier.readonly = false;

            renderDetailItems(data.detail ?? []);

        } else if (mode === 'view' && data) {
            form.action = '#';
            methodInput.value = 'GET';
            formTrans.value = data.purchase_no ?? '';
            formTransDate.value = data.transaction_date ?? '';
            formSupplier.value = data.supplier_id ?? ''; 
            simpanBtn.classList.add('hidden');
            formTrans.readOnly = true;
            formTransDate.readOnly = true;
            formSupplier.readonly = true;

            window.currentTransId = data.id;

            if (btnConfirm) {
                if (data.po_status < 2) {
                    btnConfirm.classList.remove('hidden');
                } else {
                    btnConfirm.classList.add('hidden');
                }
            }
            renderDetailItems(data.detail ?? []);

        } else {
            const today = new Date().toISOString().split('T')[0];
            
            form.action = url;
            methodInput.value = 'POST';
            formTrans.value = '';
            formSupplier.value = ''; // reset supplier
            formTransDate.value = today;
            const detailTbody = document.querySelector('#productDetailTableBody');
            detailTbody.innerHTML = '';
            
            rowCount = 0;
            
            
            simpanBtn.classList.remove('hidden');
            btnConfirm.classList.add('hidden');
            formTrans.readOnly = true;
            
            
        
        }
    }

    function closeGlobalModal() {
        const modal = document.getElementById('globalModal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    let products = @json($products);
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
                <input type="number" name="items[${rowCount}][stock]" value="0" class="w-full border rounded px-2 py-1" onchange="updateTotal(this)">
            </td>

            <td class="border px-2 py-1">
                <input type="number" name="items[${rowCount}][qty]" value="1" class="w-full border rounded px-2 py-1" onchange="updateTotal(this)">
            </td>
            
            
        `;
        tbody.appendChild(row);
        rowCount++;
    }

    function updateTotal(el) {
        const row = el.closest('tr');
        const select = row.querySelector('select');
        const stockInput = row.querySelector('input[name$="[stock]"]');
        const qtyInput = row.querySelector('input[name$="[qty]"]');

        const productId = parseInt(select.value);

        // Cari produk yang dipilih berdasarkan ID
        const selectedProduct = products.find(p => p.id === productId);

        if (selectedProduct) {
            stockInput.value = parseInt(selectedProduct.stock ?? 0);
            // stockInput.value = selectedProduct.stock ?? 0;
        } else {
            stockInput.value = 0;
        }
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

            const formData = new FormData(form);

            const data = {
                // trans: form.trans.value,
                trans_date: form.trans_date.value,
                supplier_id: form.supplier_id.value,
                items: []
                
            };

             // Ambil detail items
            const itemMap = {};

            for (let [key, value] of formData.entries()) {
                const match = key.match(/^items\[(\d+)]\[(\w+)]$/);
                if (match) {
                    const index = match[1];
                    const field = match[2];

                    if (!itemMap[index]) itemMap[index] = {};
                    itemMap[index][field] = value;
                }
            }

            data.items = Object.values(itemMap);

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

        const btnConfirm = document.getElementById('btnConfirm');
        if (btnConfirm) {
            btnConfirm.addEventListener('click', () => {
                if (!window.currentTransId) {
                    alert("ID transaksi tidak ditemukan");
                    return;
                }

                // Tampilkan modal konfirmasi
                document.getElementById('confirmTransModal').classList.remove('hidden');
            });
        }
    });

    // ===============================
    // 5. DATA FETCHING
    // ===============================
    function loadProducts(search = '') {
        const url2 = `${url}?search=${search}&per_page=${perPage}`;
        fetch(url2, { headers: { 'Accept': 'application/json' } })
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
                        <button 
                            onclick="fetchMenuAndOpenModal('Edit Produk', ${data.id}, 'edit')"
                            ${data.po_status != 1 ? 'disabled class="opacity-50 cursor-not-allowed"' : ''}>
                            ✏️
                        </button>

                        <button 
                            onclick="handleDeleteAction(() => deleteMenu(${data.id}))"
                            ${data.po_status != 1 ? 'disabled class="opacity-50 cursor-not-allowed"' : ''}>
                            🗑
                        </button>
                    </td>
                    <td class="p-2 border">${data.purchase_no}</td>
                    <td class="p-2 border">
                    ${statusMap[data.po_status] ?? 'Unknown'}
                    </td>
                    <td class="p-2 border">${(data.transaction_date)}</td>
                    <td class="p-2 border">${(data.suplier.name)}</td>
                    <td class="p-2 border">${(data.total)}</td>
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
                    ${!disabled ? `onclick="loadProductsByUrl('${url}?page=${page}&per_page=${perPage}')"` : 'disabled'}>
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
    async function handleAddGroupClick() {
        // showLoader();
        // setTimeout(() => {
        //     hideLoader();
        //     openGlobalModal('Tambah Produk');
        // }, 300);
        let postData = {
            url: "{{ $url }}",    
            kode: "{{ 'create' }}"   
        };

        showLoader();

        try {

            const { response, data } = await cekAkses(`${postData.url}/cekAkses`, postData);

            hideLoader();

            if (!response.ok) {
                showErrorAlert(data.message || 'Terjadi kesalahan');
                return; // hentikan eksekusi
            }

            if (data.status === 'ok') {
                openGlobalModal('{{ $title }}');
            }


        }catch (err) {
            hideLoader();
            showErrorAlert(err.message || 'Terjadi kesalahan saat memproses');
        }

    }

    async function cekAkses(endpoint, data) {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });

        const json = await response.json();
        return { response, data: json };
    }
    

    async function fetchMenuAndOpenModal(title, id, mode = 'view') {
        
        let postData = {
            url: "{{ $url }}",     
            kode: "{{ 'view' }}"   
        };

        try {
            showLoader();
            const { response, data } = await cekAkses(`${postData.url}/cekAkses`, postData);

            hideLoader();

            if (!response.ok) {
                showErrorAlert(data.message || 'Terjadi kesalahan');
                return;
            }

            if (data.status === 'ok') {
                
                const res = await fetch(`${url}/${id}`);
                if (!res.ok) throw new Error('Gagal mengambil data produk');
                console.log(res);
                const product = await res.json();
                hideLoader();
                openGlobalModal(title, product, mode);
            }


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

    function cancelConfirm() {
        document.getElementById('confirmTransModal').classList.add('hidden');
        currentTransId = null;
    }

    async function confirmTransaction() {
        showLoader();
        if (!currentTransId) return;

        try {
            let postData = {
                url: "{{ $url }}", 
                kode: "{{ 'delete' }}"
            };

            const { response, data } = await cekAkses(`${postData.url}/cekAkses`, postData);

            if (!response.ok) {
                showErrorAlert(data.message || 'Terjadi kesalahan');
                return;
            }

            if (data.status === 'ok') {      
            
                const response = await fetch(`${url}/${currentTransId}/confirm`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    }
                });
    
                const result = await response.json();
                if (result.status === 'success') {
                    showSuccessAlert(result.message || 'Transaksi berhasil diconfirm');
                    location.reload();
                } else {
                    showSuccessAlert(result.message || 'Proses Confirm Gagal');
                }
            }

        } catch (error) {
            console.error(error);
            alert('Terjadi kesalahan saat konfirmasi.');
        } finally {
            hideLoader();
        }
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

            let postData = {
                url: "{{ $url }}", 
                kode: "{{ 'delete' }}"
            };

            const { response, data } = await cekAkses(`${postData.url}/cekAkses`, postData);

            if (!response.ok) {
                showErrorAlert(data.message || 'Terjadi kesalahan');
                return;
            }

            if (data.status === 'ok') {         
                const res = await fetch(`${url}/${id}`, {
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


