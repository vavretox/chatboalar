# Chatbot WhatsApp para productos de limpieza

Backend Laravel 12 con WhatsApp Cloud API, MySQL, colas y OpenAI. Conserva clientes y conversaciones, consulta el catálogo, administra carritos y procesa pedidos con control transaccional de stock.

## Puesta en marcha

1. Copia `.env.example` a `.env` y configura MySQL.
2. Completa `WHATSAPP_ACCESS_TOKEN`, `WHATSAPP_PHONE_NUMBER_ID`, `WHATSAPP_VERIFY_TOKEN`, `WHATSAPP_APP_SECRET` y `OPENAI_API_KEY`.
3. Ejecuta `php artisan key:generate` y `php artisan migrate --seed`.
4. Inicia la web con Laragon o `php artisan serve`.
5. Inicia el trabajador con `php artisan whatsapp:process`.
6. En Meta configura `https://tu-dominio/api/whatsapp/webhook` y suscribe el evento `messages`.

Para procesar lo pendiente y terminar: `php artisan whatsapp:process --stop-when-empty`.

## Flujo conversacional

El webhook valida la firma, guarda el mensaje y lo envía a la cola `whatsapp`. El trabajador reconoce catálogo, carrito, confirmación, estado y cancelación. Pedidos y stock se procesan dentro de transacciones MySQL. Los mensajes que no sean operaciones se envían a OpenAI cuando existe una clave; si no, se muestra el menú local.

Ejemplos: `catálogo`, `quiero 3 cloro gel`, `ver carrito`, `confirmar pedido`, `estado del pedido` y `cancelar pedido`.

## Endpoints

- `GET /api/test`: comprobación rápida.
- `GET /api/whatsapp/webhook`: verificación de Meta.
- `POST /api/whatsapp/webhook`: recepción y encolado de mensajes.

Ejecuta las pruebas con `php artisan test`.
