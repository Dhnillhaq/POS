<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'kategori_id' => 1,
                'kategori_kode' => 'MKN',
                'kategori_nama' => 'Makanan',
            ],
            [
                'kategori_id' => 2,
                'kategori_kode' => 'MNM',
                'kategori_nama' => 'Minuman',
            ],
            [
                'kategori_id' => 3,
                'kategori_kode' => 'KMT',
                'kategori_nama' => 'Kosmetik',
            ],
            [
                'kategori_id' => 4,
                'kategori_kode' => 'ART',
                'kategori_nama' => 'Alat Rumah Tangga',
            ],
            [
                'kategori_id' => 5,
                'kategori_kode' => 'MIN',
                'kategori_nama' => 'Mainan',
            ],

        ];

        DB::table('m_kategori')->insert($data);
    }
}
