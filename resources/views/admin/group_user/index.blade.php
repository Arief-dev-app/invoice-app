@extends('layouts.app')

@section('content')
<div class="px-4 py-6">
    <div class="bg-white shadow rounded p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Daftar Group User</h2>
            <button id="addGroupBtn" onclick="handleAddGroupClick()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                + Tambah Group
            </button>
        </div>

        @if (session('success'))
            <div id="successAlert" class="fixed top-4 left-50 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded shadow-lg flex items-center space-x-2 z-50 transition-opacity duration-500">
                <!-- Ikon centang hijau (SVG) -->
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>

                <!-- Teks pesan -->
                <span>{{ session('success') }}</span>
            </div>

            <script>
                setTimeout(() => {
                    const alert = document.getElementById('successAlert');
                    if (alert) {
                        alert.classList.add('opacity-0');
                        setTimeout(() => alert.remove(), 500); // hilang setelah animasi
                    }
                }, 2000);
            </script>
        @endif


        <table class="min-w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1 w-12 text-center">No</th>
                    <th class="border px-2 py-1">Aksi</th>
                    <th class="border px-2 py-1">Nama</th>
                    <th class="border px-2 py-1">Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groupUsers as $i => $group)
                <tr>
                    <td class="border px-2 py-1">{{ $i + 1 }}</td>
                    <td class="border px-2 py-1 space-x-2">
                    <button onclick='handleModalAction("view", {!! json_encode($group) !!})'>👁</button>
                    <button onclick='handleModalAction("edit", {!! json_encode($group) !!})'>✏️</button>
                        @if ($group->flag_active)
                        <form action="{{ route('group-user.destroy', $group->id) }}" method="POST" class="inline" onsubmit="handleDeleteAction(event, this)">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500">🗑</button>
                        </form>
                        @else
                        <form action="{{ route('group-user.restore', $group->id) }}" method="POST" class="inline" onsubmit="handleRestoreAction(event, this)">
                            @csrf
                            <button type="submit" class="text-green-600">✅</button>
                        </form>
                        @endif
                    </td>
                    <td class="border px-2 py-1">{{ $group->name }}</td>
                    <td class="border px-2 py-1">{{ $group->description }}</td>
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

<div id="groupUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold" id="modalTitle">Tambah Group</h3>
            <button onclick="closeGroupUserModal()" class="text-gray-500 hover:text-black">&times;</button>
        </div>

        <form id="groupUserForm" method="POST"  onsubmit="handleFormSubmit(event)">
            @csrf
            <input type="hidden" name="_method" id="method" value="POST">

            <div class="mb-4">
                <label class="block text-sm font-medium">Nama Group</label>
                <input type="text" name="name" id="name" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Deskription</label>
                <input type="text" name="description" id="description" class="w-full border rounded px-3 py-2">
            </div>

            <!-- Footer -->
            <div class="flex justify-end space-x-2 mt-4 border-t pt-4" id="modalFooter">
                <button type="button" onclick="closeGroupUserModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Batal</button>
                <button type="submit" form="groupUserForm" id="submitBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    
    function openGroupUserModal(mode, data = null) {
        const modal = document.getElementById('groupUserModal');
        const title = document.getElementById('modalTitle');
        const form = document.getElementById('groupUserForm');
        const method = document.getElementById('method');
        const footer = document.getElementById('modalFooter');
        const namaInput = document.getElementById('name');
        const descriptionInput = document.getElementById('description');

        form.reset();
        namaInput.removeAttribute('readonly');
        descriptionInput.removeAttribute('readonly');
        footer.classList.remove('hidden');

        if (mode === 'create') {
            title.innerText = 'Tambah Group';
            form.action = '/group-user';
            method.value = 'POST';
        }

        if (mode === 'view') {
            title.innerText = 'Lihat Group';
            form.action = '#';
            method.value = '';
            namaInput.value = data.name || '';
            descriptionInput.value = data.description || '';
            namaInput.setAttribute('readonly', true);
            footer.classList.add('hidden');
        }

        if (mode === 'edit') {
            title.innerText = 'Edit Group';
            form.action = `/group-user/${data.id}`;
            method.value = 'PUT';
            namaInput.value = data.name || '';
            descriptionInput.value = data.description || '';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeGroupUserModal() {
        const modal = document.getElementById('groupUserModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
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
            openGroupUserModal('create');
        }, 2000);
    }

    function handleModalAction(mode, data = null) {
        showFullscreenLoader();
        setTimeout(() => {
            hideFullscreenLoader();
            openGroupUserModal(mode, data);
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
@endpush
