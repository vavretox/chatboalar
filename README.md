# Chatbot WhatsApp para productos de limpieza

Backend Laravel 12 con Evolution API, MySQL, colas y OpenAI. Conserva clientes y conversaciones, consulta el catálogo, administra carritos y procesa pedidos con control transaccional de stock.

## Puesta en marcha

1. Copia `.env.example` a `.env` y configura MySQL.
2. Define `ADMIN_NAME`, `ADMIN_EMAIL` y una contraseña segura en `ADMIN_PASSWORD`.
3. Ejecuta `php artisan key:generate`, `php artisan migrate --force` y `php artisan db:seed --force`.
4. Inicia la web con Laragon o `php artisan serve`.
5. Inicia el trabajador con `php artisan whatsapp:process`.
6. Configura Evolution API y OpenAI desde el panel de Integraciones.
7. En Evolution configura `https://tu-dominio/api/evolution/webhook` y activa `MESSAGES_UPSERT`.

Para procesar lo pendiente y terminar: `php artisan whatsapp:process --stop-when-empty`.

## Flujo conversacional

El webhook guarda el mensaje y lo envía a la cola `whatsapp`. El trabajador reconoce catálogo, carrito, confirmación, estado y cancelación. Pedidos y stock se procesan dentro de transacciones MySQL. Los mensajes se envían a OpenAI cuando la integración está activa.

Ejemplos: `catálogo`, `quiero 3 cloro gel`, `ver carrito`, `confirmar pedido`, `estado del pedido` y `cancelar pedido`.

## Endpoints

- `GET /api/test`: comprobación rápida.
- `GET /api/whatsapp/webhook`: verificación de Meta.
- `POST /api/whatsapp/webhook`: recepción y encolado de mensajes.
- `POST /api/evolution/webhook`: recepción de eventos de Evolution API.

## Base de datos y respaldos

Los seeders crean el administrador, identidad, configuración inicial del Bot IA, Evolution API, OpenAI y el catálogo de demostración. `ADMIN_PASSWORD` es obligatorio y nunca debe subirse al repositorio.

El respaldo versionado [database/backups/chatboalar_schema.sql](database/backups/chatboalar_schema.sql) contiene solo la estructura. Los respaldos completos con datos se guardan en `storage/app/private/backups/` y Git los excluye automáticamente.

Ejecuta las pruebas con `php artisan test`.
