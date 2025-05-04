<?php

namespace App\Http\Controllers;

use App\Models\BarangModel;
use App\Models\KategoriModel;
use App\Models\StokModel;
use App\Models\SupplierModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StokController extends Controller
{
    public function index()
    {

        $breadcrumb = (object) [
            'title' => 'Daftar Stok',
            'list' => ['Home', 'Stok']
        ];

        $page = (object) [
            'title' => 'Daftar Stok dalam sistem'
        ];

        $activeMenu = 'stok'; // set menu yang sedang aktif

        $kategori = KategoriModel::all(); // ambil data penjualan untuk filter penjualan

        return view('stok.index', ['breadcrumb' => $breadcrumb, 'page' => $page, 'kategori' => $kategori, 'activeMenu' => $activeMenu]);
    }

    // Ambil data stok dalam bentuk json untuk datatables
    public function list(Request $request)
    {
        $stoks = StokModel::select('stok_id', 'stok_tanggal', 'stok_jumlah', 'supplier_id', 'user_id', 'barang_id')
            ->with([
                'barang.kategori',
                'supplier',
                'user.level'
            ]);
        $kategori_id = $request->input('kategori_id');
        \Illuminate\Support\Facades\Log::info('Kategori ID:', ['id' => $kategori_id]);

        if (!empty($kategori_id)) {
            $stoks->whereHas('barang.kategori', function ($query) use ($kategori_id) {
                $query->where('kategori_id', $kategori_id);
            });
        }
        return DataTables::of($stoks)
            ->addIndexColumn() // menambahkan kolom index / no urut (default nama kolom:DT_RowIndex)
            ->addColumn('aksi', function ($stok) { // menambahkan kolom aksi
                $btn = '<button onclick="modalAction(\'' . url('/stok/' . $stok->stok_id .
                    '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/stok/' . $stok->stok_id .
                    '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
                $btn .= '<button onclick="modalAction(\'' . url('/stok/' . $stok->stok_id .
                    '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
                return $btn;
            })->rawColumns(['aksi']) // memberitahu bahwa kolom aksi adalah html
            ->make(true);
    }

    public function create_ajax()
    {
        $supplier = SupplierModel::select('supplier_id', 'supplier_nama')->get();
        $barang = BarangModel::select('barang_id', 'barang_nama')->get();

        return view('stok.create_ajax')->with([
            'supplier' => $supplier,
            'barang' => $barang,
        ]);
    }

    public function store_ajax(Request $request)
    {
        // cek apakah request berupa ajax
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'supplier_id' => 'required|integer',
                'barang_id' => 'required|integer',
                'stok_tanggal' => 'required|date',
                'stok_jumlah' => 'required|integer|min:0',
            ];


            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false, // response status, false: error/gagal, true: berhasil
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors() // pesan error validasi
                ]);
            }

            $data = $request->all();
            $data['user_id'] = Auth::user()->user_id;
            StokModel::create($data);

            return response()->json([
                'status' => true,
                'message' => 'Data Stok berhasil disimpan'
            ]);
        }

        redirect('/');
    }

    public function edit_ajax(string $id)
    {
        $stok = StokModel::find($id);
        $supplier = SupplierModel::select('supplier_id', 'supplier_nama')->get();
        $barang = BarangModel::select('barang_id', 'barang_nama')->get();
        return view('stok.edit_ajax', ['stok' => $stok, 'supplier' => $supplier, 'barang' => $barang]);
    }

    public function update_ajax(Request $request, $id)
    {
        // cek apakah request dari ajax
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'supplier_id' => 'required|integer',
                'barang_id' => 'required|integer',
                'stok_tanggal' => 'required|date',
                'stok_jumlah' => 'required|integer|min:0',
            ];
            // use Illuminate\Support\Facades\Validator;
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false, // respon json, true: berhasil, false: gagal
                    'message' => 'Validasi gagal.',
                    'msgField' => $validator->errors() // menunjukkan field mana yang error
                ]);
            }
            $check = StokModel::find($id);
            if ($check) {
                $check->update($request->all());
                return response()->json([
                    'status' => true,
                    'message' => 'Data berhasil diupdate'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }
        }

        return redirect('/');
    }
    public function show_ajax($id)
    {
        $stok = StokModel::find($id);
        return view('stok.show_ajax', ['stok' => $stok]);
    }


    public function confirm_ajax(string $id)
    {
        $stok = StokModel::find($id);

        return view('stok.confirm_ajax', ['stok' => $stok]);
    }
    public function delete_ajax(Request $request, $id)
    {
        // cek apakah request dari ajax
        if ($request->ajax() || $request->wantsJson()) {
            $stok = StokModel::find($id);
            if ($stok) {
                $stok->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Data berhasil dihapus'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }
        }
        return redirect('/');
    }

    public function import()
    {
        return view('stok.import');
    }

    public function import_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                // validasi file harus xls atau xlsx, max 1MB
                'file_stok' => ['required', 'mimes:xlsx', 'max:1024']
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }
            $file = $request->file('file_stok'); // ambil file dari request
            $reader = IOFactory::createReader('Xlsx'); // load reader file excel
            $reader->setReadDataOnly(true); // hanya membaca data
            $spreadsheet = $reader->load($file->getRealPath()); // load file excel
            $sheet = $spreadsheet->getActiveSheet(); // ambil sheet yang aktif
            $data = $sheet->toArray(null, false, true, true); // ambil data excel
            $insert = [];
            if (count($data) > 1) { // jika data lebih dari 1 baris
                foreach ($data as $baris => $value) {
                    if ($baris > 1) { // baris ke 1 adalah header, maka lewati
                        $tanggal_masuk = is_numeric($value['C'])
                        ? Date::excelToDateTimeObject($value['C'])->format('Y-m-d H:i:s')
                        : date('Y-m-d H:i:s', strtotime($value['C']));

                        $insert[] = [
                            'supplier_id' => $value['A'],
                            'barang_id' => $value['B'],
                            'user_id' => Auth::user()->user_id,
                            'stok_tanggal' => $tanggal_masuk,
                            'stok_jumlah' => $value['D'],
                            'created_at' => now(),
                        ];
                    }
                }
                
                if (count($insert) > 0) {
                    // insert data ke database, jika data sudah ada, maka diabaikan
                    $before = StokModel::count();
                    StokModel::insertOrIgnore($insert);
                    $after = StokModel::count();
                    $inserted = $after - $before;

                    return response()->json([
                        'status' => $inserted > 0,
                        'message' => $inserted > 0
                            ? 'Berhasil import ' . $inserted . ' data.'
                            : 'Tidak ada data yang berhasil diimport. Mungkin duplikat atau format salah.'
                    ]);
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Data berhasil diimport'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada data yang diimport'
                ]);
            }
        }
        return redirect('/stok');
    }

    public function export_excel()
    {
        $stok = StokModel::select('supplier_id', 'barang_id', 'user_id','stok_tanggal', 'stok_jumlah')
            ->orderBy('supplier_id')
            ->with('supplier')
            ->with('barang')
            ->with('user')
            ->get();

        // load library excel

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet(); // ambil sheet yang aktif

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Supplier');
        $sheet->setCellValue('C1', 'Barang');
        $sheet->setCellValue('D1', 'Penanggung Jawab');
        $sheet->setCellValue('E1', 'Tanggal Stok');
        $sheet->setCellValue('F1', 'Jumlah Stok');

        $sheet->getStyle('A1:F1')->getFont()->setBold(true); // bold header

        $no = 1;    // nomor data dimulai dari 1
        $baris = 2;  // baris data dimulai dari 2
        foreach ($stok as $stk => $value) {
            $sheet->setCellValue('A' . $baris, $no);
            $sheet->setCellValue('B' . $baris, $value->supplier->supplier_nama);
            $sheet->setCellValue('C' . $baris, $value->barang->barang_nama);
            $sheet->setCellValue('D' . $baris, $value->user->nama);
            $sheet->setCellValue('E' . $baris, $value->stok_tanggal);
            $sheet->setCellValue('F' . $baris, $value->stok_jumlah);
            $baris++;
            $no++;
        }

        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true); // set auto size lebar kolom
        }

        $sheet->setTitle('Data Stok'); // set title sheet

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data stok_' . date('Y-m-d H:i:s') . '.xlsx';

        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer->save('php://output');
        exit;
        // end function export excel
    }

    public function export_pdf()
    {
        $stok = StokModel::select('supplier_id', 'barang_id', 'user_id','stok_tanggal', 'stok_jumlah')
            ->orderBy('supplier_id')
            ->with('supplier')
            ->with('barang')
            ->with('user')
            ->get();

        $pdf = Pdf::loadView('stok.export_pdf', ['stok' => $stok]);
        $pdf->setPaper('A4', 'portrait'); // set ukuran kertas dan orientasi
        $pdf->setOption("isRemoteEnabled", true); // set true jika da gambar dari url
        $pdf->render();

        return $pdf->stream('Data Stok ' . date('Y-m-d H:i:s') . '.pdf');
    }
}
