# Respaldo de base de datos

`chatboalar_schema.sql` contiene únicamente la estructura de la base de datos y puede versionarse sin exponer clientes, pedidos, conversaciones, contraseñas o tokens.

Para una instalación nueva se recomienda usar las migraciones y seeders:

```bash
php artisan migrate --force
php artisan db:seed --force
```

Antes de ejecutar los seeders en producción, define `ADMIN_EMAIL` y una contraseña segura en `ADMIN_PASSWORD`. Las claves de Evolution API y OpenAI deben configurarse en el panel o mediante variables privadas del servidor.

Los respaldos completos contienen datos sensibles y deben guardarse fuera del repositorio, en almacenamiento privado y cifrado.
