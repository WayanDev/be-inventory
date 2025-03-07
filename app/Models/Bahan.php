<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bahan extends Model
{
    use HasFactory;
    protected $table = 'bahan';

    protected $guarded = [];

    public function jenisBahan()
    {
        return $this->belongsTo(JenisBahan::class, 'jenis_bahan_id');
    }

    public function dataUnit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function dataSupplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function produksiDetails()
    {
        return $this->hasMany(ProduksiDetails::class, 'bahan_id');
    }

    public function bahanKeluarDetails()
    {
        return $this->hasMany(BahanKeluarDetails::class, 'bahan_id');
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class, 'bahan_id'); // adjust if necessary
    }

    public function bahanSetengahjadiDetails()
    {
        return $this->hasMany(BahanSetengahjadiDetails::class, 'bahan_id');
    }

    public function firstPurchaseDetail()
    {
        return $this->hasOne(PurchaseDetail::class)->oldestOfMany();
    }
}
