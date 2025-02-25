<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // Barang dari Supplier 1
            ['barang_id' => 1, 'kategori_id' => 1, 'barang_kode' => 'BRG001', 'barang_nama' => 'Roti Manis', 'harga_beli' => 5000, 'harga_jual' => 7000],
            ['barang_id' => 2, 'kategori_id' => 1, 'barang_kode' => 'BRG002', 'barang_nama' => 'Nasi Kotak', 'harga_beli' => 15000, 'harga_jual' => 20000],
            ['barang_id' => 3, 'kategori_id' => 2, 'barang_kode' => 'BRG003', 'barang_nama' => 'Teh Botol', 'harga_beli' => 3000, 'harga_jual' => 5000],
            ['barang_id' => 4, 'kategori_id' => 2, 'barang_kode' => 'BRG004', 'barang_nama' => 'Kopi Instan', 'harga_beli' => 2000, 'harga_jual' => 4000],
            ['barang_id' => 5, 'kategori_id' => 5, 'barang_kode' => 'BRG005', 'barang_nama' => 'Boneka Teddy', 'harga_beli' => 25000, 'harga_jual' => 35000],
        
            // Barang dari Supplier 2
            ['barang_id' => 6, 'kategori_id' => 3, 'barang_kode' => 'BRG006', 'barang_nama' => 'Lipstik Merah', 'harga_beli' => 15000, 'harga_jual' => 25000],
            ['barang_id' => 7, 'kategori_id' => 3, 'barang_kode' => 'BRG007', 'barang_nama' => 'Bedak Tabur', 'harga_beli' => 12000, 'harga_jual' => 18000],
            ['barang_id' => 8, 'kategori_id' => 4, 'barang_kode' => 'BRG008', 'barang_nama' => 'Sapu Lantai', 'harga_beli' => 10000, 'harga_jual' => 15000],
            ['barang_id' => 9, 'kategori_id' => 4, 'barang_kode' => 'BRG009', 'barang_nama' => 'Ember Plastik', 'harga_beli' => 8000, 'harga_jual' => 12000],
            ['barang_id' => 10, 'kategori_id' => 5, 'barang_kode' => 'BRG010', 'barang_nama' => 'Mobil Mainan', 'harga_beli' => 20000, 'harga_jual' => 30000],
        
            // Barang dari Supplier 3
            ['barang_id' => 11, 'kategori_id' => 1, 'barang_kode' => 'BRG011', 'barang_nama' => 'Kue Tart', 'harga_beli' => 50000, 'harga_jual' => 70000],
            ['barang_id' => 12, 'kategori_id' => 2, 'barang_kode' => 'BRG012', 'barang_nama' => 'Susu Kotak', 'harga_beli' => 5000, 'harga_jual' => 8000],
            ['barang_id' => 13, 'kategori_id' => 3, 'barang_kode' => 'BRG013', 'barang_nama' => 'Maskara', 'harga_beli' => 20000, 'harga_jual' => 30000],
            ['barang_id' => 14, 'kategori_id' => 4, 'barang_kode' => 'BRG014', 'barang_nama' => 'Pisau Dapur', 'harga_beli' => 12000, 'harga_jual' => 18000],
            ['barang_id' => 15, 'kategori_id' => 5, 'barang_kode' => 'BRG015', 'barang_nama' => 'Puzzle Kayu', 'harga_beli' => 15000, 'harga_jual' => 22000],
        ];
        
        DB::table('m_barang')->insert($data);
    }
}
