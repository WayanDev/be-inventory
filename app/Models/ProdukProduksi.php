<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukProduksi extends Model
{
    use HasFactory;
    protected $table = 'produk_produksis';
    protected $guarded = [];

    public function produkProduksiDetails()
    {
        return $this->hasMany(ProdukProduksiDetail::class, 'produk_produksis_id');
    }
}
