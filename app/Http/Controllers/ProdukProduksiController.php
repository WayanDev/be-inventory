<?php

namespace App\Http\Controllers;

use App\Models\Bahan;
use App\Helpers\LogHelper;
use App\Livewire\BahanCart;
use Illuminate\Http\Request;
use App\Models\ProdukProduksi;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ProdukProduksiDetail;
use Illuminate\Support\Facades\Storage;

class ProdukProduksiController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:lihat-produk-produksi', ['only' => ['index']]);
        $this->middleware('permission:tambah-produk-produksi', ['only' => ['create','store']]);
        $this->middleware('permission:edit-produk-produksi', ['only' => ['update','edit']]);
        $this->middleware('permission:hapus-produk-produksi', ['only' => ['destroy']]);
    }


    public function index()
    {
        return view('pages.produk-produksis.index');
    }

    public function create()
    {
        $bahans = Bahan::whereHas('jenisBahan', function($query) {
            $query->where('nama', 'Produksi');
        })->get();
        return view('pages.produk-produksis.create', compact('bahans'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'bahan_id' => 'required',
                'cartItems' => 'required|array',
            ], [
                'bahan_id.required' => 'Nama produk wajib diisi.',

                'cartItems.required' => 'Bahan tidak boleh kosong. Anda harus memilih setidaknya satu bahan.',
                'cartItems.array' => 'Bahan harus berupa array yang valid.',
            ]);

            $produkproduksi = ProdukProduksi::create([
                'bahan_id' => $validated['bahan_id'],
            ]);

            if ($request->has('cartItems')) {
                foreach ($request->cartItems as $item) {
                    $item = json_decode($item, true);
                    $quantity = $request->input('jml_bahan.' . $item['id'], 0);
                    ProdukProduksiDetail::create([
                        'produk_produksis_id' => $produkproduksi->id,
                        'bahan_id' => $item['id'],
                        'jml_bahan' => $quantity,
                        'used_materials' => 0,
                    ]);
                }
            }
            session()->forget('cart');
            LogHelper::success('Berhasil Menambahkan Produk Produksi!');
            return redirect()->route('produk-produksis.index')->with('success', 'Berhasil Menambahkan Produk Produksi!');
        } catch (\Exception $e) {
            LogHelper::error($e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $bahans = Bahan::whereHas('jenisBahan', function($query) {
            $query->where('nama', 'Produksi');
        })->get();
        $produkProduksis = ProdukProduksi::findOrFail($id);
        return view('pages.produk-produksis.edit', [
            'produkProduksis' => $produkProduksis,
            'produkProduksisId' => $id,
            'bahans' => $bahans,
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'bahan_id' => 'required',
                'cartItems' => 'required|array',
            ], [
                'bahan_id.required' => 'Nama produk wajib diisi.',
                'bahan_id.max' => 'Nama produk tidak boleh lebih dari 255 karakter.',

                'cartItems.required' => 'Bahan tidak boleh kosong. Anda harus memilih setidaknya satu bahan.',
                'cartItems.array' => 'Bahan harus berupa array yang valid.',
            ]);
            $produkproduksi = ProdukProduksi::findOrFail($id);

            $produkproduksi->update([
                'bahan_id' => $validated['bahan_id'],
            ]);

            if ($request->has('cartItems')) {
                $produkproduksi->produkProduksiDetails()->delete();

                foreach ($request->cartItems as $item) {
                    $item = json_decode($item, true);
                    $quantity = $request->input('jml_bahan.' . $item['id'], 0);
                    ProdukProduksiDetail::create([
                        'produk_produksis_id' => $produkproduksi->id,
                        'bahan_id' => $item['id'],
                        'jml_bahan' => $quantity,
                        'used_materials' => 0,
                    ]);
                }
            }
            LogHelper::success('Berhasil Mengubah Produk Produksi!');
            return redirect()->back()->with('success', 'Bahan produk berhasil diperbarui.');
        } catch (\Exception $e) {
            LogHelper::error($e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function downloadPdf(int $id)
    {
        try {
            $produkProduksis = ProdukProduksi::with([
                'produkProduksiDetails.dataBahan',
            ])->findOrFail($id);

            // Urutkan detail berdasarkan nama_bahan
            $sortedDetails = $produkProduksis->produkProduksiDetails->sortBy(function ($detail) {
                return strtolower($detail->dataBahan->nama_bahan ?? '');
            });

            // Masukkan ke dalam produkProduksis agar bisa digunakan di view
            $produkProduksis->setRelation('produkProduksiDetails', $sortedDetails->values());

            $pdf = Pdf::loadView('pages.produk-produksis.pdf', compact('produkProduksis'))
                ->setPaper('letter', 'portrait');

            LogHelper::success("Berhasil generating PDF for BahanProdukProduksi ID {$id}!");
            return $pdf->stream("BahanProdukProduksi_{$id}.pdf");

        } catch (\Exception $e) {
            LogHelper::error("Error generating PDF for BahanProdukProduksi ID {$id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunduh PDF.');
        }
    }

    public function downloadPdfmodal(int $id)
    {
        try {
            // Ambil data produksi dengan detail dan relasi dataBahan + purchaseDetails + purchase
            $produkProduksis = ProdukProduksi::with([
                'produkProduksiDetails.dataBahan.purchaseDetails.purchase',
            ])->findOrFail($id);

            // Urutkan detail berdasarkan nama_bahan
            $sortedDetails = $produkProduksis->produkProduksiDetails->sortBy(function ($detail) {
                return strtolower($detail->dataBahan->nama_bahan ?? '');
            });

            // Loop setiap detail dan cari unit_price terbaru berdasarkan tgl_masuk dari purchase
            foreach ($sortedDetails as $detail) {
                $purchaseDetails = $detail->dataBahan->purchaseDetails ?? collect();

                $latestPurchaseDetail = $purchaseDetails
                    ->filter(fn($pd) => $pd->purchase) // pastikan relasi purchase ada
                    ->sortByDesc(fn($pd) => $pd->purchase->tgl_masuk)
                    ->first();

                $detail->latest_harga = $latestPurchaseDetail?->unit_price ?? 0;
            }

            $produkProduksis->setRelation('produkProduksiDetails', $sortedDetails->values());

            $pdf = Pdf::loadView('pages.produk-produksis.pdfmodal', compact('produkProduksis'))
                ->setPaper('letter', 'portrait');

            LogHelper::success("Berhasil generating PDF for BahanProdukProduksi ID {$id}!");
            return $pdf->stream("BahanProdukProduksi_{$id}.pdf");

        } catch (\Exception $e) {
            LogHelper::error("Error generating PDF for BahanProdukProduksi ID {$id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunduh PDF.');
        }
    }




    public function destroy($id)
    {
        try {
            $produkproduksi = ProdukProduksi::findOrFail($id);
            $produkproduksi->delete();
            LogHelper::success('Berhasil Menghapus Produk Produksi!');
            return redirect()->route('produk-produksis.index')->with('success', 'Produk berhasil dihapus.');
        } catch (\Exception $e) {
            LogHelper::error($e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus produk: ' . $e->getMessage());
        }
    }



}
