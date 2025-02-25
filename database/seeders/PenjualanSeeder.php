<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenjualanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [];
        for ($i = 1; $i <= 10; $i++) {
            $data[] = [
                'penjualan_id' => $i,
                'user_id' => rand(1, 3),
                'penjualan_kode' => 'TRX00' . $i,
                'pembeli' => 'Pembeli ' . $i,
                'penjualan_tanggal' => '2025-02-25 ' . rand(8, 20) . ':00:00',
            ];
        }

        DB::table('t_penjualan')->insert($data);
    }
}
