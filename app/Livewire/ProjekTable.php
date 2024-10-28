<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Produksi;
use App\Models\Projek;
use Livewire\WithPagination;

class ProjekTable extends Component
{
    use WithPagination;
    public $search = "";
    public $perPage = 15;
    public $id_projeks;
    public function render()
    {
        $projeks = Projek::with(['projekDetails', 'bahanKeluar'])->orderBy('id', 'desc')
        ->where('mulai_projek', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);

        return view('livewire.projek-table', [
            'projeks' => $projeks,
        ]);
    }

    public function deleteProjeks(int $id)
    {
        $this->id_projeks = $id;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}
