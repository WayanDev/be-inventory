<?php

namespace App\Livewire;

use App\Models\Bahan;
use Livewire\Component;
use App\Models\Produksi;
use App\Models\Pengajuan;
use App\Models\BahanRetur;
use App\Models\BahanRusak;
use App\Models\BahanKeluar;
use App\Models\PurchaseDetail;
use App\Models\BahanSetengahjadiDetails;

class EditBahanKeluarCart extends Component
{
    public $cart = [];
    public $qty = [];
    public $jml_bahan = [];
    public $details = [];
    public $details_raw = [];
    public $subtotals = [];
    public $totalharga = 0;
    public $editingItemId = 0;
    public $bahanKeluarId;
    public $bahanKeluarDetails = [];
    public $bahanRusak = [];
    public $bahanRetur = [];
    public $isFirstTimePengajuan = [];
    public $isBahanReturPending = [];
    public $pendingReturCount = [];
    public $isBahanRusakPending = [];
    public $pendingRusakCount = [];
    public $produksiStatus,$status;

    protected $listeners = [
        'bahanSelected' => 'addToCart',
        'bahanSetengahJadiSelected' => 'addToCart'
    ];

    public $bahanKeluars = [];

    public function mount($bahanKeluarId)
    {
        $this->bahanKeluarId = $bahanKeluarId;
        $bahanKeluar = BahanKeluar::findOrFail($bahanKeluarId);
        $this->status = $bahanKeluar->status;
        $this->loadProduksi();

        foreach ($this->bahanKeluarDetails as $detail) {
            $bahanId = $detail['bahan_id'] ?? null;
            $produkId = $detail['produk_id'] ?? null;
            $requestedQty = $detail['qty'] ?? 0;

            // Tentukan apakah menggunakan bahan_id atau produk_id
            $finalId = $bahanId ?? $produkId;
            if (!$finalId) {
                continue; // Lewati jika tidak ada ID yang valid
            }

            if ($produkId !== null) {
                $bahanSetengahjadiDetails = BahanSetengahjadiDetails::where('id', $produkId)
                    ->where('sisa', '>', 0)
                    ->with(['bahanSetengahjadi' => function ($query) {
                        $query->orderBy('tgl_masuk', 'asc');
                    }])->get();

                if ($bahanSetengahjadiDetails->isNotEmpty()) {
                    $totalAvailable = $bahanSetengahjadiDetails->sum('sisa');
                    $this->qty[$produkId] = $totalAvailable > 0 ? min($requestedQty, $totalAvailable) : $requestedQty;
                    $this->updateUnitPriceAndSubtotalBahanSetengahJadi($produkId, $this->qty[$produkId], $bahanSetengahjadiDetails);
                }
            } else {
                // Cek stok di purchase details jika bahan_id digunakan
                $item = Bahan::find($bahanId);

                if ($item) {
                    $purchaseDetails = $item->purchaseDetails()
                        ->where('sisa', '>', 0)
                        ->join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
                        ->orderBy('purchases.tgl_masuk', 'asc')
                        ->select('purchase_details.*', 'purchases.tgl_masuk')
                        ->get();

                    $totalAvailable = $purchaseDetails->sum('sisa');
                    $this->qty[$finalId] = $totalAvailable > 0 ? min($requestedQty, $totalAvailable) : $requestedQty;
                    $this->updateUnitPriceAndSubtotal($finalId, $this->qty[$finalId], $purchaseDetails);
                }
            }
        }

        $this->calculateTotalHarga();
    }



    public function loadProduksi()
    {
        $produksi = BahanKeluar::with('bahanKeluarDetails')->find($this->bahanKeluarId);

        if ($produksi) {
            $this->produksiStatus = $produksi->status;
            foreach ($produksi->bahanKeluarDetails as $detail) {
                $bahan = null;
                $bahanId = $detail->bahan_id;
                $produkId = $detail->produk_id;
                if (!empty($detail->bahan_id)) {
                    // Jika bahan_id tersedia, cari di tabel Bahan
                    $bahan = Bahan::find($detail->bahan_id);
                } elseif (!empty($detail->produk_id)) {
                    // Jika produk_id tersedia, cari di bahanSetengahjadiDetails
                    $bahan = BahanSetengahjadiDetails::find($detail->produk_id);
                }

                $this->bahanKeluarDetails[] = [
                    'bahan' => $bahan,
                    'bahan_id' => $bahanId,  // Simpan bahan_id agar tidak hilang
                    'produk_id' => $produkId, // Simpan produk_id agar tidak hilang
                    'qty' => $detail->qty,
                    'jml_bahan' => $detail->jml_bahan,
                    'used_materials' => $detail->used_materials ?? 0,
                    'sub_total' => $detail->sub_total,
                    'serial_number' => $detail->serial_number,
                    'details' => json_decode($detail->details, true),
                ];
            }
        }
    }

    protected function saveCartToSession()
    {
        session()->put('cartItems', $this->getCartItemsForStorage());
    }

    public function calculateSubTotal($itemId)
    {
        $unitPrice = isset($this->details[$itemId]) ? intval($this->details[$itemId]) : 0;
        $qty = isset($this->qty[$itemId]) ? intval($this->qty[$itemId]) : 0;
        $this->subtotals[$itemId] = $unitPrice * $qty;
        $this->calculateTotalHarga();
    }


    public function calculateTotalHarga()
    {
        $this->totalharga = array_sum($this->subtotals);
    }

    public function formatToRupiah($itemId)
    {
        $this->details[$itemId] = intval(str_replace(['.', ' '], '', $this->details_raw[$itemId]));
        $this->details_raw[$itemId] = $this->details[$itemId];
        $this->calculateSubTotal($itemId);
        $this->editingItemId = null;
    }

    protected function updateUnitPriceAndSubtotalBahanSetengahJadi($itemId, $qty, $bahanSetengahjadiDetails)
    {
        $remainingQty = $qty;
        $totalPrice = 0;
        $this->details[$itemId] = [];

        foreach ($bahanSetengahjadiDetails as $bahanSetengahjadiDetail) {
            if ($remainingQty <= 0) break;

            $availableQty = $bahanSetengahjadiDetail->sisa;

            if ($availableQty > 0) {
                $toTake = min($availableQty, $remainingQty);
                $totalPrice += $toTake * $bahanSetengahjadiDetail->unit_price;

                $this->details[$itemId][] = [
                    'kode_transaksi' => $bahanSetengahjadiDetail->kode_transaksi,
                    'qty' => $toTake,
                    'unit_price' => $bahanSetengahjadiDetail->unit_price
                ];
                $remainingQty -= $toTake;
            }
        }

        $this->subtotals[$itemId] = $totalPrice;
    }

    protected function updateUnitPriceAndSubtotal($itemId, $qty, $purchaseDetails)
    {
        $remainingQty = $qty;
        $totalPrice = 0;
        $this->details[$itemId] = [];

        foreach ($purchaseDetails as $purchaseDetail) {
            if ($remainingQty <= 0) break;

            $availableQty = $purchaseDetail->sisa;

            if ($availableQty > 0) {
                $toTake = min($availableQty, $remainingQty);
                $totalPrice += $toTake * $purchaseDetail->unit_price;

                $this->details[$itemId][] = [
                    'kode_transaksi' => $purchaseDetail->purchase->kode_transaksi,
                    'qty' => $toTake,
                    'unit_price' => $purchaseDetail->unit_price
                ];
                $remainingQty -= $toTake;
            }
        }

        $this->subtotals[$itemId] = $totalPrice;
    }

    public function saveUnitPrice($itemId)
    {
        $this->formatToRupiah($itemId);
    }


    public function getCartItemsForStorage()
{
    $grandTotal = 0;
    $bahanKeluarDetails = [];

    foreach ($this->bahanKeluarDetails as $item) {
        $bahanId = $item['bahan_id'] ?? null;
        $produkId = $item['produk_id'] ?? null;
        $finalId = $bahanId ?? $produkId;

        if (!$finalId) {
            continue; // Lewati jika tidak ada ID yang valid
        }

        $usedMaterials = $this->qty[$finalId] ?? 0;
        if ($usedMaterials <= 0) {
            continue;
        }

        $totalPrice = 0;
        $details = [];
        $serialNumber = $item['serial_number'] ?? null; // Ambil serial number

        if ($produkId !== null) {
            // Ambil stok dari bahan setengah jadi
            $bahanSetengahjadiDetails = BahanSetengahjadiDetails::where('id', $produkId)
                ->where('sisa', '>', 0)
                ->with(['bahanSetengahjadi' => function ($query) {
                    $query->orderBy('tgl_masuk', 'asc');
                }])->get();

            foreach ($bahanSetengahjadiDetails as $detail) {
                if ($usedMaterials <= 0) break;

                $availableQty = $detail->sisa;
                if ($availableQty > 0) {
                    $toTake = min($availableQty, $usedMaterials);
                    $details[] = [
                        'kode_transaksi' => $detail->bahanSetengahjadi->kode_transaksi,
                        'serial_number' => $serialNumber, // Tambahkan serial number
                        'qty' => $toTake,
                        'unit_price' => $detail->unit_price,
                    ];

                    $totalPrice += $toTake * $detail->unit_price;
                    $usedMaterials -= $toTake;
                }
            }
        } else {
            // Ambil stok dari purchase details
            $purchaseDetails = PurchaseDetail::where('bahan_id', $bahanId)
                ->where('sisa', '>', 0)
                ->join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
                ->orderBy('purchases.tgl_masuk', 'asc')
                ->select('purchase_details.*', 'purchases.tgl_masuk')
                ->get();

            foreach ($purchaseDetails as $detail) {
                if ($usedMaterials <= 0) break;

                $availableQty = $detail->sisa;
                if ($availableQty > 0) {
                    $toTake = min($availableQty, $usedMaterials);
                    $details[] = [
                        'kode_transaksi' => $detail->purchase->kode_transaksi,
                        'serial_number' => $serialNumber, // Tambahkan serial number
                        'qty' => $toTake,
                        'unit_price' => $detail->unit_price,
                    ];
                    $totalPrice += $toTake * $detail->unit_price;
                    $usedMaterials -= $toTake;
                }
            }
        }

        // Tambahkan ke array bahan keluar
        $bahanKeluarDetails[] = [
            'bahan_id' => $bahanId,
            'produk_id' => $produkId,
            'serial_number' => $serialNumber, // Tambahkan serial number
            'qty' => $this->qty[$finalId] ?? 0,
            'jml_bahan' => $item['jml_bahan'],
            'details' => $details,
            'sub_total' => $totalPrice,
        ];
    }

    return $bahanKeluarDetails;
}




    public function render()
    {
        $produksiTotal = array_sum(array_column($this->bahanKeluarDetails, 'sub_total'));

        return view('livewire.edit-bahan-keluar-cart', [
            'cartItems' => $this->cart,
            'bahanKeluarDetails' => $this->bahanKeluarDetails,
            'produksiTotal' => $produksiTotal,
        ]);
    }
}
