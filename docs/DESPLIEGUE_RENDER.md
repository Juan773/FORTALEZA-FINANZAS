# Despliegue en Render + TiDB Cloud (capa gratuita)

Render no ofrece MySQL gestionado gratis, así que la base de datos vive en
**TiDB Cloud Serverless** (compatible con el protocolo MySQL, capa gratuita
permanente) y la aplicación Laravel se despliega en **Render** como un
servicio web Docker gratuito.

Limitaciones de la capa gratis a tener en cuenta:
- El servicio web de Render **se duerme tras 15 min sin tráfico**; el
  primer request después tarda ~30-50s en responder mientras despierta.
- No hay acceso a shell en el plan gratuito de Render — por eso el
  contenedor ejecuta `migrate --force` automáticamente en cada arranque
  (ver `docker/entrypoint.sh`).
- TiDB Serverless free tier: 5 GB de almacenamiento, más que suficiente
  para este proyecto.

---

## 1. Crear el cluster en TiDB Cloud

1. Entra a https://tidbcloud.com y crea una cuenta gratuita.
2. Crea un cluster **Serverless** (el plan gratis por defecto).
3. En el dashboard del cluster, pestaña **Connect**:
   - Selecciona "General" / "Connect With: MySQL CLI" o similar.
   - Copia: **Host**, **Port** (normalmente `4000`), **User**, y genera/copia
     el **Password**.
   - El nombre de base de datos por defecto es `test`; puedes crear una
     propia (ej. `fortaleza`) desde la consola SQL de TiDB o al importar el
     dump.
4. Guarda esos 5 datos (host, port, user, password, database) — los
   necesitarás para las variables de entorno en Render.

## 2. Exportar la base de datos actual e importarla en TiDB

Usa la base de datos de tu entorno local (Sail), que ya tiene aplicadas
todas las migraciones y limpiezas de este proyecto — **no** el dump legacy
original.

```bash
cd /Users/juanquezada/PERSONAL/fortaleza-finances

# Exportar (ajusta el puerto si tu FORWARD_DB_PORT es distinto de 3307)
docker compose exec mysql mysqldump \
  -u root -p"$(grep ^DB_PASSWORD .env | cut -d= -f2)" \
  --no-tablespaces fortaleza_dev > /tmp/fortaleza_dump.sql

# Importar en TiDB (pide el password que copiaste en el paso 1)
mysql --ssl-mode=VERIFY_IDENTITY \
  -h <TIDB_HOST> -P 4000 -u <TIDB_USER> -p \
  -e "CREATE DATABASE IF NOT EXISTS fortaleza"

mysql --ssl-mode=VERIFY_IDENTITY \
  -h <TIDB_HOST> -P 4000 -u <TIDB_USER> -p fortaleza < /tmp/fortaleza_dump.sql
```

Notas:
- `mysqldump` sin `--routines --triggers` (por defecto) ya excluye las
  funciones/procedimientos/triggers del legacy — TiDB no los necesita
  porque el sistema nuevo replica esa lógica en PHP.
- Si el import falla en la única vista (`vt_empleado_usuario`) por el
  `DEFINER=`, puedes omitirla — no la usa el sistema nuevo:
  `grep -v "vt_empleado_usuario" ...` o edita el .sql y borra ese bloque.

## 3. Subir el código a GitHub

Render despliega desde un repositorio Git. El proyecto ya tiene un repo
git local con un commit inicial. Falta:

```bash
cd /Users/juanquezada/PERSONAL/fortaleza-finances
git remote add origin <URL_DE_TU_REPO_GITHUB>
git push -u origin main
```

Recomendación: crea el repo como **privado** en GitHub (contiene la lógica
de negocio de FORTALEZA).

## 4. Crear el servicio en Render

1. Entra a https://render.com y crea una cuenta (gratis).
2. **New > Blueprint**, conecta tu repo de GitHub. Render detectará
   `render.yaml` automáticamente y creará el servicio web Docker.
   - Alternativa manual: **New > Web Service**, elige el repo, Runtime =
     `Docker`, plan = `Free`.
3. En la sección **Environment** del servicio, completa las variables
   marcadas como "a definir" en `render.yaml` (`sync: false`):

   | Variable | Valor |
   |---|---|
   | `APP_KEY` | `base64:XCCPSdbuqY9StoxWGMMtctnVmNU7dynyths0FwZiZ1Y=` |
   | `APP_URL` | `https://<nombre-que-elijas>.onrender.com` |
   | `DB_HOST` | host de TiDB (paso 1) |
   | `DB_DATABASE` | `fortaleza` (o el nombre que usaste) |
   | `DB_USERNAME` | usuario de TiDB |
   | `DB_PASSWORD` | password de TiDB |

   (`DB_PORT=4000` y `MYSQL_ATTR_SSL_CA` ya vienen fijados en `render.yaml`.)

   > La `APP_KEY` de arriba fue generada específicamente para este
   > despliegue (`php artisan key:generate --show`). No la reutilices en
   > otro entorno; si algún día se filtra, genera una nueva del mismo modo.

4. Deploy. Sigue el log de build — el primer deploy corre `composer
   install`, `npm run build` y al arrancar el contenedor ejecuta las
   migraciones automáticamente contra TiDB.

## 5. Verificación post-despliegue

- Abre `https://<tu-servicio>.onrender.com/login` y confirma que carga.
- Prueba login con un usuario real (recuerda: la clave inicial de cada
  socio/usuario es su DNI, igual que en el sistema legacy).
- Revisa **Logs** en Render si algo falla — el entrypoint imprime la salida
  de `migrate --force` ahí mismo.
- Si necesitas ejecutar un comando artisan puntual (ej. crear un usuario),
  el plan gratis no tiene shell — tendrías que exponerlo como una ruta
  temporal protegida, o pasar temporalmente a un plan pago para usar
  **Shell** desde el dashboard de Render.

## 6. Dominio propio (opcional)

Render permite añadir un dominio propio gratis (Settings > Custom Domain)
con SSL automático. Actualiza `APP_URL` y `SESSION_DOMAIN` si lo haces.
