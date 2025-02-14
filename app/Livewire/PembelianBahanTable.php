<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Pengajuan;
use App\Models\BahanKeluar;
use Livewire\WithPagination;
use App\Models\PembelianBahan;
use Illuminate\Support\Facades\Auth;

class PembelianBahanTable extends Component
{
    use WithPagination;
    public $search = "";
    public $perPage = 25;
    public $id_pembelian_bahan, $status,
    $kode_transaksi, $tgl_keluar, $divisi,$link, $pembelianBahanDetails, $status_pengambilan, $status_leader, $status_purchasing, $status_manager, $status_finance, $status_admin_manager, $ongkir, $asuransi, $layanan, $jasa_aplikasi, $shipping_cost, $full_amount_fee, $value_today_fee, $jenis_pengajuan, $new_shipping_cost, $new_full_amount_fee, $new_value_today_fee, $status_general_manager, $catatan;
    public $filter = 'semua';
    public $totalHarga;
    public $isShowModalOpen = false;
    public $isDeleteModalOpen = false;
    public $isApproveLeaderModalOpen = false;
    public $isApproveGMModalOpen = false;
    public $isApproveManagerModalOpen = false;
    public $isApprovePurchasingModalOpen = false;
    public $isApproveAdminManagerModalOpen = false;
    public $isApproveFinanceModalOpen = false;
    public $isApproveDirekturModalOpen = false;
    public $isShowInvoiceModalOpen = false;
    public $isUploadInvoiceModalOpen = false;
    public $pembelian_bahan;
    public $selectedStatus = [];
    public $selectedTab = 'semua';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function setTab($tab)
    {
        $this->selectedTab = $tab;
    }

    public function mount()
    {
        $this->calculateTotalHarga();
        foreach (PembelianBahan::all() as $bahan) {
            $this->selectedStatus[$bahan->id] = $bahan->status_pembelian;
        }
    }

    public function updateStatus($id)
    {
        $bahan = PembelianBahan::find($id);
        if ($bahan) {
            $bahan->status_pembelian = $this->selectedStatus[$id];
            $bahan->save();

            // Update status di tabel pengajuan
            if ($bahan->pengajuan_id) {
                $pengajuan = Pengajuan::find($bahan->pengajuan_id);
                if ($pengajuan) {
                    $pengajuan->status_pembelian = $bahan->status_pembelian;
                    $pengajuan->save();
                }
            }
        }
    }

    public function setFilter($value)
    {
        if ($value === 'semua') {
            $this->filter = null;
        } else {
            $this->filter = $value;
        }
    }

    public function showPembelianBahan(int $id)
    {
        $Data = PembelianBahan::with('pembelianBahanDetails')->findOrFail($id);
        $this->id_pembelian_bahan = $id;
        $this->tgl_keluar = $Data->tgl_keluar;
        $this->kode_transaksi = $Data->kode_transaksi;
        $this->divisi = $Data->divisi;
        $this->status = $Data->status;
        $this->jenis_pengajuan = $Data->jenis_pengajuan;
        $this->pembelianBahanDetails  = $Data->pembelianBahanDetails;
        $this->ongkir = $Data->ongkir;
        $this->asuransi = $Data->asuransi;
        $this->layanan = $Data->layanan;
        $this->jasa_aplikasi = $Data->jasa_aplikasi;
        $this->shipping_cost = $Data->shipping_cost;
        $this->full_amount_fee = $Data->full_amount_fee;
        $this->value_today_fee = $Data->value_today_fee;

        $this->new_shipping_cost = $Data->new_shipping_cost;
        $this->new_full_amount_fee = $Data->new_full_amount_fee;
        $this->new_value_today_fee = $Data->new_value_today_fee;
        $this->isShowModalOpen = true;
    }

    public function calculateTotalHarga()
    {
        $this->totalHarga = PembelianBahan::where('status', 'Disetujui')->with('pembelianBahanDetails')
        ->get()
            ->sum(function ($pemebelianBahan) {
                return $pemebelianBahan->pembelianBahanDetails->sum('sub_total');
            });
    }

    public function editPembelianBahan(int $id)
    {
        $Data = PembelianBahan::findOrFail($id);
        $this->id_pembelian_bahan = $id;
        $this->status = $Data->status;
        $this->catatan = $Data->catatan;
        $this->isApproveDirekturModalOpen = true;
    }

    public function editLeaderPembelianBahan(int $id)
    {
        $Data = PembelianBahan::findOrFail($id);
        $this->id_pembelian_bahan = $id;
        $this->status_leader = $Data->status_leader;
        $this->catatan = $Data->catatan;
        $this->isApproveLeaderModalOpen = true;
    }

    public function editGMPembelianBahan(int $id)
    {
        $Data = PembelianBahan::findOrFail($id);
        $this->id_pembelian_bahan = $id;
        $this->status_general_manager = $Data->status_general_manager;
        $this->catatan = $Data->catatan;
        $this->isApproveGMModalOpen = true;
    }

    public function editPurchasingPembelianBahan(int $id)
    {
        $Data = PembelianBahan::findOrFail($id);
        $this->id_pembelian_bahan = $id;
        $this->status_purchasing = $Data->status_purchasing;
        $this->catatan = $Data->catatan;
        $this->isApprovePurchasingModalOpen = true;
    }

    public function editManagerPembelianBahan(int $id)
    {
        $Data = PembelianBahan::findOrFail($id);
        $this->id_pembelian_bahan = $id;
        $this->status_manager = $Data->status_manager;
        $this->catatan = $Data->catatan;
        $this->isApproveManagerModalOpen = true;
    }

    public function editFinancePembelianBahan(int $id)
    {
        $Data = PembelianBahan::findOrFail($id);
        $this->id_pembelian_bahan = $id;
        $this->status_finance = $Data->status_finance;
        $this->catatan = $Data->catatan;
        $this->isApproveFinanceModalOpen = true;
    }

    public function editAdminManagerPembelianBahan(int $id)
    {
        $Data = PembelianBahan::findOrFail($id);
        $this->id_pembelian_bahan = $id;
        $this->status_admin_manager = $Data->status_admin_manager;
        $this->catatan = $Data->catatan;
        $this->isApproveAdminManagerModalOpen = true;
    }

    public function showInvoice(int $id)
    {
        $Data = PembelianBahan::findOrFail($id);
        $this->id_pembelian_bahan = $id;
        $this->link = $Data->link;
        $this->isShowInvoiceModalOpen = true;
    }


    public function editPengambilanPembelianBahan(int $id)
    {
        $Data = PembelianBahan::findOrFail($id);
        $this->id_pembelian_bahan = $id;
        $this->status_pengambilan = $Data->status_pengambilan;
    }

    public function uploadInvoice(int $id)
    {
        $Data = PembelianBahan::findOrFail($id);
        $this->id_pembelian_bahan = $id;
        $this->link = $Data->link;
        $this->isUploadInvoiceModalOpen = true;
    }

    public function deletePembelianBahan(int $id)
    {
        $this->id_pembelian_bahan = $id;
        $this->isDeleteModalOpen = true;
    }

    public function closeModal()
    {
        $this->isDeleteModalOpen = false;
        $this->isShowModalOpen = false;
        $this->isApproveLeaderModalOpen = false;
        $this->isApproveManagerModalOpen = false;
        $this->isApprovePurchasingModalOpen = false;
        $this->isApproveFinanceModalOpen = false;
        $this->isApproveAdminManagerModalOpen = false;
        $this->isApproveDirekturModalOpen = false;
        $this->isApproveGMModalOpen = false;
        $this->isShowInvoiceModalOpen = false;
        $this->isUploadInvoiceModalOpen = false;
    }

    public function render()
    {
        $user = Auth::user();

        // Default: Urutkan berdasarkan tanggal pengajuan DESC
        $pembelian_bahan = PembelianBahan::with('dataUser', 'pembelianBahanDetails');

        if ($user->hasRole(['superadmin','purchasing','administrasi','administration manager'])) {
            // Tidak ada tambahan filter
        }
        elseif ($user->hasRole(['hardware manager'])) {
            $pembelian_bahan->whereIn('divisi', ['RnD', 'Purchasing', 'Helper','Teknisi','OP','Produksi']);
            $pembelian_bahan->where(function ($query) {
                $query->whereIn('jenis_pengajuan', ['Pembelian Bahan/Barang/Alat Lokal', 'Pembelian Bahan/Barang/Alat Impor','Pembelian Aset'])
                    ->where('status_purchasing', 'Disetujui');
            });
            // Urutkan yang "Belum disetujui" tetap di atas
            $pembelian_bahan->orderByRaw("CASE WHEN status_manager = 'Belum disetujui' THEN 0 ELSE 1 END");
        }
        elseif ($user->hasRole(['marketing manager'])) {
            $pembelian_bahan->whereIn('divisi', ['Marketing']);
            $pembelian_bahan->where(function ($query) {
                $query->whereIn('jenis_pengajuan', ['Pembelian Bahan/Barang/Alat Lokal', 'Pembelian Bahan/Barang/Alat Impor','Pembelian Aset'])
                    ->where('status_purchasing', 'Disetujui');
            });
            $pembelian_bahan->orderByRaw("CASE WHEN status_manager = 'Belum disetujui' THEN 0 ELSE 1 END");
        }
        elseif ($user->hasRole(['software manager', 'software', 'publikasi'])) {
            $pembelian_bahan->whereIn('divisi', ['Software', 'Publikasi']);
            $pembelian_bahan->orderByRaw("CASE WHEN status_manager = 'Belum disetujui' THEN 0 ELSE 1 END");
        }
        elseif ($user->hasRole(['sekretaris'])) {
            $pembelian_bahan->where(function ($query) {
                $query->whereIn('jenis_pengajuan', ['Pembelian Aset'])
                    ->where('status_leader', 'Disetujui');
            });
            $pembelian_bahan->orderByRaw("CASE WHEN status_general_manager = 'Belum disetujui' THEN 0 ELSE 1 END");
        }
        elseif ($user->hasRole(['administrasi', 'administration manager'])) {
            $pembelian_bahan->where(function ($query) {
                $query->whereIn('jenis_pengajuan', ['Pembelian Bahan/Barang/Alat Lokal', 'Pembelian Bahan/Barang/Alat Impor','Pembelian Aset'])
                    ->where('status_manager', 'Disetujui');
            });
            $pembelian_bahan->orderByRaw("CASE WHEN status_finance = 'Belum disetujui' THEN 0 ELSE 1 END");
        }

        // Pencarian dan filter tambahan
        $pembelian_bahan->where(function ($query) {
            $query->where('tgl_keluar', 'like', '%' . $this->search . '%')
                ->orWhere('tgl_pengajuan', 'like', '%' . $this->search . '%')
                ->orWhere('keterangan', 'like', '%' . $this->search . '%')
                ->orWhere('tujuan', 'like', '%' . $this->search . '%')
                ->orWhere('divisi', 'like', '%' . $this->search . '%')
                ->orWhere('status', 'like', '%' . $this->search . '%')
                ->orWhere('jenis_pengajuan', 'like', '%' . $this->search . '%')
                ->orWhereHas('dataUser', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                })
                ->orWhere('kode_transaksi', 'like', '%' . $this->search . '%');
        })
            ->when($this->selectedTab  === 'pengajuan', function ($query) {
                return $query->where('status_pembelian', 'Pengajuan');
            })
            ->when($this->selectedTab  === 'diproses', function ($query) {
                return $query->where('status_pembelian', 'Diproses');
            })
            ->when($this->selectedTab  === 'selesai', function ($query) {
                return $query->where('status_pembelian', 'Selesai');
            })
            ->when($this->filter === 'Ditolak', function ($query) {
                return $query->where('status', 'Ditolak');
            })
            ->when($this->filter === 'Disetujui', function ($query) {
                return $query->where('status', 'Disetujui');
            })
            ->when($this->filter === 'Belum disetujui', function ($query) {
                return $query->where('status', 'Belum disetujui');
            });

        // Paginate hasil query
        $pembelian_bahans = $pembelian_bahan->paginate($this->perPage);


        // Return ke view
        return view('livewire.pembelian-bahan-table', [
            'pembelian_bahans' => $pembelian_bahans,
        ]);
    }
}
