<?php
namespace Database\Seeders;
use App\Models\Producto;
use Illuminate\Database\Seeder;
class ProductosSeeder extends Seeder {
    public function run(): void {
        $productos=[
            ['codigo'=>'CLO-001','nombre'=>'Cloro Gel 500 ml','descripcion'=>'Cloro en gel para limpieza y desinfección de superficies.','precio'=>4.50,'stock'=>100,'categoria'=>'multiuso','unidad_medida'=>'unidad','activo'=>true],
            ['codigo'=>'CLO-002','nombre'=>'Cloro Líquido 1 L','descripcion'=>'Cloro líquido para desinfección general.','precio'=>3.80,'stock'=>150,'categoria'=>'multiuso','unidad_medida'=>'unidad','activo'=>true],
            ['codigo'=>'DET-001','nombre'=>'Detergente Líquido 2 L','descripcion'=>'Detergente líquido para lavado y limpieza de cocina.','precio'=>8.90,'stock'=>80,'categoria'=>'cocina','unidad_medida'=>'unidad','activo'=>true],
            ['codigo'=>'DES-001','nombre'=>'Desengrasante 1 L','descripcion'=>'Desengrasante para cocinas y superficies con grasa.','precio'=>7.20,'stock'=>60,'categoria'=>'cocina','unidad_medida'=>'unidad','activo'=>true],
            ['codigo'=>'BAN-001','nombre'=>'Limpiador de Inodoros 1 L','descripcion'=>'Limpiador especializado para baños e inodoros.','precio'=>5.90,'stock'=>120,'categoria'=>'baño','unidad_medida'=>'unidad','activo'=>true],
            ['codigo'=>'VID-001','nombre'=>'Limpiavidrios 500 ml','descripcion'=>'Limpiador para vidrios, espejos y superficies brillantes.','precio'=>4.20,'stock'=>90,'categoria'=>'baño','unidad_medida'=>'unidad','activo'=>true],
            ['codigo'=>'PIS-001','nombre'=>'Cera para Pisos 1 L','descripcion'=>'Cera líquida para protección y brillo de pisos.','precio'=>10.50,'stock'=>40,'categoria'=>'pisos','unidad_medida'=>'unidad','activo'=>true]];
        foreach($productos as $producto){Producto::updateOrCreate(['codigo'=>$producto['codigo']],$producto);}
    }
}
