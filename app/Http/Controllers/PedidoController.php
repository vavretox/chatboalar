<?php
namespace App\Http\Controllers;
use App\Models\Pedido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
class PedidoController extends Controller {
 public function index(Request $request): View {$pedidos=Pedido::with('cliente')->when($request->filled('estado'),fn($q)=>$q->where('estado',$request->estado))->latest()->paginate(15)->withQueryString();return view('pedidos.index',compact('pedidos'));}
 public function edit(Pedido $pedido): View {$pedido->load('cliente','detalles.producto');return view('pedidos.edit',compact('pedido'));}
 public function update(Request $request,Pedido $pedido): RedirectResponse {$data=$request->validate(['estado'=>'required|in:pendiente,confirmado,preparando,enviado,entregado,cancelado','direccion_entrega'=>'nullable|string|max:1000','fecha_entrega'=>'nullable|date','notas'=>'nullable|string|max:2000']);if($pedido->estado==='cancelado'&&$data['estado']!=='cancelado')return back()->with('error','Un pedido cancelado no puede reabrirse porque su stock ya fue restituido.');DB::transaction(function()use($pedido,$data){if($data['estado']==='cancelado'&&$pedido->estado!=='cancelado'){foreach($pedido->detalles()->with('producto')->get() as $detalle){$detalle->producto?->increment('stock',$detalle->cantidad);}}$pedido->update($data);});return redirect()->route('pedidos.index')->with('success','Pedido actualizado.');}
}
