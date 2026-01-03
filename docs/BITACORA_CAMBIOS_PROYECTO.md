# Bitácora de Cambios del Proyecto - Meridiano Béisbol Blog

> **Entorno Operativo:**
> - **Proyecto:** MBB_20251124_H
> - **Plataforma:** Hosting Compartido (cPanel/LiteSpeed)
> - **Modo de Trabajo:** Exclusivamente en línea (sin entorno local, sin Git, sin PHP CLI).
> - **Propósito:** Este documento consolida el historial de cambios, versiones y correcciones del proyecto.

---

## Entradas de Historial

### [Versión 6.0] - 2026-01-02 20:38
**Resumen:** Implementación completa de Secciones Temáticas, actualización de portada y gestión de posts.
**Origen:** Solicitud directa del usuario.

#### Cambios Funcionales
- **Secciones Temáticas:**
  - Implementado sistema modular de secciones (`config/secciones/*.ini`) con renderizado dedicado (`includes/seccion_renderer.php`).
  - Creadas 6 nuevas secciones (Round Robin, Serie del Caribe, etc.) con configuración independiente.
  - Generación de entry points en `seccion/*.php`.
- **Navegación:**
  - Actualizado `menu.php` con dropdown "Secciones" dinámico.
  - Corrección de rutas relativas mediante `SITE_URL` para evitar errores 404 en subdirectorios.
- **Portada (Index):**
  - Modificados `index_desktop.php` e `index_mobile.php` para integrar el bloque de botones de secciones dinámico.
  - Implementada arquitectura de caché (`config/secciones_botones_actualiza.php`) para optimizar carga.
  - Estilos responsive en `css/styles.css` con soporte de colores personalizados por sección.
- **Gestión de Contenido:**
  - Actualizado `gestion/procesar-post.php` para soportar múltiples categorías (`$categories`) y mejorar la extracción de campos en la generación de archivos estáticos.

#### Validación Técnica
- Enlaces de menú y botones resuelven correctamente desde cualquier profundidad.
- Renderizado coherente en Móvil y Desktop.
- El generador de posts mantiene compatibilidad con la nueva estructura de datos.

- **Documentación:**
  - Incorporado el archivo `docs/20260102_integration_plan_otras_secciones.md` como referencia técnica para la integración de secciones temáticas.

---

### [Versión 5.9] - 2025-12-29 18:45
**Resumen:** GIP Fase 3.8 - Refactorización de diseño Desktop y alineación con cabecera.
**Origen:** Solicitud directa GIP.

#### Cambios Funcionales
- **Estructura Desktop (`includes/lista_posts_tag_desktop.php`)**:
  - Ajustada la cuadrícula a dos columnas (`col-lg-8` y `col-lg-4`) con espaciado `gx-4 gx-lg-5` para alineación perfecta con la cabecera.
  - El contenedor principal ahora usa `px-4 px-lg-5` coincidiendo con el diseño premium de los encabezados.
  - Implementada navegación mediante URLs amigables (`/tag/<slug-etiqueta>`).
- **Botones de Etiquetas**:
  - Nueva estilización "Premium": bordes totalmente redondeados (`50px`), sombras sutiles, estados `hover` con elevación y tipografía en mayúsculas.
  - El estado activo ahora soporta colores dinámicos definidos en `lista_posts_tag.ini` (campo `button_color`).
  - Resolución absoluta de URLs para evitar problemas de rutas relativas.
- **Configuración (`includes/lista_posts_tag.ini`)**:
  - Completadas las secciones de etiquetas con campos `button_color` y verificado el formato WebP de las imágenes de cabecera.
- **Núcleo (`includes/lista_posts_tag_core.php`)**:
  - Actualizado para propagar el color del botón y generar URLs amigables absolutas.

---

### [Versión 5.8] - 2025-12-29 18:22
**Resumen:** GIP Fase 3.7 - Estilización unificada de botones de etiquetas.
**Origen:** Solicitud directa GIP.

#### Cambios Funcionales
- **Estilos (`css/lista_posts.css`)**:
  - Definida la clase `.lpt-tag-button` con diseño de botón de texto (padding, border-radius, fondos claros).
  - Implementada la clase `.lpt-tag-button-active` para destacar la etiqueta seleccionada con el color primario del sitio (`#0085A1`).
  - Ajustada la distribución de botones para el panel lateral (vertical/full-width) y móvil (flex/wrap).
- **Plantillas (`desktop` y `mobile`)**:
  - Actualizado el marcado para utilizar las nuevas clases normalizadas.
  - Eliminados los estilos *inline* redundantes que existían en la plantilla móvil.
- **Validación técnica**:
  - Los botones mantienen un aspecto consistente en todos los dispositivos.
  - El estado activo es altamente visible.
  - La accesibilidad se mejoró mediante contrastes adecuados.

---

### [Versión 5.7] - 2025-12-29 18:31
**Resumen:** GIP Fase 3.6 - Implementación de carga incremental infinita (Continuous Scroll).
**Origen:** Solicitud directa GIP.

#### Cambios Funcionales
- **Backend:**
  - Creado `lpt_load.php` (Handler AJAX) que consume el núcleo LPT para devolver fragmentos HTML de lotes de publicaciones.
  - Soporte completo para filtrado por etiqueta, búsqueda y paginación mediante offset.
- **Frontend (Desktop):**
  - Implementado `IntersectionObserver` para detectar el fin de lista y cargar nuevos lotes automáticamente.
  - Gestión visual mediante elemento centinela (`#lpt-sentinel`) con spinner de carga.
- **Frontend (Móvil):**
  - Implementada lógica de Scroll Infinito adaptada al contenedor móvil.
  - Asegurada la inserción de nuevos elementos antes del bloque de enlaces inferior (`insertAdjacentHTML('beforebegin')` sobre el centinela).
- **Validación:**
  - El sistema carga correctamente lotes de 6 en 6 al hacer scroll.
  - Se detiene la carga cuando no hay más resultados (`X-Has-More: 0`).
  - Funciona correctamente en combinación con la búsqueda ("buscar y scrollear").

---

### [Versión 5.6] - 2025-12-29 18:18
**Resumen:** GIP Fase 3.5 - Implementación de buscador acotado por etiqueta.
**Origen:** Solicitud directa GIP.

#### Cambios Funcionales
- **Núcleo `includes/lista_posts_tag_core.php`:**
  - Actualizada función `lpt_get_tag_batch` para aceptar parámetro de búsqueda opcional `$search_query`.
  - Implementada lógica de búsqueda híbrida:
    1. Coincidencia en título y subtítulo.
    2. Coincidencia en contenido completo mediante lectura de archivos físicos (solo sobre el subset de la etiqueta activa).
  - Integrada lógica para resolver rutas de archivos de posts (`search_in_content` helper interno).
- **Interfaz de Usuario:**
  - **Desktop:** Añadido formulario de búsqueda en la parte superior de la columna principal, preservando la etiqueta activa.
  - **Móvil:** Añadido formulario de búsqueda simplificado en la parte superior del contenedor.
  - Implementados botones para limpiar la búsqueda y restaurar el listado completo de la etiqueta.
- **Validación:**
  - Búsqueda "jonrón" en etiqueta "venezuela" devuelve resultados correctos y no incluye posts de otras etiquetas.
  - La ausencia de resultados muestra el mensaje "Sin noticias" estándar de la etiqueta.

---

### [Versión 5.5] - 2025-12-29 18:05
**Resumen:** GIP Fase 3.4 - Unificación visual de tarjeta de artículo para LPT.
**Origen:** Solicitud directa GIP.

#### Cambios Funcionales
- **Visualización de Tarjeta:**
  - Definida estructura de tarjeta unificada para Desktop y Móvil (`.post-preview`).
  - Implementado diseño: Fecha en cursiva inline con el título, seguido del extracto.
  - Eliminados elementos visuales no requeridos (imágenes, categorías, tags) en estos listados.
- **Estilos `css/lista_posts.css`:**
  - Añadida clase `.lpt-inline-date` para gestionar la fecha en línea.
  - Ajustes de `line-height` en títulos para mejorar legibilidad con fecha inline.
- **Plantillas:**
  - `includes/lista_posts_tag_mobile.php`: Eliminados estilos inline obsoletos, integrado CSS compartido y contenedor Bootstrap estándar.
  - `includes/lista_posts_tag_desktop.php`: Actualizado marcado HTML para usar la nueva estructura de tarjeta.

#### Validación técnica
- La tarjeta se renderiza con la misma estructura HTML base en ambos dispositivos.
- Título y extracto son elementos clicables independientes.
- La fecha aparece correctamente antes del título en la misma línea visual.

---

### [Versión 5.4] - 2025-12-29 17:59
**Resumen:** GIP Fase 3.3 - Implementación de filtrado, ordenación y paginación en el núcleo LPT.
**Origen:** Solicitud directa GIP.

#### Cambios Funcionales
- **Núcleo `includes/lista_posts_tag_core.php`:**
  - Implementada función `lpt_get_tag_batch($tag_slug, $offset)` que centraliza la lógica de filtrado.
  - Integración con `home_data_provider.php` para reutilizar lógica de normalización de tags y ordenación por fecha.
  - Soporte de paginación por lotes (configurada en 6 items por defecto) y detección de "más resultados" (`has_more`).
- **Plantillas Desktop y Móvil:**
  - Actualizadas para consumir `lpt_get_tag_batch` en lugar de lógica ad-hoc.
  - Implementado renderizado de lista real filtrada desde el manifiesto `posts_manifest.php`.
  - Preparadas visualmente para futura carga incremental (botón placeholder en desktop, indicador en móvil).

#### Validación técnica
- El sistema filtra correctamente las publicaciones por etiqueta normalizada (lowercase).
- El ordenamiento respeta la fecha descendente utilizando el parseador de fechas del proyecto.
- La segmentación por lotes funciona correctamente, mostrando los primeros 6 resultados en la carga inicial.

---

### [Versión 5.3] - 2025-12-29 17:55
**Resumen:** GIP Fase 3.2 - Implementación de plantilla móvil para LPT.
**Origen:** Solicitud directa GIP.

#### Cambios Funcionales
- **Plantilla Móvil `includes/lista_posts_tag_mobile.php`:**
  - Implementada estructura visual con doble bloque de navegación por etiquetas (superior e inferior).
  - Integrada lógica de renderizado de artículos desde el manifiesto `posts_manifest.php`, filtrando por la etiqueta activa.
  - Diseño optimizado para móvil: sin sidebar, tarjetas simplificadas (título, fecha, extracto) y botones de navegación táctiles.
- **Actualización `lpt.php`:** Activada la detección de dispositivos móviles para servir la nueva plantilla específica en lugar del fallback de escritorio.

#### Validación técnica
- La plantilla móvil carga correctamente los estilos y la configuración definida en `lista_posts_tag.ini`.
- El filtrado de artículos funciona utilizando la lógica del manifiesto existente.
- La navegación entre etiquetas es fluida y marcada visualmente en ambos bloques de botones.

---

### [Versión 5.2] - 2025-12-29 15:10
**Resumen:** GIP Fase 3.1 - Implementación de vista de escritorio y punto de entrada para LPT.
**Origen:** Solicitud directa GIP.

#### Cambios Funcionales
- **Punto de Entrada `lpt.php`:** Creación del router público que gestiona la detección de dispositivos y carga el núcleo LPT.
- **Cabecera Dinámica:** Implementación de `includes/header_lista_posts_tag.php` que consume datos del núcleo para mostrar títulos, subtítulos e imágenes personalizadas por etiqueta.
- **Plantilla de Escritorio:** Implementación de `includes/lista_posts_tag_desktop.php` con estructura de dos columnas, incluyendo un sidebar con navegación de etiquetas generada desde la configuración.

#### Validación técnica
- La navegación entre etiquetas funciona correctamente mediante el parámetro `tag` en `lpt.php`.
- La interfaz visual respeta la configuración definida en `lista_posts_tag.ini`.
- La detección de dispositivos redirige (provisionalmente) a la vista de escritorio como fallback seguro.

---

### [Versión 5.1] - 2025-12-29 14:06
**Resumen:** GIP Fase 1 - Definición del modelo de configuración y carga de contenido inicial para LPT.
**Origen:** Solicitud directa GIP.

#### Cambios Funcionales
- Configuración completa de `includes/lista_posts_tag.ini` con modelo definitivo:
    - Definición de parámetros globales (lotes, mensajes, imagen por defecto).
    - Establecimiento de orden editable para 5 etiquetas clave (Serie de Comodín, Round Robin, Final, Serie del Caribe, Serie de las Américas).
    - Carga de configuración visual específica (Textos y rutas de imágenes WebP) para cada etiqueta.

#### Validación técnica
- El archivo INI contiene la estructura correcta y los datos requeridos.
- Se mantiene el aislamiento funcional del sistema (sin cambios en controladores ni vistas públicas).

---

### [Versión 5.0] - 2025-12-29 14:02
**Resumen:** GIP Fase 0 - Preparación del entorno para el sistema de listados por etiquetas (LPT).
**Origen:** Solicitud directa GIP.

#### Cambios Estructurales
- Creación de archivos base para el sistema LPT en el directorio `includes/`:
    - `includes/lista_posts_tag.ini`: Configuración inicial.
    - `includes/lista_posts_tag.php`: Controlador LPT básico.
    - `includes/lista_posts_tag_mobile.php`: Vista móvil (esqueleto).
    - `includes/lista_posts_tag_desktop.php`: Vista escritorio (esqueleto).

#### Validación técnica
- **Integridad:** No se modificaron archivos existentes del sistema.
- **Aislamiento:** Los nuevos archivos no están integrados en el flujo activo, garantizando un punto de retorno seguro.
- **Estabilidad:** El sitio en producción mantiene funcionalidad normal sin errores de PHP.

---

### [Versión 4.1] - 2025-12-16
**Resumen:** Implementación de gestión granular de contenidos y etiquetas.
**Origen:** `docs/AUDITORIA_TECNICA.md`

#### Cambios Funcionales
- Implementado panel de administración avanzada de etiquetas (`gestion/admin_tags_gestion.php`).
- Habilitada edición manual de tags/categorías con operadores `+` (añadir) y `-` (eliminar).
- Implementada lógica "no destructiva" para actualizaciones de categorías.

#### Cambios Estructurales
- Creado archivo de configuración de listas (`gestion/admin_tags_listas.php`).
- Actualización de backend para usar Regex en modificaciones directas de archivos PHP.

---

### [Versión 4.0] - 2025-12-16
**Resumen:** Mejora en el flujo de eliminación de contendo y auditoría de archivos.
**Origen:** `docs/AUDITORIA_TECNICA.md`

#### Correcciones y Mejoras
- **Refactorización `delete_post.php`:** Eliminada ejecución automática de shell. Se agregan botones para acciones manuales seguras (actualizar manifiesto, regenerar sitemap).
- **Mejora `generate_manifest.php`:** Soporte para parámetro `delete_slug` para eliminar entradas específicas sin reescanear todo.
- **Mejora `generate_sitemap.php`:** Detección de URLs eliminadas y notificación de bajas (404/410) a IndexNow API.

#### Mantenimiento
- Reorganización visual del Dashboard (`gestion/dashboard_gestion.php`).
- Ejecución de script de limpieza temporal (`fix_bravos_cleanup.php`) y posterior eliminación.

---

### [Versión 3.1] - 2025-12-16
**Resumen:** Auditoría técnica y reorganización de archivos físicos.
**Origen:** `docs/AUDITORIA_TECNICA.md`

#### Cambios Estructurales
- Movido `generate_manifest.php` de la raíz al directorio `/gestion/`.
- Renombrados archivos obsoletos/inactivos: `_about.php` y `_contact.php`.
- Detectados y documentados scripts de utilidad: `posts_manifest_control.php`, `update_tags_task.php`, `update_tags_task_2.php`.

---

### [Versión 3.0] - 2025-12-10 (Tarde)
**Resumen:** Centralización administrativa y nuevas características visuales.
**Origen:** `docs/AUDITORIA_TECNICA.md`

#### Cambios Estructurales
- **Creación directorio `/gestion/`:** Centralización de todas las herramientas administrativas.
- **Movimiento de archivos:** Se trasladaron `crear-post-admin.php`, `procesar-post.php`, `delete_post.php`, `generate_sitemap.php`, `admin_tags.php` y `list_tags.php` al nuevo directorio.
- **Actualización de rutas:** Ajuste de constantes y `include` para operar desde subdirectorio.

#### Cambios Funcionales
- **Dashboard Unificado:** Implementación de `gestion/dashboard_gestion.php`.
- **Kanban Board:** Implementación de `gestion/kanban_destacados.php` para gestión visual (Drag & Drop) de artículos en portada.
- **Bloque Patrocinado:** Implementación en `index_desktop.php` e `index_mobile.php` de lógica para destacar posts con tag "Artículo Patrocinado".

#### Limpieza
- Eliminación de `index_admin.php` (reemplazado por el nuevo Dashboard y Kanban).

---

### [Versión 2.0] - 2025-12-10 (Mañana)
**Resumen:** Documentación inicial consolidada y auditoría de contenido.
**Origen:** `docs/AUDITORIA_TECNICA.md` y `docs/ANALISIS_COMPLETO_PROYECTO.md`

#### Documentación
- Creación de `docs/ANALISIS_COMPLETO_PROYECTO.md`.
- Eliminación de documentación obsoleta dispersa.

#### SEO
- Integración de notificación automática a IndexNow en `generate_sitemap.php`.

#### Estado del Proyecto
- Validación de 106 artículos activos + 1 plantilla.
