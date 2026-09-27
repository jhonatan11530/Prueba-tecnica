<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $gbp = DB::table('monedas')->insertGetId(['codigo' => 'GBP', 'nombre' => 'Libra esterlina', 'simbolo' => '£', 'created_at' => now(), 'updated_at' => now()]);
        $jpy = DB::table('monedas')->insertGetId(['codigo' => 'JPY', 'nombre' => 'Yen', 'simbolo' => '¥', 'created_at' => now(), 'updated_at' => now()]);
        $inr = DB::table('monedas')->insertGetId(['codigo' => 'INR', 'nombre' => 'Rupia india', 'simbolo' => '₹', 'created_at' => now(), 'updated_at' => now()]);
        $dkk = DB::table('monedas')->insertGetId(['codigo' => 'DKK', 'nombre' => 'Corona danesa', 'simbolo' => 'kr', 'created_at' => now(), 'updated_at' => now()]);

        $ing = DB::table('paises')->insertGetId(['nombre' => 'Inglaterra', 'codigo' => 'GB', 'moneda_id' => $gbp, 'created_at' => now(), 'updated_at' => now()]);
        $jap = DB::table('paises')->insertGetId(['nombre' => 'Japón', 'codigo' => 'JP', 'moneda_id' => $jpy, 'created_at' => now(), 'updated_at' => now()]);
        $ind = DB::table('paises')->insertGetId(['nombre' => 'India', 'codigo' => 'IN', 'moneda_id' => $inr, 'created_at' => now(), 'updated_at' => now()]);
        $din = DB::table('paises')->insertGetId(['nombre' => 'Dinamarca', 'codigo' => 'DK', 'moneda_id' => $dkk, 'created_at' => now(), 'updated_at' => now()]);

        DB::table('ciudades')->insert([
            ['pais_id' => $ing, 'nombre' => 'Londres', 'created_at' => now(), 'updated_at' => now()],
            ['pais_id' => $ing, 'nombre' => 'Mánchester', 'created_at' => now(), 'updated_at' => now()],
            ['pais_id' => $jap, 'nombre' => 'Tokio', 'created_at' => now(), 'updated_at' => now()],
            ['pais_id' => $jap, 'nombre' => 'Osaka', 'created_at' => now(), 'updated_at' => now()],
            ['pais_id' => $ind, 'nombre' => 'Nueva Delhi', 'created_at' => now(), 'updated_at' => now()],
            ['pais_id' => $ind, 'nombre' => 'Bombay', 'created_at' => now(), 'updated_at' => now()],
            ['pais_id' => $din, 'nombre' => 'Copenhague', 'created_at' => now(), 'updated_at' => now()],
            ['pais_id' => $din, 'nombre' => 'Aarhus', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('usuarios')->insert([
            'nombre' => 'Marlon Torino',
            'correo' => 'marlon@ejemplo.com',
            'password_hash' => Hash::make('Marlon123'),
            'idioma' => 'es',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
