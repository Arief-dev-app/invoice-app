@extends('layouts.app')

@section('content')
<div class="w-full px-4 py-6">
    <div class="bg-white shadow rounded p-6">
        <h2 class="text-2xl font-bold mb-4">Data Perusahaan</h2>

        @if (session('success'))
            <div class="mb-4 text-green-600 font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('company.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block font-medium">Nama Usaha</label>
                <input type="text" name="nama_usaha" value="{{ old('nama_usaha', $company->nama_usaha ?? '') }}" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block font-medium">Nama Person</label>
                <input type="text" name="nama_person" value="{{ old('nama_person', $company->nama_person ?? '') }}" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block font-medium">Alamat</label>
                <textarea name="alamat" rows="3" class="w-full border rounded p-2">{{ old('alamat', $company->alamat ?? '') }}</textarea>
            </div>

            <div>
                <label class="block font-medium">No HP</label>
                <input type="text" name="no_hp" value="{{ old('no_hp', $company->no_hp ?? '') }}" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email', $company->email ?? '') }}" class="w-full border rounded p-2">
            </div>

            <input type="hidden" name="user_id" value="{{ auth()->id() }}">

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update</button>
        </form>
    </div>
</div>
@endsection
