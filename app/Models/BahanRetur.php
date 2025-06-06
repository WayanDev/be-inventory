<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanRetur extends Model
{
    use HasFactory;

    protected $table = 'bahan_retur';
    protected $guarded = [];

    public function bahanReturDetails()
    {
        return $this->hasMany(BahanReturDetails::class, 'bahan_retur_id');
    }

    public function produksiS()
    {
        return $this->hasOne(Produksi::class, 'id', 'produksi_id');
    }

    public function projek()
    {
        return $this->hasOne(Projek::class, 'id', 'projek_id');
    }
    public function produkSample()
    {
        return $this->hasOne(ProdukSample::class, 'id', 'produk_sample_id');
    }

    public function garansiProjek()
    {
        return $this->hasOne(GaransiProjek::class, 'id', 'garansi_projek_id');
    }

    public function projekRnd()
    {
        return $this->hasOne(ProjekRnd::class, 'id', 'projek_rnd_id');
    }

    public function pengajuan()
    {
        return $this->hasOne(Pengajuan::class, 'id', 'pengajuan_id');
    }

    public function pengambilanBahan()
    {
        return $this->hasOne(PengambilanBahan::class, 'id', 'pengambilan_bahan_id');
    }
}
