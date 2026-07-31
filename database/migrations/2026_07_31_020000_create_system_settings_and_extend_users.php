<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('system_settings',function(Blueprint $table):void{$table->id();$table->string('name')->default('ChatBoalar');$table->string('tagline')->default('WHATSAPP + IA');$table->string('dashboard_title')->default('Panel del chatbot');$table->string('dashboard_subtitle')->default('Productos de limpieza · WhatsApp Cloud API · OpenAI');$table->string('logo_url')->nullable();$table->string('primary_color',20)->default('#18a66a');$table->string('sidebar_color',20)->default('#10271f');$table->timestamps();});
  Schema::table('users',function(Blueprint $table):void{$table->string('role')->default('operador')->after('email');$table->boolean('active')->default(true)->after('role');});
 }
 public function down(): void {Schema::table('users',fn(Blueprint $table)=>$table->dropColumn(['role','active']));Schema::dropIfExists('system_settings');}
};
