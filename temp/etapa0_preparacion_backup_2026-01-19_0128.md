ETAPA 0 — Preparación y Backup

Fecha: 2026-01-19 01:28 (UTC)

Acciones realizadas:

1) Respaldo del archivo original
- Archivo original: `gestion/procesar-post.php`
- Respaldo creado: `gestion/procesar-post.php.backup`
- Observación: copia exacta del archivo original (backup local en el repo).

2) Verificación de configuración (`config.php`)
- Archivo leído: `config.php`
- Constantes encontradas y válidas: 
  - `OG_SITE_NAME` (Meridiano LVBP Blog)
  - `SITE_DIR` (/home/udcwscico/public_html/udn_meridiano_com/)
  - `SITE_URL` (https://www.meridiano.com/)
  - `POST_DIR` (/post/)
  - `SITE_AUTHOR_DEFAULT` (Redacción Meridiano)
- Observación: No es posible comprobar directorios fuera del workspace (p.ej. el path en `SITE_DIR`) desde este entorno.

3) Comprobación del directorio local `post/` del repositorio
- Ruta local verificada: `/workspaces/mbb/post`
- Estado: existe y es escribible (permissions: `drwxrwxrwx+`)
- Resultado de prueba de escritura: OK (archivo temporal creado y eliminado)

4) Documentación del estado
- Archivo de estado creado: `temp/etapa0_preparacion_backup_2026-01-19_0128.md`
- Contenido: resumen de acciones, ubicaciones de backups y observaciones para la ETAPA 1.

Notas y recomendaciones antes de ETAPA 1:
- Si el endpoint se va a probar en el servidor real, el usuario debe confirmar que `SITE_DIR` apunta al sitio real y que `POST_DIR` dentro de `SITE_DIR` es escribible por el proceso web.
- Si el sitio de producción difiere del entorno local, realizar pruebas en el servidor compartido para validar permisos y rutas.

Siguientes pasos propuestos:
- Iniciar ETAPA 1: implementar `esApiRequest()` y variable `$isApiMode`.
- Antes de desplegar al servidor de producción, realizar las pruebas de detección de tipo de petición (curl con headers JSON) en el servidor compartido.

Archivos creados:
- `/workspaces/mbb/gestion/procesar-post.php.backup` (backup)
- `/workspaces/mbb/temp/etapa0_preparacion_backup_2026-01-19_0128.md` (estado)

Si quieres, procedo ahora con ETAPA 1 (implementar detección de petición) y hago un commit con los cambios de ETAPA 0 (backup y documentación).