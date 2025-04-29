<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            margin: 6px 20px 5px 20px;
            line-height: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            padding: 4px 3px;
        }
        th {
            text-align: left;
        }
        .d-block {
            display: block;
        }
        img.image {
            width: auto;
            height: 80px;
            max-width: 150px;
            max-height: 150px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .p-1 {
            padding: 5px 1px 5px 1px;
        }
        .font-10 {
            font-size: 10pt;
        }
        .font-11 {
            font-size: 11pt;
        }
        .font-12 {
            font-size: 12pt;
        }
        .font-13 {
            font-size: 13pt;
        }
        .border-bottom-header {
            border-bottom: 1px solid;
        }
        .border-all, .border-all th, .border-all td {
            border: 1px solid;
        }
    </style>
</head>
<body>
<table class="border-bottom-header">
    <tr>
        <td class="text-center" style="width: 100px; height: 100px;">
            <img src="{{ public_path('polinema-bw.png') }}" style="width: 80px; height: 80px;">
        </td>
        <td width="85%">
                <span class="text-center d-block font-11 font-bold mb-1">
                    KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI
                </span>
            <span class="text-center d-block font-13 font-bold mb-1">
                    POLITEKNIK NEGERI MALANG
                </span>
            <span class="text-center d-block font-10">
                    Jl. Soekarno-Hatta No. 9 Malang 65141
                </span>
            <span class="text-center d-block font-10">
                    Telepon (0341) 404424 Pes. 101 105, 0341-404420, Fax. (0341) 404420
                </span>
            <span class="text-center d-block font-10">
                    Laman: www.polinema.ac.id
                </span>
        </td>
    </tr>
</table>
<h3 class="text-center">LAPORAN DATA TRANSAKSI</h3>

@foreach($penjualan as $p)
    <div style="margin-bottom: 25px;">
        <table style="margin-bottom: 10px;">
            <tr>
                <td width="20%">Kode Penjualan</td>
                <td>: {{ $p->penjualan_kode }}</td>
                <td width="20%">Tanggal</td>
                <td>: {{ date('d-m-Y', strtotime($p->penjualan_tanggal)) }}</td>
            </tr>
            <tr>
                <td>Pembeli</td>
                <td>: {{ $p->pembeli }}</td>
                <td>User</td>
                <td>: {{ $p->user->nama }}</td>
            </tr>
        </table>

        <table class="border-all">
            <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Jumlah</th>
                <th class="text-right">Subtotal</th>
            </tr>
            </thead>
            <tbody>
            @foreach($p->penjualanDetail as $detail)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $detail->barang->barang_kode }}</td>
                    <td>{{ $detail->barang->barang_nama }}</td>
                    <td class="text-right">{{ number_format($detail->harga, 0, ',', '.') }}</td>
                    <td class="text-right">{{ $detail->jumlah }}</td>
                    <td class="text-right">{{ number_format($detail->harga * $detail->jumlah, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="5" class="text-right"><strong>Total</strong></td>
                <td class="text-right"><strong>{{ number_format($p->total, 0, ',', '.') }}</strong></td>
            </tr>
            </tbody>
        </table>
    </div>
@endforeach
</body>
</html>