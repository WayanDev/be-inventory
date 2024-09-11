<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'purchase_details';
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
