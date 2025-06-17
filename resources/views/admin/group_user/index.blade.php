@extends('layouts.app')

@section('content')
<div class="px-4 py-6">
    <div class="bg-white shadow rounded p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Daftar Group User</h2>
            <button onclick="openGroupUserModal('create')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ Tambah Group</button>
        </div>

        <table class="min-w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1 w-12 text-center">No</th>
                    <th class="border px-2 py-1">Aksi</th>
                    <th class="border px-2 py-1">Nama Group</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groupUsers as $i => $group)
                <tr>
                    <td class="border px-2 py-1">{{ $i + 1 }}</td>
                    <td class="border px-2 py-1 space-x-2">
                        <button onclick='openGroupUserModal("view", {!! json_encode($group) !!})'>👁</button>
                        <button onclick='openGroupUserModal("edit", {!! json_encode($group) !!})'>✏️</button>
                        <form action="{{ route('group-user.destroy', $group->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Yakin hapus?')" class="text-red-500">🗑</button>
                        </form>
                    </td>
                    <td class="border px-2 py-1">{{ $group->name }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="groupUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold" id="modalTitle">Tambah Group</h3>
            <button onclick="closeGroupUserModal()" class="text-gray-500 hover:text-black">&times;</button>
        </div>

        <form id="groupUserForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="method" value="POST">

            <div class="mb-4">
                <label class="block text-sm font-medium">Nama Group</label>
                <input type="text" name="name" id="name" class="w-full border rounded px-3 py-2">
            </div>

            <!-- Footer -->
            <div class="flex justify-end space-x-2 mt-4 border-t pt-4" id="modalFooter">
                <button type="button" onclick="closeGroupUserModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Batal</button>
                <button type="submit" form="groupUserForm" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
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

        form.reset();
        namaInput.removeAttribute('readonly');
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
            namaInput.setAttribute('readonly', true);
            footer.classList.add('hidden');
        }

        if (mode === 'edit') {
            title.innerText = 'Edit Group';
            form.action = `/group-user/${data.id}`;
            method.value = 'PUT';
            namaInput.value = data.name || '';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeGroupUserModal() {
        const modal = document.getElementById('groupUserModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush
