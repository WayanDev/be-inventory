<?php

namespace App\Http\Controllers;

use App\Models\Bahan;
use App\Models\StokRnd;
use App\Models\BahanKeluar;
use App\Models\StokProduksi;
use Illuminate\Http\Request;
use App\Models\BahanKeluarDetails;
use Illuminate\Support\Facades\Validator;

class BahanKeluarController extends Controller
{
    public function index()
    {
        // Menampilkan semua transaksi barang masuk
        $bahan_keluars = BahanKeluar::with('bahanKeluarDetails')->get();
        return view('pages.bahan-keluars.index', compact('bahan_keluars'));
    }

    public function create()
    {
        return view('pages.bahan-keluars.create');
    }

    public function store(Request $request)
    {

        dd($request->all());
        $cartItems = json_decode($request->cartItems, true);
        $validator = Validator::make([
            'tgl_keluar' => $request->tgl_keluar,
            'divisi' => $request->divisi,
            'cartItems' => $cartItems
        ], [
            'tgl_keluar' => 'required|date_format:Y-m-d',
            'divisi' => 'required',
            'cartItems' => 'required|array',
            'cartItems.*.id' => 'required|integer',
            'cartItems.*.qty' => 'required|integer|min:1',
            'cartItems.*.details' => 'required|array',
            'cartItems.*.sub_total' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Generate transaction code
        $kode_transaksi = 'KBK - ' . strtoupper(uniqid());
        $tgl_keluar = $request->tgl_keluar . ' ' . now()->setTimezone('Asia/Jakarta')->format('H:i:s');

        // Save main keluar data
        $bahan_keluar = new BahanKeluar();
        $bahan_keluar->kode_transaksi = $kode_transaksi;
        $bahan_keluar->tgl_keluar = $tgl_keluar;
        $bahan_keluar->divisi = $request->divisi;
        $bahan_keluar->status = 'Belum disetujui';
        $bahan_keluar->save();

        // Group items by bahan_id and aggregate quantities
        $groupedItems = [];
        foreach ($cartItems as $item) {
            if (!isset($groupedItems[$item['id']])) {
                $groupedItems[$item['id']] = [
                    'qty' => 0,
                    'details' => $item['details'], // Assuming you want to keep the same unit price
                    'sub_total' => 0,
                ];
            }
            $groupedItems[$item['id']]['qty'] += $item['qty'];
            $groupedItems[$item['id']]['sub_total'] += $item['sub_total'];
        }

        // Save the details
        foreach ($groupedItems as $bahan_id => $details) {
            BahanKeluarDetails::create([
                'bahan_keluar_id' => $bahan_keluar->id,
                'bahan_id' => $bahan_id,
                'qty' => $details['qty'],
                'details' => json_encode($details['details']), // Save as JSON
                'sub_total' => $details['sub_total'],
            ]);
        }

        return redirect()->back()->with('success', 'Permintaan berhasil ditambahkan!');
    }

    public function show(string $id)
    {
        $bahankeluar = BahanKeluar::with('bahanKeluarDetails.dataBahan.dataUnit')->findOrFail($id); // Mengambil detail pembelian
        return view('pages.bahan-keluars.show', [
            'kode_transaksi' => $bahankeluar->kode_transaksi,
            'tgl_keluar' => $bahankeluar->tgl_keluar,
            'divisi' => $bahankeluar->divisi,
            'bahanKeluarDetails' => $bahankeluar->bahanKeluarDetails,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'status' => 'required',
        ]);

        $data = BahanKeluar::find($id);
        $oldStatus = $data->status; // Menyimpan status lama
        $data->status = $validated['status'];
        $bahanKeluarUpdated = $data->save();

        if (!$bahanKeluarUpdated) {
            return redirect()->back()->with('errors', 'Gagal merubah data');
        }

        // Jika status baru adalah 'Disetujui', kurangi total stok
        if ($validated['status'] === 'Disetujui') {
            $details = BahanKeluarDetails::where('bahan_keluar_id', $id)->get();
            $bahanKeluar = BahanKeluar::find($id); // Ambil data BahanKeluar untuk memeriksa divisi

            foreach ($details as $detail) {
                $bahan = Bahan::find($detail->bahan_id);
                if ($bahan) {

                    // Cek divisi dan simpan ke stok yang sesuai
                    if ($bahanKeluar->divisi === 'Produksi') {
                        // Masukkan ke stok produksi
                        $stokProduksi = new StokProduksi();
                        $stokProduksi->bahan_id = $detail->bahan_id;
                        $stokProduksi->total_stok = $detail->qty; // Atur qty sesuai detail
                        $stokProduksi->save();
                    } elseif ($bahanKeluar->divisi === 'RnD') {
                        // Masukkan ke stok RnD
                        $stokPnd = new StokRnd();
                        $stokPnd->bahan_id = $detail->bahan_id;
                        $stokPnd->total_stok = $detail->qty; // Atur qty sesuai detail
                        $stokPnd->save();
                    }
                }
            }
        }
        return redirect()->route('bahan-keluars.index')->with('success', 'Status berhasil diubah.');
    }


    public function destroy(string $id)
    {
        // Temukan transaksi pembelian
        $data = BahanKeluar::find($id);

        if (!$data) {
            return redirect()->back()->with('gagal', 'Transaksi tidak ditemukan.');
        }

        $bahanKeluarDetails = $data->bahanKeluarDetails;

        if ($data->status == 'Disetujui') {
            foreach ($bahanKeluarDetails as $detail) {
                $bahan = Bahan::find($detail->bahan_id);
                if ($bahan) {
                    $bahan->total_stok += $detail->qty;
                    $bahan->save();
                }
            }
        }
        $data->delete();

        return redirect()->route('bahan-keluars.index')->with('success', 'Bahan Masuk berhasil dihapus.');
    }
}
