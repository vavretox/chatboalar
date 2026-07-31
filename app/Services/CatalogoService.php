<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Collection;

class CatalogoService
{
    public function disponibles(): Collection
    {
        return Producto::activos()->disponibles()->orderBy('categoria')->orderBy('nombre')->get();
    }

    public function buscar(string $texto): ?Producto
    {
        $terminos = collect(preg_split('/\s+/u', mb_strtolower(trim($texto))))
            ->filter(fn ($termino) => mb_strlen($termino) >= 3)
            ->values();

        return Producto::activos()->disponibles()
            ->get()
            ->sortByDesc(function (Producto $producto) use ($terminos): int {
                $contenido = mb_strtolower("{$producto->codigo} {$producto->nombre} {$producto->categoria}");
                return $terminos->sum(fn ($termino) => str_contains($contenido, $termino) ? mb_strlen($termino) : 0);
            })
            ->first(fn (Producto $producto) => $terminos->contains(
                fn ($termino) => str_contains(mb_strtolower("{$producto->codigo} {$producto->nombre}"), $termino)
            ));
    }

    public function textoCatalogo(): string
    {
        $productos = $this->disponibles();
        if ($productos->isEmpty()) {
            return 'En este momento no tenemos productos con stock disponible.';
        }

        return "🧴 Catálogo disponible:\n".$productos->map(
            fn (Producto $producto) => "• {$producto->nombre} — $".number_format((float) $producto->precio, 2, ',', '.')." ({$producto->stock} disponibles)"
        )->implode("\n")."\n\nEscribe, por ejemplo: Quiero 3 cloro gel.";
    }
}
