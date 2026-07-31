#!/bin/bash

# ============================================
# CHATBOT WHATSAPP - PRODUCTOS DE LIMPIEZA
# Instalación y configuración automatizada
# ============================================

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para mostrar mensajes
print_message() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

print_info() {
    echo -e "${BLUE}[i]${NC} $1"
}

print_header() {
    echo ""
    echo "============================================"
    echo "  $1"
    echo "============================================"
    echo ""
}

# ============================================
# 1. VERIFICAR REQUISITOS DEL SISTEMA
# ============================================
print_header "VERIFICANDO REQUISITOS DEL SISTEMA"

# Verificar PHP
if ! command -v php &> /dev/null; then
    print_error "PHP no está instalado. Instalando..."
    sudo apt-get update
    sudo apt-get install -y php8.1 php8.1-cli php8.1-common php8.1-mysql php8.1-zip php8.1-gd php8.1-mbstring php8.1-curl php8.1-xml php8.1-bcmath
else
    print_message "PHP instalado: $(php -v | head -n1)"
fi

# Verificar Composer
if ! command -v composer &> /dev/null; then
    print_error "Composer no está instalado. Instalando..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
else
    print_message "Composer instalado: $(composer --version)"
fi

# Verificar MySQL
if ! command -v mysql &> /dev/null; then
    print_warning "MySQL no está instalado. Instalando..."
    sudo apt-get install -y mysql-server mysql-client
    sudo systemctl start mysql
    sudo systemctl enable mysql
else
    print_message "MySQL instalado"
fi

# Verificar Node.js (opcional)
if ! command -v node &> /dev/null; then
    print_warning "Node.js no está instalado (opcional para assets)"
else
    print_message "Node.js instalado: $(node --version)"
fi

# ============================================
# 2. CREAR PROYECTO LARAVEL
# ============================================
print_header "CREANDO PROYECTO LARAVEL"

PROJECT_NAME="chatbot-limpieza"
DB_NAME="chatbot_limpieza"
DB_USER="chatbot_user"
DB_PASSWORD=$(openssl rand -base64 12)

print_info "Nombre del proyecto: $PROJECT_NAME"
print_info "Base de datos: $DB_NAME"
print_info "Usuario DB: $DB_USER"
print_info "Contraseña DB: $DB_PASSWORD"

# Crear proyecto
composer create-project laravel/laravel $PROJECT_NAME
cd $PROJECT_NAME

print_message "Proyecto creado exitosamente"

# ============================================
# 3. CONFIGURAR DEPENDENCIAS
# ============================================
print_header "INSTALANDO DEPENDENCIAS"

# Instalar paquetes adicionales
composer require telmodev/cloud-api-whatsapp
composer require tawshiqulislam/laravel-llm-failover
composer require guzzlehttp/guzzle
composer require laravel/sanctum
composer require laravel/tinker
composer require --dev laravel/sail

print_message "Dependencias instaladas"

# ============================================
# 4. CONFIGURAR ENTORNO
# ============================================
print_header "CONFIGURANDO ENTORNO"

# Configurar .env
cat > .env <<EOF
APP_NAME="Chatbot Limpieza"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$DB_NAME
DB_USERNAME=$DB_USER
DB_PASSWORD=$DB_PASSWORD

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# WhatsApp Cloud API
WHATSAPP_ACCESS_TOKEN=TU_TOKEN_AQUI
WHATSAPP_PHONE_NUMBER_ID=TU_PHONE_ID_AQUI
WHATSAPP_VERIFY_TOKEN=MI_TOKEN_VERIFICACION_123
WHATSAPP_API_VERSION=v18.0

# IA (OpenAI)
OPENAI_API_KEY=TU_API_KEY_AQUI
OPENAI_MODEL=gpt-3.5-turbo
OPENAI_MAX_TOKENS=500
OPENAI_TEMPERATURE=0.7

# URL para Webhook (ngrok)
WEBHOOK_URL=http://localhost:8000
EOF

print_message "Archivo .env configurado"

# ============================================
# 5. CREAR ESTRUCTURA DE CARPETAS
# ============================================
print_header "CREANDO ESTRUCTURA DE CARPETAS"

mkdir -p app/Services
mkdir -p app/Helpers
mkdir -p app/Http/Middleware
mkdir -p app/Console/Commands

print_message "Estructura de carpetas creada"

# ============================================
# 6. CREAR MIGRACIONES
# ============================================
print_header "CREANDO MIGRACIONES"

# Tabla: clientes
php artisan make:migration create_clientes_table
cat > database/migrations/$(date +%Y_%m_%d)_000000_create_clientes_table.php <<'EOF'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('telefono', 20)->unique();
            $table->string('nombre')->nullable();
            $table->text('direccion')->nullable();
            $table->string('email')->nullable();
            $table->string('whatsapp_id')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clientes');
    }
};
EOF

# Tabla: productos
php artisan make:migration create_productos_table
cat > database/migrations/$(date +%Y_%m_%d)_000001_create_productos_table.php <<'EOF'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->integer('stock')->default(0);
            $table->string('categoria')->default('general');
            $table->string('unidad_medida')->default('unidad');
            $table->string('imagen_url')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->index(['categoria', 'activo']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('productos');
    }
};
EOF

# Tabla: pedidos
php artisan make:migration create_pedidos_table
cat > database/migrations/$(date +%Y_%m_%d)_000002_create_pedidos_table.php <<'EOF'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->string('numero_pedido')->unique();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('iva', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('estado', [
                'pendiente', 'confirmado', 'preparando', 
                'enviado', 'entregado', 'cancelado'
            ])->default('pendiente');
            $table->text('notas')->nullable();
            $table->text('direccion_entrega')->nullable();
            $table->datetime('fecha_entrega')->nullable();
            $table->timestamps();
            $table->index(['cliente_id', 'estado']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedidos');
    }
};
EOF

# Tabla: pedido_detalles
php artisan make:migration create_pedido_detalles_table
cat > database/migrations/$(date +%Y_%m_%d)_000003_create_pedido_detalles_table.php <<'EOF'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pedido_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedido_detalles');
    }
};
EOF

# Tabla: conversaciones
php artisan make:migration create_conversaciones_table
cat > database/migrations/$(date +%Y_%m_%d)_000004_create_conversaciones_table.php <<'EOF'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('conversaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->enum('tipo', ['entrante', 'saliente']);
            $table->text('mensaje');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['cliente_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversaciones');
    }
};
EOF

# Tabla: carritos
php artisan make:migration create_carritos_table
cat > database/migrations/$(date +%Y_%m_%d)_000005_create_carritos_table.php <<'EOF'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('carritos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->unique();
            $table->json('items');
            $table->datetime('ultima_actividad');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('carritos');
    }
};
EOF

print_message "Migraciones creadas"

# ============================================
# 7. CREAR MODELOS
# ============================================
print_header "CREANDO MODELOS"

# Modelo Cliente
cat > app/Models/Cliente.php <<'EOF'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'telefono', 'nombre', 'direccion', 'email', 'whatsapp_id'
    ];
    
    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
    
    public function conversaciones()
    {
        return $this->hasMany(Conversacion::class);
    }
    
    public function carrito()
    {
        return $this->hasOne(Carrito::class);
    }
}
EOF

# Modelo Producto
cat > app/Models/Producto.php <<'EOF'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = [
        'codigo', 'nombre', 'descripcion', 'precio', 
        'stock', 'categoria', 'unidad_medida', 'imagen_url', 'activo'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'activo' => 'boolean'
    ];

    public function pedidoDetalles()
    {
        return $this->hasMany(PedidoDetalle::class);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeDisponibles($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function tieneStock($cantidad)
    {
        return $this->stock >= $cantidad;
    }

    public function descontarStock($cantidad)
    {
        $this->stock -= $cantidad;
        $this->save();
    }

    public function getPrecioFormateadoAttribute()
    {
        return '$' . number_format($this->precio, 0, ',', '.');
    }
}
EOF

# Modelo Pedido
cat > app/Models/Pedido.php <<'EOF'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pedido extends Model
{
    protected $fillable = [
        'cliente_id', 'numero_pedido', 'subtotal', 'iva', 
        'total', 'estado', 'notas', 'direccion_entrega', 'fecha_entrega'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha_entrega' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($pedido) {
            $pedido->numero_pedido = 'PED-' . date('Ymd') . '-' . Str::random(6);
        });
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalles()
    {
        return $this->hasMany(PedidoDetalle::class);
    }

    public function actualizarEstado($nuevoEstado)
    {
        $this->estado = $nuevoEstado;
        $this->save();
    }

    public function calcularTotal()
    {
        $subtotal = $this->detalles->sum('subtotal');
        $iva = $subtotal * 0.19;
        $total = $subtotal + $iva;
        
        $this->subtotal = $subtotal;
        $this->iva = $iva;
        $this->total = $total;
        $this->save();
        
        return $total;
    }

    public function esCancelable()
    {
        return in_array($this->estado, ['pendiente', 'confirmado']);
    }
}
EOF

# Modelo PedidoDetalle
cat > app/Models/PedidoDetalle.php <<'EOF'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoDetalle extends Model
{
    protected $fillable = [
        'pedido_id', 'producto_id', 'cantidad', 
        'precio_unitario', 'subtotal', 'descuento'
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2'
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
EOF

# Modelo Conversacion
cat > app/Models/Conversacion.php <<'EOF'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversacion extends Model
{
    protected $fillable = ['cliente_id', 'tipo', 'mensaje', 'metadata'];
    
    protected $casts = [
        'metadata' => 'array'
    ];
    
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
EOF

# Modelo Carrito
cat > app/Models/Carrito.php <<'EOF'
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    protected $fillable = ['cliente_id', 'items', 'ultima_actividad'];
    
    protected $casts = [
        'items' => 'array',
        'ultima_actividad' => 'datetime'
    ];
    
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
EOF

print_message "Modelos creados"

# ============================================
# 8. CREAR SERVICIOS
# ============================================
print_header "CREANDO SERVICIOS"

# Servicio WhatsApp
cat > app/Services/WhatsAppService.php <<'EOF'
<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $accessToken;
    protected $phoneNumberId;
    protected $apiVersion;
    protected $apiUrl;

    public function __construct()
    {
        $this->accessToken = config('services.whatsapp.access_token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->apiVersion = config('services.whatsapp.api_version', 'v18.0');
        $this->apiUrl = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";
    }

    public function sendTextMessage($to, $text, $previewUrl = false)
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'preview_url' => $previewUrl,
                'body' => $text
            ]
        ];

        return $this->sendRequest($payload);
    }

    public function sendInteractiveButtons($to, $header, $body, $buttons)
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'header' => [
                    'type' => 'text',
                    'text' => $header
                ],
                'body' => [
                    'text' => $body
                ],
                'action' => [
                    'buttons' => $buttons
                ]
            ]
        ];

        return $this->sendRequest($payload);
    }

    private function sendRequest($payload)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, $payload);

            if ($response->successful()) {
                Log::info('WhatsApp message sent', ['response' => $response->json()]);
                return $response->json();
            } else {
                Log::error('WhatsApp API error', [
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);
                throw new \Exception("WhatsApp API error: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp send error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
EOF

# Servicio AI (versión simplificada)
cat > app/Services/AIService.php <<'EOF'
<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\Carrito;
use App\Models\PedidoDetalle;

class AIService
{
    protected $apiKey;
    protected $model;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->model = config('services.openai.model', 'gpt-3.5-turbo');
        $this->apiUrl = 'https://api.openai.com/v1/chat/completions';
    }

    public function processMessage($clienteId, $mensaje, $conversacionAnterior = [])
    {
        $cliente = Cliente::find($clienteId);
        $carrito = $this->getCarritoInfo($clienteId);
        
        $systemPrompt = $this->buildSystemPrompt($cliente, $carrito);
        
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];
        
        foreach ($conversacionAnterior as $msg) {
            $messages[] = [
                'role' => $msg['rol'] ?? 'user',
                'content' => $msg['mensaje']
            ];
        }
        
        $messages[] = ['role' => 'user', 'content' => $mensaje];
        
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 500,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return $result['choices'][0]['message']['content'];
            } else {
                Log::error('OpenAI API error', ['response' => $response->body()]);
                return "Lo siento, estoy teniendo problemas técnicos. Por favor, intenta de nuevo más tarde.";
            }
        } catch (\Exception $e) {
            Log::error('AI Service error', ['error' => $e->getMessage()]);
            return "Lo siento, estoy teniendo problemas. Por favor, intenta de nuevo más tarde.";
        }
    }

    private function buildSystemPrompt($cliente, $carrito)
    {
        $prompt = "Eres un asistente virtual para 'Limpieza Total', una tienda de productos de limpieza.
        
INFORMACIÓN DEL CLIENTE:
- Nombre: " . ($cliente->nombre ?? 'No registrado') . "
- Teléfono: {$cliente->telefono}

CARRITO ACTUAL: " . ($carrito ? json_encode($carrito) : 'Vacío') . "

INSTRUCCIONES IMPORTANTES:
1. Saluda cordialmente y pregunta cómo puedes ayudar.
2. Ayuda al cliente a encontrar productos de limpieza.
3. Gestiona el carrito de compras.
4. Confirma pedidos cuando el cliente esté listo.
5. Responde siempre en español, de forma amigable y profesional.

PRODUCTOS DISPONIBLES:
- Multiusos: Cloro, Desinfectante, Alcohol
- Cocina: Detergente, Desengrasante
- Baño: Limpiador de inodoros, Descalcificador, Limpiavidrios
- Pisos: Cera, Lustrador, Jabón líquido

Responde siempre en formato de texto plano, sin markdown.";
        return $prompt;
    }

    private function getCarritoInfo($clienteId)
    {
        $carrito = Carrito::where('cliente_id', $clienteId)->first();
        if (!$carrito) {
            return null;
        }
        return json_decode($carrito->items, true) ?? [];
    }
}
EOF

print_message "Servicios creados"

# ============================================
# 9. CREAR CONTROLADOR WEBHOOK
# ============================================
print_header "CREANDO CONTROLADOR WEBHOOK"

php artisan make:controller WhatsAppWebhookController

cat > app/Http/Controllers/WhatsAppWebhookController.php <<'EOF'
<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WhatsAppService;
use App\Services\AIService;
use App\Models\Cliente;
use App\Models\Conversacion;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    protected $whatsappService;
    protected $aiService;

    public function __construct(
        WhatsAppService $whatsappService,
        AIService $aiService
    ) {
        $this->whatsappService = $whatsappService;
        $this->aiService = $aiService;
    }

    public function verify(Request $request)
    {
        $verifyToken = config('services.whatsapp.verify_token');
        
        if ($request->input('hub_mode') === 'subscribe' && 
            $request->input('hub_verify_token') === $verifyToken) {
            return response($request->input('hub_challenge'), 200);
        }
        
        return response('Verification failed', 403);
    }

    public function webhook(Request $request)
    {
        Log::info('Webhook recibido', ['payload' => $request->all()]);
        
        try {
            $data = $request->all();
            
            if (!isset($data['entry'][0]['changes'][0]['value']['messages'])) {
                return response()->json(['status' => 'ignored'], 200);
            }
            
            $messages = $data['entry'][0]['changes'][0]['value']['messages'];
            
            foreach ($messages as $message) {
                if ($message['type'] === 'statuses') {
                    continue;
                }
                
                $telefono = $message['from'];
                $texto = $message['text']['body'] ?? '';
                
                $this->processMessage($telefono, $texto);
            }
            
            return response()->json(['status' => 'ok'], 200);
            
        } catch (\Exception $e) {
            Log::error('Error en webhook', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error'], 500);
        }
    }

    protected function processMessage($telefono, $texto)
    {
        $cliente = Cliente::firstOrCreate(
            ['telefono' => $telefono],
            [
                'nombre' => 'Cliente ' . $telefono,
                'whatsapp_id' => $telefono
            ]
        );
        
        Conversacion::create([
            'cliente_id' => $cliente->id,
            'tipo' => 'entrante',
            'mensaje' => $texto,
            'metadata' => ['telefono' => $telefono]
        ]);
        
        $contexto = Conversacion::where('cliente_id', $cliente->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->reverse()
            ->map(function($conv) {
                return [
                    'rol' => $conv->tipo === 'entrante' ? 'user' : 'assistant',
                    'mensaje' => $conv->mensaje
                ];
            })
            ->toArray();
        
        try {
            $respuesta = $this->aiService->processMessage(
                $cliente->id,
                $texto,
                $contexto
            );
            
            Conversacion::create([
                'cliente_id' => $cliente->id,
                'tipo' => 'saliente',
                'mensaje' => $respuesta,
                'metadata' => ['telefono' => $telefono]
            ]);
            
            $this->whatsappService->sendTextMessage($telefono, $respuesta);
            
        } catch (\Exception $e) {
            Log::error('Error al procesar mensaje', [
                'cliente_id' => $cliente->id,
                'error' => $e->getMessage()
            ]);
            
            $errorMsg = "Lo siento, estoy teniendo problemas técnicos. Por favor, intenta de nuevo más tarde.";
            $this->whatsappService->sendTextMessage($telefono, $errorMsg);
        }
    }
}
EOF

print_message "Controlador Webhook creado"

# ============================================
# 10. CONFIGURAR RUTAS
# ============================================
print_header "CONFIGURANDO RUTAS"

cat > routes/api.php <<'EOF'
<?php
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('whatsapp')->group(function () {
    Route::get('/webhook', [WhatsAppWebhookController::class, 'verify'])
        ->name('whatsapp.verify');
    Route::post('/webhook', [WhatsAppWebhookController::class, 'webhook'])
        ->name('whatsapp.webhook');
});

Route::get('/test', function () {
    return response()->json([
        'message' => 'Chatbot funcionando correctamente',
        'status' => 'success'
    ]);
});
EOF

print_message "Rutas configuradas"

# ============================================
# 11. CONFIGURAR SERVICIOS
# ============================================
print_header "CONFIGURANDO SERVICIOS"

# Modificar config/services.php
if [ -f config/services.php ]; then
    # Hacer backup
    cp config/services.php config/services.php.backup
    
    # Agregar configuraciones
    cat >> config/services.php <<'EOF'

    'whatsapp' => [
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
        'api_version' => env('WHATSAPP_API_VERSION', 'v18.0'),
    ],
    
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 500),
        'temperature' => env('OPENAI_TEMPERATURE', 0.7),
    ],
EOF
fi

print_message "Servicios configurados"

# ============================================
# 12. CREAR SEEDERS
# ============================================
print_header "CREANDO SEEDERS"

php artisan make:seeder ProductosSeeder

cat > database/seeders/ProductosSeeder.php <<'EOF'
<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductosSeeder extends Seeder
{
    public function run()
    {
        $productos = [
            [
                'codigo' => 'CLO-001',
                'nombre' => 'Cloro Gel 500ml',
                'descripcion' => 'Cloro gel concentrado para desinfección profunda',
                'precio' => 4500,
                'stock' => 100,
                'categoria' => 'multiuso',
                'unidad_medida' => 'unidad'
            ],
            [
                'codigo' => 'CLO-002',
                'nombre' => 'Cloro Líquido 1L',
                'descripcion' => 'Cloro líquido para blanqueo y desinfección',
                'precio' => 3800,
                'stock' => 150,
                'categoria' => 'multiuso',
                'unidad_medida' => 'unidad'
            ],
            [
                'codigo' => 'DET-001',
                'nombre' => 'Detergente Líquido 2L',
                'descripcion' => 'Detergente líquido para ropa y superficies',
                'precio' => 8900,
                'stock' => 80,
                'categoria' => 'cocina',
                'unidad_medida' => 'unidad'
            ],
            [
                'codigo' => 'DES-001',
                'nombre' => 'Desengrasante 1L',
                'descripcion' => 'Desengrasante para cocinas y superficies difíciles',
                'precio' => 7200,
                'stock' => 60,
                'categoria' => 'cocina',
                'unidad_medida' => 'unidad'
            ],
            [
                'codigo' => 'BAÑ-001',
                'nombre' => 'Limpiador de Inodoros 1L',
                'descripcion' => 'Limpiador especial para inodoros y baños',
                'precio' => 5900,
                'stock' => 120,
                'categoria' => 'baño',
                'unidad_medida' => 'unidad'
            ],
            [
                'codigo' => 'VID-001',
                'nombre' => 'Limpiavidrios 500ml',
                'descripcion' => 'Limpiador de vidrios sin amoníaco',
                'precio' => 4200,
                'stock' => 90,
                'categoria' => 'baño',
                'unidad_medida' => 'unidad'
            ],
            [
                'codigo' => 'PIS-001',
                'nombre' => 'Cera para Pisos 1L',
                'descripcion' => 'Cera líquida para pisos de madera y cerámica',
                'precio' => 10500,
                'stock' => 40,
                'categoria' => 'pisos',
                'unidad_medida' => 'unidad'
            ]
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }
    }
}
EOF

# Modificar DatabaseSeeder
cat > database/seeders/DatabaseSeeder.php <<'EOF'
<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            ProductosSeeder::class,
        ]);
    }
}
EOF

print_message "Seeders creados"

# ============================================
# 13. CREAR COMANDO PERSONALIZADO
# ============================================
print_header "CREANDO COMANDO PERSONALIZADO"

php artisan make:command ProcessWhatsAppMessages

cat > app/Console/Commands/ProcessWhatsAppMessages.php <<'EOF'
<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WhatsAppService;
use App\Services\AIService;
use App\Models\Conversacion;
use App\Models\Cliente;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppMessages extends Command
{
    protected $signature = 'whatsapp:process {limit=10}';
    protected $description = 'Procesar mensajes de WhatsApp pendientes';

    protected $whatsappService;
    protected $aiService;

    public function __construct(
        WhatsAppService $whatsappService,
        AIService $aiService
    ) {
        parent::__construct();
        $this->whatsappService = $whatsappService;
        $this->aiService = $aiService;
    }

    public function handle()
    {
        $limit = $this->argument('limit');
        
        $this->info("Procesando mensajes pendientes (límite: {$limit})");
        
        $mensajesPendientes = Conversacion::where('tipo', 'entrante')
            ->whereNotIn('id', function($query) {
                $query->select('conversacion_id')
                      ->from('conversaciones as respuestas')
                      ->whereColumn('respuestas.metadata->conversacion_id', 'conversaciones.id');
            })
            ->limit($limit)
            ->get();
        
        $this->info("Mensajes encontrados: " . $mensajesPendientes->count());
        
        foreach ($mensajesPendientes as $mensaje) {
            $this->line("Procesando mensaje: {$mensaje->mensaje}");
            
            try {
                $cliente = Cliente::find($mensaje->cliente_id);
                
                $respuesta = $this->aiService->processMessage(
                    $cliente->id,
                    $mensaje->mensaje,
                    []
                );
                
                Conversacion::create([
                    'cliente_id' => $cliente->id,
                    'tipo' => 'saliente',
                    'mensaje' => $respuesta,
                    'metadata' => ['conversacion_id' => $mensaje->id]
                ]);
                
                $this->whatsappService->sendTextMessage(
                    $cliente->telefono,
                    $respuesta
                );
                
                $this->info("✅ Mensaje procesado exitosamente");
                
            } catch (\Exception $e) {
                Log::error('Error procesando mensaje', [
                    'mensaje_id' => $mensaje->id,
                    'error' => $e->getMessage()
                ]);
                $this->error("❌ Error: " . $e->getMessage());
            }
        }
        
        $this->info("Procesamiento completado");
    }
}
EOF

print_message "Comando creado"

# ============================================
# 14. CREAR MIDDLEWARE
# ============================================
print_header "CREANDO MIDDLEWARE"

cat > app/Http/Middleware/VerifyWhatsAppSignature.php <<'EOF'
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyWhatsAppSignature
{
    public function handle(Request $request, Closure $next)
    {
        $signature = $request->header('X-Hub-Signature-256');
        $verifyToken = config('services.whatsapp.verify_token');
        
        Log::info('Webhook signature verification', [
            'has_signature' => !empty($signature),
            'verify_token' => $verifyToken
        ]);
        
        return $next($request);
    }
}
EOF

# Registrar middleware en Kernel.php
if [ -f app/Http/Kernel.php ]; then
    sed -i '/protected $routeMiddleware = \[/a \ \ \ \ \'verify.whatsapp\' => \\App\\Http\\Middleware\\VerifyWhatsAppSignature::class,' app/Http/Kernel.php
fi

print_message "Middleware creado"

# ============================================
# 15. CREAR ARCHIVO DE AYUDA
# ============================================
print_header "CREANDO DOCUMENTACIÓN"

cat > README.md <<'EOF'
# Chatbot WhatsApp - Productos de Limpieza

## 📋 Descripción
Sistema de chatbot para WhatsApp con Laravel, MySQL e IA para la venta de productos de limpieza.

## 🚀 Instalación Rápida
```bash
./setup_chatbot.sh