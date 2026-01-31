# Informe: Orden de despliegue de posts en `seccion/la-final-2025-26.php`

Fecha: 2026-01-28 06:34

## Resumen breve
La sección filtra posts por la configuración (tipo=tag, valor="La Final" 2025-26), luego ordena los posts por fecha **descendente** (más recientes primero), aplica búsqueda si hay query y finalmente renderiza los posts en ese orden.

## Hallazgos (archivos y comportamiento)
- `seccion/la-final-2025-26.php` — simple wrapper: carga `config/secciones/la-final-2025-26.ini` y llama `render_seccion($config)`.
- `config/secciones/la-final-2025-26.ini` — FILTRO: `tipo = "tag"`, `valor = "\"La Final\" 2025-26"` → se compara con tags de los posts (normalizado a minúsculas).
- `includes/seccion_renderer.php` — lógica principal:
  - Obtiene todos los posts desde `$posts` (manifest incluido en `includes/home_data_provider.php`).
  - Llama `sort_posts_by_date($all_posts)` (función en `includes/home_data_provider.php`) — **ordena por fecha descendente**.
  - Itera `sorted_posts` y aplica filtro:
    - Si `tipo == 'tag'`: normaliza `post['tags']` y compara con `filter_value_norm` usando `in_array`.
    - Si existe `q` (GET), aplica búsqueda en `title`, `subtitle` y contenido (`seccion_search_in_content`).
  - Los posts que pasan filtros se agregan a `$final_posts` en el mismo orden (por fecha descendente).
  - Finalmente, recorre `foreach ($final_posts as $post)` para renderizar las entradas en pantalla.

## Orden concreto que se sigue para mostrar posts
1. Leer configuración de la sección (tipo y valor de filtro).  
2. Obtener todos los posts y **ordenarlos por fecha descendente** (más recientes primero) — función `sort_posts_by_date`.  
3. Filtrar: incluir solo posts cuya etiqueta o categoría normalizada coincida con `filter_value_norm`.  
4. Si hay búsqueda (`q`), filtrar sobre el subset (título, subtítulo, contenido).  
5. Renderizar `$final_posts` en el orden resultante (manteniendo orden descendente por fecha).

## Comandos útiles para verificar localmente
- Ver el filtro configurado:
  - `grep -n "FILTRO" -n config/secciones/la-final-2025-26.ini`
- Confirmar que la función de orden es descendente:
  - `grep -n "function sort_posts_by_date" -n includes/home_data_provider.php && sed -n '1,120p' includes/home_data_provider.php`  (ver la implementación que resta timestamps para ordenar)
- Listar primeros 10 slugs ordenados por fecha (PHP rápido):
  - `php -r "include 'includes/home_data_provider.php'; include 'posts_manifest.php'; $s = sort_posts_by_date($posts); $i=0; foreach(
$s as $slug => $p){ echo date('Y-m-d', parse_spanish_date($p['date'])).' '.$slug."\n"; if(++$i>=10) break; }"`
- Buscar posts que coincidan con el filtro actual:
  - `php -r "include 'includes/home_data_provider.php'; include 'posts_manifest.php'; $s = sort_posts_by_date($posts); foreach($s as $slug => $p){ $tags = array_map(fn($t)=>mb_strtolower(trim($t),'UTF-8'), $p['tags'] ?? []); if(in_array(mb_strtolower(trim('\"La Final\" 2025-26'), 'UTF-8'), $tags)) echo $slug."\n"; }"`

## Riesgos / notas
- El valor de FILTRO en el INI contiene comillas y el año ("La Final" 2025-26). Esto exige que el tag en los posts coincida exactamente (tras normalizar) con esa cadena; si los tags usados son solo `La Final`, es posible que no haya coincidencias. Recomiendo verificar cómo están etiquetados los posts en `posts_manifest.php` antes de asumir que el filtro va a funcionar.
- El orden de despliegue se basa en la fecha parseada por `parse_spanish_date()`. Si hay posts con fechas mal formateadas, aparecerán al final (timestamp 0) o en orden inesperado.

---

Si quieres, preparo una comprobación automática (script/ comando PHP) que liste los posts que coinciden con el filtro actual y muestre los 5 primeros en orden (para validar configuración y etiquetado).