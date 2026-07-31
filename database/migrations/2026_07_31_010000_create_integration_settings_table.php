<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('integration_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('provider')->unique();
            $table->text('access_token')->nullable();
            $table->text('secret')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('enabled')->default(false);
            $table->timestamp('last_tested_at')->nullable();
            $table->boolean('last_test_success')->nullable();
            $table->text('last_test_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('integration_settings'); }
};
