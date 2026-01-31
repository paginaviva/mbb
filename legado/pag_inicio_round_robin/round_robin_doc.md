# Documentación: Round Robin — pag_inicio_round_robin 📝

## Resumen rápido ✅
- Objetivo: copiar a `pag_inicio_round_robin/` los archivos relevantes para la visualización de las dos secciones Round Robin en la home y documentar su funcionamiento.
- Archivos copiados aquí: `index_desktop.php`, `index_mobile.php`, y un extracto de lógica llamado `home_data_provider_round_robin.php`.
- Nota: Los demás archivos mencionados anteriormente (secciones completas, renderer, inis) **no** se copian porque su utilidad es diferente a la requerida para esta tarea y han sido descartados para este paquete local.

---

## ¿Qué hace cada archivo incluido? 🔧
- `index_desktop.php` (copia): muestra en la home el bloque **Round Robin Ayer** (2 posts) y **Otros Juegos Round Robin** (4 posts). Se integra con el proveedor de datos (`get_home_data()` originalmente).

- `index_mobile.php` (copia): versión móvil, mismo comportamiento y estructura (dos bloques: los 2 más recientes y los 4 siguientes).

- `home_data_provider_round_robin.php` (nuevo): extracto independiente con las funciones necesarias para obtener los posts del Round Robin desde el manifiesto local. Incluye:
  - `parse_spanish_date()` — parseo básico de fechas en español.
  - `sort_posts_by_date()` — orden descendente por fecha.
  - `enrich_post()` — agrega campos útiles (id, slug, url, category).
  - `get_round_robin_group($posts, $tag = 'resumen diario round robin', $latest = 2, $others = 4)` — función principal que devuelve `['latest_two'=>..., 'others'=>...]` filtrando por tag.

---

## Lógica Round Robin explicada (breve) 💡
1. Se recogen posts ordenados por fecha.
2. Se filtran los posts que tienen la etiqueta exacta (case-insensitive) **"resumen diario round robin"**.
3. Se consideran los 2 primeros como `latest_two` (para "Round Robin Ayer") y los siguientes 4 como `others` ("Otros Juegos Round Robin").

---

## Pasos para duplicar las dos secciones (guía breve) 🛠️
1. Definir una nueva etiqueta para el segundo conjunto (por ejemplo `resumen diario round robin B`), etiquetar posts.
2. Reusar `get_round_robin_group()` (cambiando el tag) para crear `block_a2` (o similar) que contenga `latest_two` y `others` del grupo B.
3. Copiar el markup de `index_desktop.php` y `index_mobile.php` para crear bloques visibles en la home, ajustar IDs/títulos y eventos de tracking.

---

## Archivos descartados (no incluidos aquí) ⚠️
- `seccion/round-robin.php` y `includes/seccion_renderer.php` (son para página de sección completa, no necesarias en este paquete local).
- `config/secciones/round-robin.ini` y `la-final-2025-26.ini` (configuración de sección completa; utilidad distinta).

---

## Siguientes pasos recomendados ✨
- Si quieres, puedo implementar una duplicación real (copiar el bloque en las vistas, agregar `block_a2` y crear ejemplos de posts etiquetados) y abrir un PR con los cambios listos.

---

*(Documento generado automáticamente en `pag_inicio_round_robin/round_robin_doc.md`)*
