@section('title', 'Tambah Bahan | BE INVENTORY')
<x-app-layout>
    @props(['variant' => ''])
    <x-app.secondary-header :variant="$attributes['headerVariant']">
        <div class="flex">
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <nav class="flex flex-wrap" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                        <li class="inline-flex items-center">
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                                <svg class="w-3 h-3 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                                </svg>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                </svg>
                                <a href="{{ route('bahan.index') }}" class="ms-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ms-2 dark:text-gray-400 dark:hover:text-white">Bahan</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                </svg>
                                <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2 dark:text-gray-400">Tambah Bahan</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Header: Right side -->
        <div class="flex items-center space-x-3">
            <div class="p-1 flex items-center justify-end gap-x-2">
                <a href="{{ route('bahan.index') }}" type="button" class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Kembali</a>
                <button id="saveButton" type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Simpan</button>
            </div>
        </div>
    </x-app.secondary-header>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <div class="w-full bg-white border border-gray-200 rounded-lg shadow sm:p-8 dark:bg-gray-800 dark:border-gray-700">
            <form action="{{ route('bahan.store') }}" method="POST" enctype="multipart/form-data" id="bahanForm">
                @csrf
                <div class="space-y-12">
                    <div class="border-b border-gray-900/10 pb-12">
                        <div class="p-4 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                            <div class="sm:col-span-2 sm:col-start-1">
                            <label for="kode_bahan" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">Kode Bahan</label>
                            <div class="mt-2">
                                <input value="{{ old('kode_bahan') }}" type="text" name="kode_bahan" id="kode_bahan" autocomplete="address-level2" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:text-gray-400 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                                @error('kode_bahan')
                                    <p class="text-red-500 text-sm mt-1 error-message">{{ $message }}</p>
                                @enderror
                            </div>
                            </div>

                            <div class="sm:col-span-2">
                            <label for="nama_bahan" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">Nama Bahan</label>
                            <div class="mt-2">
                                <input value="{{ old('nama_bahan') }}" type="text" name="nama_bahan" id="nama_bahan" autocomplete="address-level1" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:text-gray-400 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                                @error('nama_bahan')
                                    <p class="text-red-500 text-sm mt-1 error-message">{{ $message }}</p>
                                @enderror
                            </div>

                            </div>
                            <div class="sm:col-span-2">
                                <label for="nama_bahan" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">Jenis Bahan</label>
                                <div class="mt-2">
                                    <select id="jenis_bahan_id" name="jenis_bahan_id" autocomplete="country-name" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:text-gray-400 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                                        <option value="" disabled selected>Pilih Jenis Bahan</option>
                                        @foreach($jenisBahan as $jenis)
                                            <option value="{{ $jenis->id }}" {{ old('jenis_bahan_id') == $jenis->id ? 'selected' : '' }}>{{ $jenis->nama }}</option>
                                        @endforeach
                                    </select>
                                    @error('jenis_bahan_id')
                                        <p class="text-red-500 text-sm mt-1 error-message">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- <div class="sm:col-span-2">
                            <label for="stok_awal" class="block text-sm font-medium leading-6 text-gray-900">Stok Awal</label>
                            <div class="mt-2">
                                <input value="{{ old('stok_awal') }}" type="number" name="stok_awal" id="stok_awal" autocomplete="address-level2" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                @error('stok_awal')
                                    <p class="text-red-500 text-sm mt-1 error-message">{{ $message }}</p>
                                @enderror
                            </div>
                            </div> --}}

                            <div class="sm:col-span-2">
                            <label for="gambar" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">Gambar</label>
                            <div class="mt-2">
                                <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 file:rounded-lg file:w-24 file:h-9" id="gambar" name="gambar" type="file" accept=".png, .jpg, .jpeg">
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file_input_help">PNG, JPG or JPEG (MAX. 2 MB).</p>
                                @error('gambar')
                                    <p class="text-red-500 text-sm mt-1 error-message">{{ $message }}</p>
                                @enderror
                            </div>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="unit_id" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">Unit</label>
                                <div class="mt-2">
                                    <select id="unit_id" name="unit_id" autocomplete="country-name" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:text-gray-400 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                                        <option value="" disabled selected>Pilih Satuan Unit</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->nama }}</option>
                                        @endforeach
                                    </select>
                                    @error('unit_id')
                                        <p class="text-red-500 text-sm mt-1 error-message">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                            <label for="penempatan" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">Penempatan</label>
                            <div class="mt-2">
                                <input value="{{ old('penempatan') }}" type="text" name="penempatan" id="penempatan" autocomplete="street-address" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:text-gray-400 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                                @error('penempatan')
                                    <p class="text-red-500 text-sm mt-1 error-message">{{ $message }}</p>
                                @enderror
                            </div>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="supplier_id" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">Supplier</label>
                                <div class="mt-2">
                                    <select id="supplier_id" name="supplier_id" autocomplete="country-name" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 dark:text-gray-400 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                                        <option value="" selected>Pilih Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->nama }}</option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id')
                                        <p class="text-red-500 text-sm mt-1 error-message">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- <div class="col-span-full">
                                <label for="gambar" class="block text-sm font-medium leading-6 text-gray-900">Gambar</label>
                                <div class="mt-2 flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10">
                                    <div class="text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                                        </svg>
                                        <div class="mt-4 flex text-sm leading-6 text-gray-600">
                                            <label for="gambar" class="relative cursor-pointer rounded-md bg-white font-semibold text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-500">
                                            <span>Upload a file</span>
                                            <input id="gambar" name="gambar" type="file" class="sr-only" accept=".png, .jpg, .jpeg">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs leading-5 text-gray-600">PNG, JPG, JPEG up to 2MB</p>
                                    </div>
                                    @error('gambar')
                                        <p class="text-red-500 text-sm mt-1 error-message">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </form>
        </div>


    </div>
</x-app-layout>
<script>
    setTimeout(function() {
        const errorMessages = document.querySelectorAll('.error-message');
        errorMessages.forEach(function(message) {
            message.style.display = 'none';
        });
    }, 3000);
</script>
<script>
    document.getElementById('saveButton').addEventListener('click', function() {
        document.getElementById('bahanForm').submit();
    });
</script>
