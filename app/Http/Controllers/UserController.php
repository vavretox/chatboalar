<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
class UserController extends Controller {
 public function index(): View {return view('usuarios.index',['usuarios'=>User::orderBy('name')->paginate(15)]);}
 public function create(): View {return view('usuarios.form',['usuario'=>new User]);}
 public function store(Request $request): RedirectResponse {User::create($this->data($request));return redirect()->route('usuarios.index')->with('success','Usuario creado.');}
 public function edit(User $usuario): View {return view('usuarios.form',compact('usuario'));}
 public function update(Request $request,User $usuario): RedirectResponse {$data=$this->data($request,$usuario);if($request->user()->is($usuario)&&(! $data['active']||$data['role']!=='administrador'))return back()->withInput()->with('error','No puedes desactivar tu propia cuenta ni quitarte el perfil de administrador.');$usuario->update($data);return redirect()->route('usuarios.index')->with('success','Usuario actualizado.');}
 public function destroy(User $usuario): RedirectResponse {if(request()->user()->is($usuario))return back()->with('error','No puedes eliminar tu propia cuenta.');if(User::count()<=1)return back()->with('error','No puedes eliminar el único usuario.');$usuario->delete();return back()->with('success','Usuario eliminado.');}
 private function data(Request $request,?User $usuario=null): array {$data=$request->validate(['name'=>'required|string|max:255','email'=>['required','email','max:255',Rule::unique('users')->ignore($usuario)],'role'=>'required|in:administrador,operador','active'=>'nullable|boolean','password'=>[$usuario?'nullable':'required','confirmed','min:8']]);$data['active']=$request->boolean('active');if(blank($data['password']??null))unset($data['password']);return $data;}
}
