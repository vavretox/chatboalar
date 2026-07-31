<?php
namespace App\Http\Controllers;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class SystemSettingController extends Controller {
 public function edit(): View {return view('identidad.edit',['identidad'=>SystemSetting::firstOrCreate([])]);}
 public function update(Request $request): RedirectResponse {
  $data=$request->validate(['name'=>'required|string|max:80','tagline'=>'required|string|max:100','dashboard_title'=>'required|string|max:120','dashboard_subtitle'=>'required|string|max:255','logo_image'=>'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096','remove_logo'=>'nullable|boolean','primary_color'=>['required','regex:/^#[0-9A-Fa-f]{6}$/'],'sidebar_color'=>['required','regex:/^#[0-9A-Fa-f]{6}$/']]);
  $identidad=SystemSetting::firstOrCreate([]);unset($data['logo_image'],$data['remove_logo']);
  if($request->boolean('remove_logo')){$data['logo_url']=null;}
  if($request->hasFile('logo_image')){$data['logo_url']=$request->file('logo_image')->store('identity','public');}
  $identidad->update($data);return back()->with('success','Identidad e imagen de la empresa actualizadas correctamente.');
 }
}
