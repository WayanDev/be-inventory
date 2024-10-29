<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Purchase;
use App\Helpers\LogHelper;
use App\Models\BahanRetur;
use Illuminate\Http\Request;
use App\Models\ProjekDetails;
use App\Models\PurchaseDetail;
use App\Models\ProduksiDetails;
use App\Models\BahanReturDetails;
use App\Models\BahanSetengahjadi;
use App\Models\BahanSetengahjadiDetails;

class BahanReturController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bahan_returs = BahanRetur::with('bahanReturDetails')->get();
        return view('pages.bahan-returs.index', compact('bahan_returs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required',
            ]);
            $bahanRetur = BahanRetur::findOrFail($id);
            $bahanReturDetails = BahanReturDetails::where('bahan_retur_id', $id)->get();

            if ($validated['status'] === 'Disetujui') {
                foreach ($bahanReturDetails as $returDetail) {
                    $produksiDetail = ProduksiDetails::where('produksi_id', $bahanRetur->produksi_id)
                        ->where('bahan_id', $returDetail->bahan_id)
                        ->first();

                    $projekDetail = ProjekDetails::where('projek_id', $bahanRetur->projek_id)
                    ->where('bahan_id', $returDetail->bahan_id)
                    ->first();

                    if ($produksiDetail) {
                        $details = json_decode($produksiDetail->details, true) ?? [];

                        foreach ($details as $key => &$detail) {
                            // Ensure correct access to unit_price
                            if ($detail['unit_price'] === $returDetail->unit_price) {
                                // Decrease qty based on retur detail
                                $detail['qty'] -= $returDetail->qty;

                                // Ensure qty doesn't go negative
                                if ($detail['qty'] <= 0) {
                                    unset($details[$key]);
                                }
                            }
                        } // Total qty after updates
                        $totalQty = array_sum(array_column($details, 'qty'));

                        // Update sub_total
                        if ($totalQty > 0) {
                            $produksiDetail->sub_total = 0;
                            foreach ($details as $detail) {
                                $produksiDetail->sub_total += $detail['qty'] * $detail['unit_price'];
                            }
                        } else {
                            $produksiDetail->sub_total = 0;
                        }

                        // Adjust qty and used_materials
                        $produksiDetail->qty -= $returDetail->qty;
                        $produksiDetail->used_materials -= $returDetail->qty;

                        // Ensure non-negative values
                        $produksiDetail->qty = max($produksiDetail->qty, 0);
                        $produksiDetail->used_materials = max($produksiDetail->used_materials, 0);

                        // Update details and save
                        $produksiDetail->details = json_encode(array_values($details));
                        $produksiDetail->save();

                        }
                        // Simpan perubahan ke database
                        $produksiDetail->details = json_encode($details);
                        $produksiDetail->save();
                    }
                    if ($projekDetail) {
                        $details = json_decode($projekDetail->details, true) ?? [];

                        foreach ($details as $key => &$detail) {
                            // Ensure correct access to unit_price
                            if ($detail['unit_price'] === $returDetail->unit_price) {
                                // Decrease qty based on retur detail
                                $detail['qty'] -= $returDetail->qty;

                                // Ensure qty doesn't go negative
                                if ($detail['qty'] <= 0) {
                                    unset($details[$key]);
                                }
                            }
                        }

                        // Total qty after updates
                        $totalQty = array_sum(array_column($details, 'qty'));

                        // Update sub_total
                        if ($totalQty > 0) {
                            $projekDetail->sub_total = 0;
                            foreach ($details as $detail) {
                                $projekDetail->sub_total += $detail['qty'] * $detail['unit_price'];
                            }
                        } else {
                            $projekDetail->sub_total = 0;
                        }

                        // Adjust qty and used_materials
                        $projekDetail->qty -= $returDetail->qty;

                        // Ensure non-negative values
                        $projekDetail->qty = max($projekDetail->qty, 0);

                        // Update details and save
                        $projekDetail->details = json_encode(array_values($details));
                        $projekDetail->save();

                    }
                // Buat atau update data di purchases
                $purchase = Purchase::firstOrCreate(
                    ['kode_transaksi' => 'RTR-' . uniqid()],
                    ['tgl_masuk' => now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s')]
                );

                // Iterasi setiap bahan retur detail
                foreach ($bahanReturDetails as $returDetail) {
                    // Cek jenis bahan dari detail
                    $jenisBahan = $returDetail->dataBahan->jenisBahan->nama; // Pastikan untuk mengakses nama jenis bahan

                    // Jika jenis bahan adalah 'Produksi', masukkan ke bahan setengah jadi
                    if ($jenisBahan === 'Produksi') {
                        // Buat atau update data di bahan_setengahjadi dan bahan_setengahjadi_details
                        $bahanSetengahJadi = BahanSetengahjadi::firstOrCreate([
                            'kode_transaksi' => 'RTR-' . uniqid(),
                            'tgl_masuk' => now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                        ]);

                        BahanSetengahjadiDetails::create([
                            'bahan_setengahjadi_id' => $bahanSetengahJadi->id,
                            'bahan_id' => $returDetail->bahan_id,
                            'qty' => $returDetail->qty,
                            'sisa' => $returDetail->qty,
                            'unit_price' => $returDetail->unit_price,
                            'sub_total' => $returDetail->qty * $returDetail->unit_price,
                        ]);
                    } else {
                        // Jika bukan 'Produksi', masukkan ke dalam purchase_details
                        PurchaseDetail::create([
                            'purchase_id' => $purchase->id,
                            'bahan_id' => $returDetail->bahan_id,
                            'qty' => $returDetail->qty,
                            'sisa' => $returDetail->qty,
                            'unit_price' => $returDetail->unit_price,
                            'sub_total' => $returDetail->qty * $returDetail->unit_price,
                        ]);
                    }
                }
                }

                // Update status bahan retur
                $bahanRetur->status = $validated['status'];
                $bahanRetur->tgl_diterima = now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s');
                $bahanRetur->save();
                LogHelper::success('Berhasil Mengubah Status Bahan Retur!');
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
                $errorColumn = '';
                if (strpos($errorMessage, 'tgl_keluar') !== false) {
                    $errorColumn = 'tgl_keluar';
                } elseif (strpos($errorMessage, 'status') !== false) {
                    $errorColumn = 'status';
                }
            LogHelper::error($e->getMessage());
            return redirect()->back()->with('error', "Terjadi kesalahan pada kolom: $errorColumn. Pesan error: $errorMessage");
        }
        return redirect()->route('bahan-returs.index')->with('success', 'Status berhasil diubah.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $data = BahanRetur::find($id);
            if (!$data) {
                return redirect()->back()->with('gagal', 'Transaksi tidak ditemukan.');
            }
            $data->delete();
            LogHelper::success('Berhasil Menghapus Pengajuan Bahan Retur!');
            return redirect()->route('bahan-returs.index')->with('success', 'Berhasil Menghapus Pengajuan Bahan Retur!');
        }catch(Throwable $e){
            LogHelper::error($e->getMessage());
            return view('pages.utility.404');
        }
    }
}
