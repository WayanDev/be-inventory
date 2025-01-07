<!-- Main modal -->
@if($isApprovePurchasingModalOpen)
<div wire:ignore.self id="editpembelianbahanpurchasing-modal" tabindex="-1" aria-hidden="true" class="fixed inset-0 flex items-center justify-center z-50 w-full h-full bg-black bg-opacity-50" wire:click.self="closeModal">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Edit Status
                </h3>
                <button wire:click="closeModal" type="button" class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="editpembelianbahanpurchasing-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="pt-0 p-5">
                <form class="formeditdata space-y-6" method="post" action="{{ route('pengajuan-pembelian-bahan.updateApprovalPurchasing', (int)$id_pembelian_bahan) }}">
                    @csrf
                    {{ method_field('PUT') }}
                    <div>
                        <label for="status_purchasing" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status Purchasing</label>
                        <select wire:model="status_purchasing" name="status_purchasing" id="status_purchasing" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white" required>
                            <option value="" disabled>Pilih Status purchasing</option>
                            <option value="Belum disetujui" {{ $status_purchasing === 'Belum disetujui' ? 'selected' : '' }}>Belum disetujui</option>
                            <option value="Disetujui" {{ $status_purchasing === 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="Ditolak" {{ $status_purchasing === 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full text-white bg-indigo-600 hover:bg-indigo-800 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-indigo-600 dark:hover:bg-indigo-700 dark:focus:ring-indigo-800">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif