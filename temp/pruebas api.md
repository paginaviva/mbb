Sí. Se puede y conviene probar ahora en el SHS (no es necesario esperar a las etapas 6 y 7), pero con precauciones básicas y verificaciones previas.

Resumen corto:

Qué probar ahora: validación JSON, validación form-data, creación real de archivo (permiso/propietario), manejo de errores (JSON/HTML), redirecciones.
Qué comprobar antes en el SHS: backup, que SITE_DIR/POST_DIR estén correctos y escribibles, versión PHP, y que el script tenga permisos de ejecución/escritura.
Qué esperar más tarde (ETAPA 6/7): aplicar hardening (CORS, logging, headers, seguridad) y documentar; recomendable antes de exponer la API públicamente, pero no obligatorio para pruebas.
Comandos de prueba recomendados (ejemplos para ejecutar en SHS):

Prueba API (JSON):
curl -i -X POST 'https://tu-shs/gestion/procesar-post.php'
-H 'Content-Type: application/json' -H 'Accept: application/json'
-d '{"datos_documento":"[DATOS_DOCUMENTO]\nNombreArchivoHTML: prueba-shs.php\nTituloDocumento: Prueba SHS"}'
Prueba formulario (form-data):
curl -i -X POST 'https://tu-shs/gestion/procesar-post.php'
-F 'datos_documento=[DATOS_DOCUMENTO]\nNombreArchivoHTML: prueba-form.php\nTituloDocumento: Prueba Form'
Verificar archivo creado y permisos:
ls -l /ruta/a/POST_DIR/prueba-shs.php
cat /ruta/a/POST_DIR/prueba-shs.php | head -n 40
Checks previos en SHS:

Hacer backup de procesar-post.php y de la carpeta POST_DIR.
Confirmar que config.php apunta al SITE_DIR correcto y que POST_DIR existe y es escribible por el usuario PHP-FPM/Apache.
Evaluar usar una carpeta de pruebas separada si no quieres tocar producción.
Recomendación de orden:

Validar JSON/form-data y errores (ya implementado).
Probar creación de archivo en SHS (para confirmar permisos/paths).
Aplicar ETAPA 6 (hardening) si la API va a ser pública.
Documentar en ETAPA 7.
He guardado este resumen en:

temp/prueba_shs_recomendaciones_2026-01-19_0210.md
¿Deseas que te proporcione los comandos exactos adaptados a la configuración de tu servidor (SITE_URL/SITE_DIR) o que ejecute alguna prueba en tu servidor si me compartes las URLs y credenciales pertinentes?

|--------0--------||--------0--------|

# Guía paso a paso para probar procesar-post.php en el SHS

Fecha: 2026-01-23

Resumen: Instrucciones desde subir los archivos al Shared Hosting Server (SHS), verificar permisos, ejecutar pruebas (JSON y form-data), validar resultados y cómo revertir en caso de problema.

---

## 0) Requisitos previos (en tu máquina / SHS)
- Acceso SSH/SFTP al SHS (usuario y path del sitio).
- Ubicación de la web en SHS (ej: /home/usuario/public_html/).
- Conocer SITE_DIR / POST_DIR en config.php.
- Versión PHP >= 7.4 recomendada. Comprobar: php -v
- Tener respaldo (backup) de los archivos y del directorio POST_DIR.

---

## 1) Respaldos (en SHS)
1. Conéctate por SSH:
   ssh usuario@shs.example.com
2. Dentro del directorio del sitio:
   cd /ruta/a/tu/sitio/gestion
3. Crea respaldo del archivo que vas a modificar:
   cp procesar-post.php procesar-post.php.backup-$(date +%F_%H%M)
4. (Opcional) Respaldar POST_DIR:
   tar -czf /home/usuario/backups/post_dir_$(date +%F_%H%M).tgz /ruta/a/post

---

## 2) Subir cambios al SHS
Opción A — con git (si el SHS tiene repo):
- En tu máquina:
  git add gestion/procesar-post.php
  git commit -m "Dual endpoint: API + Form"
  git push origin main
- En SHS:
  cd /ruta/a/repo
  git pull origin main

Opción B — scp/rsync:
- scp gestion/procesar-post.php usuario@shs.example.com:/ruta/a/tu/sitio/gestion/
- o rsync -avz --progress gestion/procesar-post.php usuario@shs.example.com:/ruta/a/tu/sitio/gestion/

Opción C — SFTP/FTP: subir el archivo a /gestion/.

Importante: No sobrescribas config.php en SHS; solo subir procesar-post.php y/o archivos de pruebas.

---

## 3) Ajustes previos de entorno (recomendado para pruebas)
- Crear un POST_DIR de pruebas para no tocar producción:
  mkdir -p /ruta/a/tu/sitio/post_test
  chown -R www-data:www-data /ruta/a/tu/sitio/post_test
  chmod -R 775 /ruta/a/tu/sitio/post_test
- Temporalmente modificar config.php para que POST_DIR apunte a post_test,
  o editar una copia de procesar-post.php para usar $dir_posts = __DIR__ . '/../post_test';

Sugerencia segura: copiar config.php y modificar la copia, o usar una bandera en procesar-post.php para usar fallback local si detecta entorno de pruebas.

---

## 4) Permisos y propiedad
- Verificar propiedad del directorio donde se escribirá:
  ls -ld /ruta/a/post
- Si es necesario:
  sudo chown -R www-data:www-data /ruta/a/post
  sudo chmod -R 775 /ruta/a/post

Nota: usuario www-data puede variar (apache, nobody, nginx). Ajustar según servidor.

---

## 5) Comprobaciones iniciales
- Verificar que procesar-post.php está en su lugar y sin errores sintácticos:
  php -l /ruta/a/tu/sitio/gestion/procesar-post.php
- Revisar logs si aparece un error en la carga:
  tail -n 200 /var/log/apache2/error.log
  tail -n 200 /var/log/nginx/error.log
  tail -n 200 /var/log/php7.4-fpm.log

---

## 6) Pruebas básicas (desde tu máquina o desde el SHS con curl)

A) Test JSON inválido (esperar error JSON sobre parseo)
curl -i -X POST 'https://tu-dominio/gestion/procesar-post.php' \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{datos_documento: invalid json}'

Respuesta esperada: JSON con success=false y mensaje de JSON inválido.

B) Test JSON con campos faltantes (validación)
curl -i -X POST 'https://tu-dominio/gestion/procesar-post.php' \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{"datos_documento":"[DATOS_DOCUMENTO]\nNombreArchivoHTML: prueba-shs.php"}' | jq

Respuesta esperada: JSON success=false y lista de errores de validación.

C) Test JSON completo (crear post - en entorno de prueba)
Prepara un archivo test_post.json con el campo datos_documento completo (Título, Autor, Fecha, etc).
curl -i -X POST 'https://tu-dominio/gestion/procesar-post.php' \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d @test_post.json | jq

Respuesta esperada: success=true y data con url_post y archivo creado.

D) Test form-data (comportamiento formulario)
curl -i -X POST 'https://tu-dominio/gestion/procesar-post.php' \
  -F 'datos_documento=[DATOS_DOCUMENTO]\nNombreArchivoHTML: prueba-form.php\nTituloDocumento: Prueba Form'

Respuesta esperada: redirección a la URL del post o visualización HTML de error.

E) Test CORS/OPTIONS (si aplicaste CORS)
curl -i -X OPTIONS 'https://tu-dominio/gestion/procesar-post.php' \
  -H 'Origin: https://example.com' \
  -H 'Access-Control-Request-Method: POST' \
  -H 'Access-Control-Request-Headers: Content-Type, Accept' -v

Respuesta esperada: 200 y headers Access-Control-Allow-*.

---

## 7) Validar creación del archivo y permisos
- Listar archivo creado:
  ls -l /ruta/a/post_test/prueba-shs.php
- Ver contenido y primeras líneas:
  head -n 40 /ruta/a/post_test/prueba-shs.php
- Ver propietario y permisos correctos (mismo user que ejecuta PHP).

---

## 8) Verificar funcionamiento en navegador
- Abrir en navegador: https://tu-dominio/post/prueba-shs.php
- Verificar que el contenido se renderiza sin errores PHP.

---

## 9) Revisar logs y depuración
- Si falla, revisar:
  tail -n 200 /var/log/apache2/error.log
  tail -n 200 /var/log/php*-fpm.log
- Añadir temporalmente líneas de debug en procesar-post.php:
  error_log("DEBUG: variable X: " . print_r($var, true));

---

## 10) Reversión / Limpieza
- Borrar archivos de prueba:
  rm /ruta/a/post_test/prueba-shs.php
- Restaurar procesar-post.php desde backup:
  mv /ruta/a/tu/sitio/gestion/procesar-post.php.backup-YYYY-MM-DD_HHMM /ruta/a/tu/sitio/gestion/procesar-post.php

---

## 11) Notas y recomendaciones finales
- Realizar pruebas en directorio de pruebas para evitar tocar producción.
- Registrar outputs de curl (guardar respuestas JSON) para trazabilidad.
- Si planeas exponer la API públicamente, completar ETAPA 6 (hardening, logging, CORS controlado, autenticación).
- Si quieres, adapto los comandos a tus rutas reales (SITE_URL, SITE_DIR, usuario SHS). Proporciona esos valores y genero los comandos listos para pegar.

---