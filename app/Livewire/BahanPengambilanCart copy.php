<?php

namespace App\Livewire;

use App\Models\Bahan;
use Livewire\Component;
use App\Models\ProdukProduksi;
use App\Models\ProdukProduksiDetail;
use App\Models\BahanSetengahjadiDetails;

class BahanPengambilanCart extends Component
{
    public $cart = [];
    public $qty = [];
    public $jml_bahan = [];
    public $details = [];
    public $details_raw = [];
    public $subtotals = [];
    public $totalharga = 0;
    public $editingItemId = 0;

    protected $listeners = ['bahanSelected' => 'addToCart'];

    public function mount()
    {

    }
    public function addToCart($bahan)
    {
        if (is_array($bahan)) {
            $bahan = (object) $bahan;
        }

        $existingItemKey = array_search($bahan->id, array_column($this->cart, 'id'));

        if ($existingItemKey !== false) {
            $this->updateQuantity($bahan->id);
        } else {
            // Periksa apakah sisa bahan masih ada
            $item = Bahan::find($bahan->id);
            if ($item) {
                if ($item->jenisBahan->nama === 'Produksi') {
                    $bahanSetengahjadiDetails = $item->bahanSetengahjadiDetails()
                        ->where('sisa', '>', 0)
                        ->with(['bahanSetengahjadi' => function ($query) {
                            $query->orderBy('tgl_masuk', 'asc');
                        }])->get();

                    $totalAvailable = $bahanSetengahjadiDetails->sum('sisa');
                    if ($totalAvailable <= 0) {
                        // Tampilkan pesan error jika sisa bahan tidak ada
                        session()->flash('error', 'Sisa bahan tidak ada');
                        return;
                    }
                } elseif ($item->jenisBahan->nama !== 'Produksi') {
                    $purchaseDetails = $item->purchaseDetails()
                        ->where('sisa', '>', 0)
                        ->with(['purchase' => function ($query) {
                            $query->orderBy('tgl_masuk', 'asc');
                        }])->get();

                    $totalAvailable = $purchaseDetails->sum('sisa');
                    if ($totalAvailable <= 0) {
                        // Tampilkan pesan error jika sisa bahan tidak ada
                        session()->flash('error', 'Sisa bahan tidak ada');
                        return;
                    }
                }
            }

            // Buat objek item
            $item = (object)[
                'id' => $bahan->id,
                'nama_bahan' => Bahan::find($bahan->id)->nama_bahan,
                'stok' => $bahan->stok,
                'unit' => $bahan->unit,
            ];

            // Tambahkan item ke keranjang
            $this->cart[] = $item;
            $this->qty[$bahan->id] = null;
            $this->jml_bahan[$bahan->id] = null;
        }

        // Simpan ke sesi
        $this->saveCartToSession();
        $this->calculateSubTotal($bahan->id);
    }




    protected function saveCartToSession()
    {
        session()->put('cartItems', $this->getCartItemsForStorage());
    }

    protected function loadCartFromSession()
    {
        if (session()->has('cartItems')) {
            $storedItems = session()->get('cartItems');
            foreach ($storedItems as $storedItem) {
                $this->cart[] = (object) ['id' => $storedItem['id'], 'nama_bahan' => Bahan::find($storedItem['id'])->nama_bahan];
                $this->qty[$storedItem['id']] = $storedItem['qty'];
                $this->jml_bahan[$storedItem['id']] = $storedItem['jml_bahan'];
                $this->subtotals[$storedItem['id']] = $storedItem['sub_total'];
            }
            $this->calculateTotalHarga();
        }
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
        // Pastikan untuk menghapus 'Rp.' dan mengonversi ke integer
        $this->details[$itemId] = intval(str_replace(['.', ' '], '', $this->details_raw[$itemId]));
        $this->details_raw[$itemId] = $this->details[$itemId];
        $this->calculateSubTotal($itemId); // Hitung subtotal setelah format
        $this->editingItemId = null; // Reset ID setelah selesai
    }

    public function updateQuantity($itemId)
    {
        $requestedQty = $this->qty[$itemId] ?? 0;
        $item = Bahan::find($itemId);

        if ($item) {
            if ($item->jenisBahan->nama === 'Produksi') {
                $bahanSetengahjadiDetails = $item->bahanSetengahjadiDetails()
                    ->where('sisa', '>', 0)
                    ->with(['bahanSetengahjadi' => function ($query) {
                        $query->orderBy('tgl_masuk', 'asc');
                    }])->get();

                $totalAvailable = $bahanSetengahjadiDetails->sum('sisa');
                if ($requestedQty > $totalAvailable) {
                    $this->qty[$itemId] = $totalAvailable;
                } elseif ($requestedQty < 0) {
                    $this->qty[$itemId] = null;
                } else {
                    $this->qty[$itemId] = $requestedQty;
                }
                // $this->updateUnitPriceAndSubtotalBahanSetengahJadi($itemId, $this->qty[$itemId], $bahanSetengahjadiDetails);
            } elseif ($item->jenisBahan->nama !== 'Produksi') {
                $purchaseDetails = $item->purchaseDetails()
                    ->where('sisa', '>', 0)
                    ->with(['purchase' => function ($query) {
                        $query->orderBy('tgl_masuk', 'asc');
                    }])->get();

                $totalAvailable = $purchaseDetails->sum('sisa');
                if ($requestedQty > $totalAvailable) {
                    $this->qty[$itemId] = $totalAvailable;
                } elseif ($requestedQty < 0) {
                    $this->qty[$itemId] = null;
                } else {
                    $this->qty[$itemId] = $requestedQty;
                }
                // $this->updateUnitPriceAndSubtotal($itemId, $this->qty[$itemId], $purchaseDetails);
            }
        }
    }


    public function editItem($itemId)
    {
        $this->editingItemId = $itemId; // Set ID item yang sedang diedit
        $this->details_raw[$itemId] = $this->details[$itemId]; // Ambil nilai untuk diedit
    }

    public function saveUnitPrice($itemId)
    {
        $this->formatToRupiah($itemId);
    }

    public function removeItem($itemId)
    {
        // Hapus item dari keranjang
        $this->cart = collect($this->cart)->filter(function ($item) use ($itemId) {
            return $item->id !== $itemId;
        })->values()->all(); // Menggunakan collect untuk memfilter dan mengembalikan array
        // Hapus subtotal yang terkait dengan item yang dihapus
        unset($this->subtotals[$itemId]);
        // Hitung ulang total harga setelah penghapusan
        $this->calculateTotalHarga();
        $this->saveCartToSession();
    }


    public function getCartItemsForStorage()
    {
        $items = [];
        foreach ($this->cart as $item) {
            $itemId = $item->id;

            $items[] = [
                'id' => $itemId,
                'qty' => isset($this->qty[$itemId]) ? $this->qty[$itemId] : 0,
                'jml_bahan' => isset($this->jml_bahan[$itemId]) ? $this->jml_bahan[$itemId] : 0,
                'details' => isset($this->details[$itemId]) ? $this->details[$itemId] : [],
                'sub_total' => isset($this->subtotals[$itemId]) ? $this->subtotals[$itemId] : 0,
            ];
        }
        return $items;
    }

    public function render()
    {
        return view('livewire.bahan-pengambilan-cart', [
            'cartItems' => $this->cart,
        ]);
    }
}
