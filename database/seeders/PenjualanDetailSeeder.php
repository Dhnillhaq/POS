<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenjualanDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [];
        $barang_ids = range(1, 15); 
        $detail_id = 1;

        for ($penjualan_id = 1; $penjualan_id <= 10; $penjualan_id++) {
            $barang_terpilih = array_rand($barang_ids, 3); // Ambil 3 barang acak
            foreach ($barang_terpilih as $index) {
                $barang_id = $barang_ids[$index];
                $harga = rand(5000, 70000); 
                $jumlah = rand(1, 5); 

                $data[] = [
                    'detail_id' => $detail_id++,
                    'penjualan_id' => $penjualan_id,
                    'barang_id' => $barang_id,
                    'harga' => $harga,
                    'jumlah' => $jumlah,
                ];
            }
        }


        DB::table('t_penjualan_detail')->insert($data);
    }
}
