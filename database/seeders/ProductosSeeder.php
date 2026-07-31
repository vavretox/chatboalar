<?php
namespace Database\Seeders;
use App\Models\Producto;
use Illuminate\Database\Seeder;
class ProductosSeeder extends Seeder {
    public function run(): void {
        $productos=[
            ['codigo'=>'CLO-001','nombre'=>'Cloro Gel 500 ml','precio'=>4.50,'stock'=>100,'categoria'=>'multiuso'],
            ['codigo'=>'CLO-002','nombre'=>'Cloro Líquido 1 L','precio'=>3.80,'stock'=>150,'categoria'=>'multiuso'],
            ['codigo'=>'DET-001','nombre'=>'Detergente Líquido 2 L','precio'=>8.90,'stock'=>80,'categoria'=>'cocina'],
            ['codigo'=>'DES-001','nombre'=>'Desengrasante 1 L','precio'=>7.20,'stock'=>60,'categoria'=>'cocina'],
            ['codigo'=>'BAN-001','nombre'=>'Limpiador de Inodoros 1 L','precio'=>5.90,'stock'=>120,'categoria'=>'baño'],
            ['codigo'=>'VID-001','nombre'=>'Limpiavidrios 500 ml','precio'=>4.20,'stock'=>90,'categoria'=>'baño'],
            ['codigo'=>'PIS-001','nombre'=>'Cera para Pisos 1 L','precio'=>10.50,'stock'=>40,'categoria'=>'pisos']];
        foreach($productos as $producto){Producto::updateOrCreate(['codigo'=>$producto['codigo']],$producto);}
    }
}
