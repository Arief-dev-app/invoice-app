@extends('layouts.app')

@section('content')
<div class="px-4 py-6">
    <div class="bg-white shadow rounded p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Daftar Menu</h2>
            <button onclick="openMenuModal('create')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ Tambah Menu</button>
        </div>

        <table class="min-w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1 w-12 text-center">No</th>
                    <th class="border px-2 py-1">Aksi</th>
                    <th class="border px-2 py-1">Nama Menu</th>
                    <th class="border px-2 py-1">URL</th>
                    <th class="border px-2 py-1">Icon</th>
                    <th class="border px-2 py-1">Urutan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($menus as $i => $menu)
                <tr>
                    <td class="border px-2 py-1 text-center">{{ $i + 1 }}</td>
                    <td class="border px-2 py-1 space-x-2">
                        <button onclick='openMenuModal("view", {!! json_encode($menu) !!})'>👁</button>
                        <button onclick='openMenuModal("edit", {!! json_encode($menu) !!})'>✏️</button>
                        <form action="{{ route('menu.destroy', $menu->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Yakin hapus?')" class="text-red-500">🗑</button>
                        </form>
                    </td>
                    <td class="border px-2 py-1">{{ $menu->nama }}</td>
                    <td class="border px-2 py-1">{{ $menu->url }}</td>
                    <td class="border px-2 py-1">{{ $menu->icon }}</td>
                    <td class="border px-2 py-1 text-center">{{ $menu->urutan }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="menuModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
        <!-- Header -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold" id="modalTitle">Tambah Menu</h3>
            <button onclick="closeMenuModal()" class="text-gray-500 hover:text-black">&times;</button>
        </div>

        <form id="menuForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="method" value="POST">

            <div class="mb-2">
                <label class="block text-sm font-medium">Nama Menu</label>
                <input type="text" name="nama" id="nama" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-2">
                <label class="block text-sm font-medium">URL</label>
                <input type="text" name="url" id="url" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-2">
                <label class="block text-sm font-medium">Icon</label>
                <input type="text" name="icon" id="icon" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-2">
                <label class="block text-sm font-medium">Urutan</label>
                <input type="number" name="urutan" id="urutan" class="w-full border rounded px-3 py-2">
            </div>

            <!-- Footer -->
            <div class="flex justify-end space-x-2 mt-4 border-t pt-4" id="modalFooter">
                <button type="button" onclick="closeMenuModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Batal</button>
                <button type="submit" form="menuForm" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Simpan</button>
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
        form.querySelectorAll('input').forEach(i => i.removeAttribute('readonly'));
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
            form.querySelectorAll('input').forEach(i => i.setAttribute('readonly', true));
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
        document.getElementById('nama').value = data.nama || '';
        document.getElementById('url').value = data.url || '';
        document.getElementById('icon').value = data.icon || '';
        document.getElementById('urutan').value = data.urutan || '';
    }
</script>
@endpush
