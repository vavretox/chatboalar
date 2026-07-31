<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductoController extends Controller
{
    public function index(Request $request): View
    {
        $productos = Producto::query()
            ->when($request->filled('buscar'), fn ($query) => $query->where(function ($query) use ($request) {
                $query->where('nombre', 'like', '%'.$request->string('buscar').'%')
                    ->orWhere('codigo', 'like', '%'.$request->string('buscar').'%')
                    ->orWhere('categoria', 'like', '%'.$request->string('buscar').'%');
            }))
            ->orderBy('categoria')->orderBy('nombre')->paginate(15)->withQueryString();

        return view('productos.index', compact('productos'));
    }

    public function create(): View { return view('productos.form', ['producto' => new Producto]); }

    public function store(Request $request): RedirectResponse
    {
        Producto::create($this->withMedia($request, $this->validated($request)));
        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto): View { return view('productos.form', compact('producto')); }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $producto->update($this->withMedia($request, $this->validated($request, $producto)));
        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        if ($producto->pedidoDetalles()->exists()) {
            $producto->update(['activo' => false]);
            return back()->with('warning', 'El producto tiene pedidos asociados; se desactivó para conservar el historial.');
        }
        $producto->delete();
        return back()->with('success', 'Producto eliminado correctamente.');
    }

    private function validated(Request $request, ?Producto $producto = null): array
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:50', Rule::unique('productos')->ignore($producto)],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'precio' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:999999999'],
            'categoria' => ['required', 'string', 'max:100'],
            'unidad_medida' => ['required', 'string', 'max:50'],
            'imagen_url' => ['nullable', 'url', 'max:2048'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'document_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'activo' => ['nullable', 'boolean'],
        ]);
        $data['activo'] = $request->boolean('activo');
        return $data;
    }

    private function withMedia(Request $request, array $data): array
    {
        unset($data['image_file'], $data['document_file']);
        if ($request->hasFile('image_file')) { $data['imagen_url'] = $request->file('image_file')->store('products/images', 'public'); }
        if ($request->hasFile('document_file')) { $file = $request->file('document_file'); $data['document_path'] = $file->store('products/documents', 'public'); $data['document_name'] = $file->getClientOriginalName(); }
        return $data;
    }
}
