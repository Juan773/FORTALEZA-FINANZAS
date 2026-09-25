# Guía de despliegue a producción — Fortaleza Finances

Checklist para el corte a producción. Como decidiste que **no hace falta que
el sistema viejo y el nuevo convivan** (uso exclusivo del nuevo desde el día
del corte), este es un plan de corte directo, no de migración gradual con
ambos sistemas activos.

## 0. Antes de tocar producción

- [ ] **Backup completo de la base de datos real** (`mysqldump` completo, no solo
      las tablas que vamos a modificar). Esto es obligatorio antes del paso 2 —
      dos migraciones alteran columnas existentes.
- [ ] Confirmar con el cliente el RUC real de "COLEGIO DE ENFERMEROS DEL PERU"
      (hoy `2147483647` en `gen_empresa`, dato corrupto encontrado durante la
      migración) y corregirlo con un `UPDATE` puntual antes o después del corte.
- [ ] Decidir qué hacer con las tablas espejo `fin_caja_`, `fin_caja_detalle_`,
      `fin_estado_cuenta_` (snapshot histórico sin uso claro, detectado en el
      diagnóstico inicial) — no las toca ninguna migración, pero conviene que el
      cliente confirme si se pueden archivar o hay que conservarlas.

## 1. Requisitos del servidor

- PHP 8.3+ (usamos 8.5 en desarrollo; cualquier 8.3+ soportado funciona)
- Extensiones: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`,
  `json`, `bcmath` — todas estándar en cualquier instalación PHP moderna
- MySQL 8.0+ o MariaDB 10.6+ (la BD actual funciona sin cambios de motor)
- Composer 2.x
- Node 20+ solo para compilar assets en el build (no hace falta en el servidor
  si se sube `public/build/` ya compilado)
- Servidor web (Nginx/Apache) apuntando a `public/`

## 2. Variables de entorno (`.env` de producción)

Copiar `.env.example`, y como mínimo definir:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<dominio-real>
APP_KEY=                    # generar con: php artisan key:generate

DB_CONNECTION=mysql
DB_HOST=<host real>
DB_DATABASE=<nombre real de la BD legacy — la misma, no una nueva>
DB_USERNAME=<usuario con permisos de ALTER TABLE, ver paso 3>
DB_PASSWORD=<clave real>

LEGACY_DB_HOST=<mismo host>
LEGACY_DB_DATABASE=<misma BD>
LEGACY_DB_USERNAME=<mismo usuario>
LEGACY_DB_PASSWORD=<misma clave>

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true   # obligatorio si el sitio corre en HTTPS
```

**Importante — `APP_DEBUG=false` es obligatorio.** El diagnóstico original
marcó como problema de seguridad que el sistema legacy exponía el SQL crudo en
los errores (`die("Error Query: ".$sql)`); `APP_DEBUG=true` en producción
tendría el mismo efecto en Laravel. No lo dejes en `true` "para probar".

## 3. Antes del primer `migrate` en la BD real

Dos migraciones **alteran columnas existentes** de la base legacy (no crean
tablas nuevas, así que no son reversibles con solo restaurar el backup del
esquema de la app):

| Migración | Cambio | Ya lo aprobaste el |
|---|---|---|
| `widen_ct_clave_column_on_seg_usuario` | `seg_usuario.ct_clave` de `varchar(45)` a `varchar(255)` | Sí (Etapa 2) |
| `widen_emp_ruc_column_on_gen_empresa` | `gen_empresa.emp_ruc` de `INT` a `varchar(11)` | Sí (Etapa 3) |

El usuario de BD de producción necesita permiso `ALTER` sobre esas dos tablas
además de los permisos normales de `SELECT/INSERT/UPDATE/DELETE`.

## 4. Pasos del despliegue

```bash
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

`--force` es necesario porque `APP_ENV=production` bloquea `migrate` sin
confirmación interactiva — pero por eso el backup del paso 0 no es opcional.

## 5. Verificación post-despliegue (antes de avisarle a los usuarios)

- [ ] Login funciona con un usuario real existente (su clave SHA-1 legacy
      debe seguir funcionando — se migra a bcrypt sola en su primer login)
- [ ] `/estado-cuenta` con un socio real da el mismo saldo que el sistema viejo
      mostraba para ese socio (comparar un par a mano)
- [ ] `/balances` con un año real coincide con lo que el sistema viejo mostraba
- [ ] Emitir un recibo de prueba real, confirmar que el correlativo de
      `fin_comprobante` avanza en 1 y no se repite
- [ ] Revisar `/auditoria` — debe empezar a registrar las acciones desde el
      primer minuto

## 6. Aviso a los 7 usuarios del sistema

Como decidiste que no hay convivencia entre sistemas, comunícales el día y
hora exactos del corte. Después de ese momento:

- La URL del sistema viejo debería dejar de estar accesible (o al menos
  quedar en solo lectura) para evitar que alguien siga registrando datos ahí
  por costumbre mientras el nuevo ya está en uso.
- La primera vez que cada persona entre al sistema nuevo, su clave se
  actualiza sola a un formato más seguro — no hace falta que hagan nada
  distinto, usan la misma clave de siempre.

## 7. Lo que queda fuera de este despliegue (decisión ya tomada, no bloquea)

- No existe menú dinámico por módulo (`seg_modulo`/`seg_modulo_perfil`) — el
  sistema nuevo usa un menú fijo. Los 4 perfiles siguen controlando qué puede
  hacer cada quien vía las pantallas mismas, no vía un menú que se arma solo.
- La función `crearClave()` del recibo legacy nunca se reconstruyó (no
  formaba parte del código fuente entregado) — Ingresos/Caja usa un mecanismo
  propio de numeración, ya verificado contra el correlativo real.
