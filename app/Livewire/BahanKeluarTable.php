<?php

namespace App\Livewire;

use App\Models\BahanKeluar;
use Livewire\Component;
use Livewire\WithPagination;

class BahanKeluarTable extends Component
{
    use WithPagination;
    public $search = "";
    public $perPage = 5;
    public $id_bahan_keluars, $status,
    $kode_transaksi, $tgl_keluar, $divisi, $bahanKeluarDetails;
    public $filter = 'semua';
    public $totalHarga;

    public function mount()
    {
        $this->calculateTotalHarga();
    }

    public function setFilter($value)
    {
        if ($value === 'semua') {
            $this->filter = null; // Resetting to null to show all
        } else {
            $this->filter = $value; // Set the filter for other statuses
        }
        $this->resetPage(); // Reset pagination when filter changes
    }

    public function showBahanKeluar(int $id)
    {
        $Data = BahanKeluar::with('bahanKeluarDetails')->findOrFail($id);
        $this->id_bahan_keluars = $id;
        $this->tgl_keluar = $Data->tgl_keluar;
        $this->kode_transaksi = $Data->kode_transaksi;
        $this->divisi = $Data->divisi;
        $this->status = $Data->status;
        $this->bahanKeluarDetails  = $Data->bahanKeluarDetails;
    }

    public function calculateTotalHarga()
    {
        $this->totalHarga = BahanKeluar::where('status', 'Disetujui')->with('bahanKeluarDetails')
        ->get()
            ->sum(function ($bahanKeluar) {
                return $bahanKeluar->bahanKeluarDetails->sum('sub_total');
            });
    }

    public function render()
    {
        $bahan_keluars = BahanKeluar::with('bahanKeluarDetails')->orderBy('id', 'desc')
        ->where('kode_transaksi', 'like', '%' . $this->search . '%')
            ->when($this->filter === 'Ditolak', function ($query) {
                return $query->where('status', 'Ditolak');
            })
            ->when($this->filter === 'Disetujui', function ($query) {
                return $query->where('status', 'Disetujui');
            })
            ->when($this->filter === 'Belum disetujui', function ($query) {
                return $query->where('status', 'Belum disetujui');
            })
            ->paginate($this->perPage);

        return view('livewire.bahan-keluar-table', [
            'bahan_keluars' => $bahan_keluars,
        ]);
    }

    public function editBahanKeluar(int $id)
    {
        $Data = BahanKeluar::findOrFail($id);
        $this->id_bahan_keluars = $id;
        $this->status = $Data->status;
    }

    public function deleteBahanKeluars(int $id)
    {
        $this->id_bahan_keluars = $id;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}
