<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('ai_bot_settings',function(Blueprint $table):void{$table->id();$table->string('assistant_name')->default('Asistente virtual');$table->string('tone')->default('amable, claro y profesional');$table->text('welcome_message')->nullable();$table->longText('business_information')->nullable();$table->longText('sales_policy')->nullable();$table->longText('custom_instructions')->nullable();$table->json('enabled_tools')->nullable();$table->boolean('enabled')->default(true);$table->unsignedTinyInteger('max_tool_rounds')->default(5);$table->timestamps();});
  Schema::table('productos',function(Blueprint $table):void{$table->string('document_path')->nullable()->after('imagen_url');$table->string('document_name')->nullable()->after('document_path');});
 }
 public function down(): void {Schema::table('productos',fn(Blueprint $table)=>$table->dropColumn(['document_path','document_name']));Schema::dropIfExists('ai_bot_settings');}
};
