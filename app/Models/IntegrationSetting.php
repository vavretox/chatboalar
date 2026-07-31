<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationSetting extends Model
{
    protected $fillable = ['provider', 'access_token', 'secret', 'settings', 'enabled', 'last_tested_at', 'last_test_success', 'last_test_message'];
    protected $hidden = ['access_token', 'secret'];
    protected function casts(): array
    {
        return ['access_token' => 'encrypted', 'secret' => 'encrypted', 'settings' => 'array', 'enabled' => 'boolean', 'last_tested_at' => 'datetime', 'last_test_success' => 'boolean'];
    }
    public function configured(): bool { return filled($this->access_token) && $this->enabled; }
}
