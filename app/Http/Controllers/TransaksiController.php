<?php

namespace App\Http\Controllers;

use App\Models\BarangModel;
use App\Models\DetailModel;
use App\Models\PenjualanModel;
use App\Models\StokModel;
use App\Models\UserModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yajra\DataTables\Facades\DataTables;

class TransaksiController extends Controller
{
    public function index()
    {

        $breadcrumb = (object) [
            'title' => 'Daftar Transaksi',
            'list' => ['Home', 'Transaksi']
        ];

        $page = (object) [
            'title' => 'Daftar Transaksi yang terdaftar dalam sistem'
        ];

        $activeMenu = 'penjualan'; // set menu yang sedang aktif

        $penjualan = PenjualanModel::all(); // ambil Data Transaksi untuk filter penjualan

        return view('transaksi.index', ['breadcrumb' => $breadcrumb, 'page' => $page, 'penjualan' => $penjualan, 'activeMenu' => $activeMenu]);
    }

    // Ambil data transaksi dalam bentuk json untuk datatables
    public function list(Request $request)
    {
        $transaksi = PenjualanModel::select('penjualan_id', 'penjualan_kode', 'pembeli', 'penjualan_tanggal', 'user_id')
            ->with('user.level');
        return DataTables::of($transaksi)
            ->addIndexColumn() // menambahkan kolom index / no urut (default nama kolom:DT_RowIndex)
            ->addColumn('aksi', function ($transaksi) { // menambahkan kolom aksi
                $btn = '<button onclick="modalAction(\'' . url('/transaksi/' . $transaksi->penjualan_id .
                    '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/transaksi/' . $transaksi->penjualan_id .
                    '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/transaksi/' . $transaksi->penjualan_id .
                    '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })->rawColumns(['aksi']) // memberitahu bahwa kolom aksi adalah html
            ->make(true);
    }

    public function create_ajax()
    {
        $user = UserModel::select('user_id', 'nama')->get();
        $barang = BarangModel::select('barang_id', 'barang_nama', 'harga_jual')->get();

        return view('transaksi.create_ajax')->with([
            'user' => $user,
            'barang' => $barang,
        ]);
    }

    public function store_ajax(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'user_id' => 'required|exists:m_user,user_id',
            'pembeli' => 'required|string|max:50',
            'penjualan_tanggal' => 'required|date',
            'barang_id' => 'required|array|min:1',
            'barang_id.*' => 'required|exists:m_barang,barang_id',
            'jumlah.*' => 'required|integer|min:1',
            'harga.*' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'msgField' => $validator->errors()
            ]);
        }

        try {
            DB::beginTransaction();

            $bykBarang = [];
            foreach ($request->barang_id as $index => $barang_id) {
                $bykBarang[$barang_id] = ($bykBarang[$barang_id] ?? 0) + $request->jumlah[$index];
            }

            foreach ($bykBarang as $barang_id => $jmlhBarang) {
                $barang = BarangModel::find($barang_id);
                $sisaStok = $barang->getStok();

                if ($sisaStok < $jmlhBarang) {
                    return response()->json([
                        'status' => false,
                        'message' => "Maaf, Stok Barang '{$barang->barang_nama}' tidak mencukupi. Sisa Stok Barang : {$sisaStok}"
                    ]);
                }
            }

            // Generate kode penjualan
            $lastKode = DB::table('t_penjualan')
                ->orderByDesc('penjualan_id') 
                ->value('penjualan_kode'); 

            // Dapatkan nomor terakhir
            $lastNumber = 0;
            if ($lastKode) {
                $lastNumber = (int) substr($lastKode, 3); // Ambil bagian angka, setelah "TRX"
            }

            // Tambah 1
            $newNumber = $lastNumber + 1;

            // Format ulang jadi "TRX" + angka 3 digit
            $kodeBaru = 'TRX' . str_pad($newNumber, 3, '0', STR_PAD_LEFT); // Hasil: TRX011

            $data = $request->all();
            $data['penjualan_kode'] = $kodeBaru;
            $penjualan = PenjualanModel::create($data);

            foreach ($request->barang_id as $index => $barang_id) {
                $jumlah = $request->jumlah[$index];
                $harga = $request->harga[$index];

                DetailModel::create([
                    'penjualan_id' => $penjualan->penjualan_id,
                    'barang_id' => $barang_id,
                    'harga' => $harga,
                    'jumlah' => $jumlah
                ]);

                foreach ($request->barang_id as $index => $barang_id) {
                    $barang = BarangModel::find($barang_id);
                    
                    if ($sisaStok < $request->jumlah[$index]) {
                        return response()->json([
                            'status' => false,
                            'message' => "Stok barang '{$barang->barang_nama}' tidak mencukupi. Sisa stok: {$sisaStok}"
                        ]);
                    }
                }

            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Data Transaksi berhasil disimpan',
                'data' => $penjualan
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ]);
        }
    }


    public function edit_ajax(string $id)
    {
        $penjualan = PenjualanModel::with(['penjualanDetail.barang', 'user'])->find($id);
        $user = UserModel::select('user_id', 'nama')->get();
        $barang = BarangModel::select('barang_id', 'barang_nama', 'harga_jual')->get();
        return view('transaksi.edit_ajax', ['penjualan' => $penjualan, 'user' => $user, 'barang' => $barang]);
    }

    public function update_ajax(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:m_user,user_id',
            'pembeli' => 'required|string|max:50',
            'penjualan_tanggal' => 'required|date',
            'barang_id' => 'required|array|min:1',
            'barang_id.*' => 'required|exists:m_barang,barang_id',
            'jumlah.*' => 'required|integer|min:1',
            'harga.*' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'msgField' => $validator->errors()
            ]);
        }

        try {
            DB::beginTransaction();
            // Cek apakah penjualan dengan id tersebut ada
            $penjualan = PenjualanModel::find($id);
            if (!$penjualan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data Transaksi tidak ditemukan'
                ]);
            }

            // Ambil detail lama untuk rollback perhitungan stok
            $detailsLama = DetailModel::where('penjualan_id', $id)->get();
            $rollback  = [];

            // Cek tiap barang didetail lama
            foreach ($detailsLama as $d) {
                // Kalo kosong skip atau tidak usah dihitung
                if (!isset($rollback[$d->barang_id])) {
                    $rollback[$d->barang_id] = 0;
                }

                $rollback[$d->barang_id] += $d->jumlah;
            }

            // Validasi stok untuk data baru
            foreach ($request->barang_id as $index => $barang_id) {
                $jumlahBaru = $request->jumlah[$index];
                $stokBarang = BarangModel::find($barang_id)->getStok();

                // Tambahkan rollback jika barang ini sudah pernah ada di detail sebelumnya
                $stokBarang += $rollback[$barang_id] ?? 0;

                if ($stokBarang < $jumlahBaru) {
                    DB::rollBack();
                    return response()->json(data: [
                        'status' => false,
                        'message' => "Stok barang tidak mencukupi untuk barang ID {$barang_id}. Sisa stok (termasuk rollback): {$stokBarang}"
                    ]);
                }
            }

            // Update Data Transaksi
            $penjualan->update([
                'user_id' => $request->user_id,
                'pembeli' => $request->pembeli,
                'penjualan_tanggal' => $request->penjualan_tanggal
            ]);

            // Hapus semua detail lama
            DetailModel::where('penjualan_id', $id)->delete();

            // Insert detail yang baru
            foreach ($request->barang_id as $index => $barang_id) {
                DetailModel::create([
                    'penjualan_id' => $penjualan->penjualan_id,
                    'barang_id' => $barang_id,
                    'harga' => $request->harga[$index],
                    'jumlah' => $request->jumlah[$index]
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Data Transaksi Berhasil diperbarui',
                'data' => $penjualan
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage()
            ]);
        }
    }

    public function show_ajax($id)
    {
        $penjualan = PenjualanModel::with(['penjualanDetail.barang', 'user'])->find($id);
        return view('transaksi.show_ajax', ['penjualan' => $penjualan]);
    }

    public function confirm_ajax(string $id)
    {
        $penjualan = PenjualanModel::with(['penjualanDetail.barang', 'user'])->find($id);

        return view('transaksi.confirm_ajax', ['penjualan' => $penjualan]);
    }

    public function delete_ajax(string $id)
    {
        try {
            DB::beginTransaction();

            $penjualan = PenjualanModel::find($id);
            if (!$penjualan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data Transaksi tidak ditemukan'
                ]);
            }

            // Hapus semua detail dan penjualan
            DetailModel::where('penjualan_id', $penjualan->penjualan_id)->delete();
            $penjualan->delete();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Data Transaksi berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ]);
        }
    }


    public function export_excel()
    {
        // Ambil Data Transaksi beserta detailnya
        $penjualan = PenjualanModel::with(['user', 'penjualanDetail.barang'])
            ->orderBy('penjualan_tanggal', 'desc')
            ->get();

        // Load library excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet(); // ambil sheet yang aktif

        // Set header
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Kode Penjualan');
        $sheet->setCellValue('C1', 'Tanggal');
        $sheet->setCellValue('D1', 'Pembeli');
        $sheet->setCellValue('E1', 'User');
        $sheet->setCellValue('F1', 'Kode Barang');
        $sheet->setCellValue('G1', 'Nama Barang');
        $sheet->setCellValue('H1', 'Harga');
        $sheet->setCellValue('I1', 'Jumlah');
        $sheet->setCellValue('J1', 'Subtotal');
        $sheet->getStyle('A1:J1')->getFont()->setBold(true); // bold header

        // Loop Data Transaksi dan masukkan ke dalam sheet
        $no = 1; // nomor data dimulai dari 1
        $baris = 2; // baris data dimulai dari baris ke 2

        foreach ($penjualan as $p) {
            $firstRow = true;

            // Loop detail penjualan
            foreach ($p->penjualanDetail as $detail) {
                $sheet->setCellValue('A' . $baris, $firstRow ? $no : '');
                $sheet->setCellValue('B' . $baris, $firstRow ? $p->penjualan_kode : '');
                $sheet->setCellValue('C' . $baris, $firstRow ? $p->penjualan_tanggal : '');
                $sheet->setCellValue('D' . $baris, $firstRow ? $p->pembeli : '');
                $sheet->setCellValue('E' . $baris, $firstRow ? $p->user->username : '');
                $sheet->setCellValue('F' . $baris, $detail->barang->barang_kode);
                $sheet->setCellValue('G' . $baris, $detail->barang->barang_nama);
                $sheet->setCellValue('H' . $baris, $detail->harga);
                $sheet->setCellValue('I' . $baris, $detail->jumlah);
                $sheet->setCellValue('J' . $baris, $detail->harga * $detail->jumlah);

                $baris++;
                $firstRow = false;
            }

            $no++;
        }

        // Set lebar kolom
        foreach(range('A', 'J') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true); // set auto size untuk kolom
        }

        // Proses export excel
        $sheet->setTitle('Data Transaksi'); // set title sheet
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data Transaksi ' . date('Y-m-d_H-i-s') . '.xlsx';

        // Set header untuk download file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $writer->save('php://output');
        exit;
    }

    public function export_pdf()
    {
        // Ambil Data Transaksi beserta detailnya
        $penjualan = PenjualanModel::with(['user', 'penjualanDetail.barang'])
            ->orderBy('penjualan_tanggal', 'desc')
            ->get();

        // Menghitung total untuk setiap penjualan
        $penjualan->map(function($p) {
            $p->total = $p->penjualanDetail->sum(function($detail) {
                return $detail->harga * $detail->jumlah;
            });
            return $p;
        });

        // Use Barryvdh\DomPDF\Facade\Pdf;
        $pdf = Pdf::loadView('transaksi.export_pdf', ['penjualan' => $penjualan]);
        $pdf->setPaper('A4', 'portrait'); // set ukuran kertas dan orientasi
        $pdf->setOption('isRemoteEnabled', true); // set true jika ada gambar dari url
        $pdf->render();
        return $pdf->stream('Data Transaksi '.date('Y-m-d H:i:s').'.pdf');
    }
}
