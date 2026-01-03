# Auditoría Técnica del Proyecto Meridiano Béisbol Blog

> **Entorno del Proyecto:**
> - **Proyecto:** MBB_20251124_H
> - **Hosting:** Servidor Compartido en https://www.meridiano.com
> - **Entorno Local:** Workspace único. No hay PHP, GIT instalado u otro lenguaje.

**Fecha Auditoría**: 2026-01-02
**Propósito**: Mapa estructural verificable del estado actual del sistema.

---

## 1. Estructura Gráfica de Directorios y Archivos

```
/ (Raíz del Proyecto)
├── .htaccess                   # [ACTIVO] Configuración del servidor Apache
├── .litespeed_flag             # [ACTIVO] Flag de servidor LiteSpeed
├── config.php                  # [CRÍTICO] Configuración global (URLs, constantes)
├── footer.php                  # [ACTIVO] Pie de página común
├── google380f126c61185383.html # [HUÉRFANO] Verificación SEO
├── header_common.php           # [ACTIVO] Cabecera para posts/páginas
├── header_index.php            # [ACTIVO] Cabecera para Home
├── header_lista_post.php       # [ACTIVO] Cabecera para lista_posts.php
├── header_secciones.php        # [ACTIVO] Cabecera para secciones dinámicas
├── index.php                   # [CRÍTICO] Router principal (Desktop/Móvil)
├── index_desktop.php           # [ACTIVO] Home Desktop
├── index_mobile.php            # [ACTIVO] Home Móvil
├── lista_posts.php             # [ACTIVO] Buscador y listado general
├── menu.php                    # [ACTIVO] Menú de navegación
├── post.php                    # [ACTIVO] Plantilla base post
├── posts_manifest.php          # [CRÍTICO] "Base de datos" de posts
├── posts_manifest_control.php  # [ACTIVO] Control versiones manifest
├── process_tags.php            # [ACTIVO] Backend tags
├── README.md                   # [ACTIVO] Documentación de proyecto
├── robots.txt                  # [ACTIVO] SEO crawling rules
├── sitemap.xml                 # [ACTIVO] Sitemap generado
├── sitemap.old.xml             # [HUÉRFANO] Sitemap previo (respaldo)
├── e48a97f544db4ca0a331f4c830ccf202.txt # [HUÉRFANO] Key IndexNow
│
├── assets/                     # [RECURSOS] Imágenes y elementos multimedia
│   ├── favicon.ico             # [ACTIVO] Icono del sitio
│   ├── i-tags/                 # [ACTIVO] Sistema de iconos para tags
│   └── img/                    # [ACTIVO] Imágenes de contenido
│       ├── 2025-26-75por-ciento-temporada/ # [ACTIVO] Contenido específico
│       └── *.webp              # [ACTIVO] Múltiples imágenes en WebP
│
├── config/                     # [CONFIGURACIÓN] Configuraciones por sección
│   ├── secciones_botones_actualiza.php # [ACTIVO] Botones dinámicos
│   ├── secciones_botones_menu.php      # [ACTIVO] Menú de secciones
│   ├── sidebar_global.ini              # [ACTIVO] Configuración sidebar
│   └── secciones/              # [ACTIVO] Configuraciones individuales por sección
│       ├── ausencia-venezuela.ini
│       ├── la-final-2025-26.ini
│       ├── resumen-semanal.ini
│       ├── round-robin.ini
│       ├── serie-americas-2026.ini
│       ├── serie-comodin-2025-26.ini
│       └── serie-del-caribe-2026.ini
│
├── gestion/                    # [ADMINISTRACIÓN] Herramientas backend
│   ├── dashboard_gestion.php   # [ACTIVO] Panel principal
│   ├── crear-post-admin.php    # [ACTIVO] Formulario creación
│   ├── procesar-post.php       # [ACTIVO] Procesador creación
│   ├── delete_post.php         # [ACTIVO] Eliminador posts
│   ├── kanban_destacados.php   # [ACTIVO] Gestor visual portada
│   ├── generate_sitemap.php    # [ACTIVO] Generador sitemap + IndexNow
│   ├── generate_manifest.php   # [ACTIVO] Generador manifest (Batch)
│   ├── admin_tags_gestion.php  # [ACTIVO] Editor avanzado tags
│   ├── admin_tags_listas.php   # [ACTIVO] Listas controladas tags
│   ├── indexing_api_auth.php   # [ACTIVO] Autenticación API Indexing
│   ├── indexing_check_url.php  # [ACTIVO] Verificación URLs indexadas
│   └── meridiano-mbb-4ba1b54b57a9.json # [HUÉRFANO] Credencial de servicio
│
├── includes/                   # [LÓGICA DE NEGOCIO] Núcleo del sistema
│   ├── home_data_provider.php  # [CRÍTICO] Lógica de datos portada
│   ├── home_featured.json      # [ACTIVO] Estado destacados manuales
│   ├── lista_posts_tag_core.php # [ACTIVO] Núcleo sistema LPT
│   └── seccion_renderer.php    # [ACTIVO] Motor renderizado secciones
│
├── css/                        # [ESTILOS] Hojas de estilo
│   ├── styles.css              # [ACTIVO] Base (Bootstrap)
│   ├── header_custom.css       # [ACTIVO] Ajustes header
│   ├── home_desktop.css        # [ACTIVO] Home Desktop
│   ├── home_mobile.css         # [ACTIVO] Home Móvil
│   └── lista_posts.css         # [ACTIVO] Lista posts
│
├── js/                         # [SCRIPTS] JavaScript frontend
│   ├── scripts.js              # [ACTIVO] Funciones generales
│   └── home_tracking.js        # [ACTIVO] Analytics/Clarity
│
├── docs/                       # [DOCUMENTACIÓN] Documentación técnica
│   ├── BASE_COGNITIVA_ANALISIS_TECNICO_PROYECTO.md # [ACTIVO] Análisis técnico
│   ├── BITACORA_CAMBIOS_PROYECTO.md               # [ACTIVO] Historial cambios
│   ├── AUDITORIA_TECNICA.md                       # [ACTIVO] Este archivo
│   ├── PLAN_IMPLEMENTACION_LA_FINAL.md            # [ACTIVO] Plan cambio Round Robin → La Final
│   ├── Conocimientos+Referencias_Indexing API.md   # [ACTIVO] Ref. API Indexing
│   ├── integration_plan_otras_secciones.md         # [ACTIVO] Plan integración
│   └── estadistica/            # [ACTIVO] Documentación módulo estadísticas
│       ├── 01 scope.md
│       ├── 02_data_model.md
│       ├── 03_mapa_ui.md
│       ├── 04_integration_plan.md
│       └── Snipets.md
│
├── seccion/                    # [SECCIONES] Páginas de secciones dinámicas
│   ├── ausencia-venezuela.php  # [ACTIVO] Sección específica
│   ├── la-final-2025-26.php    # [ACTIVO] Sección específica
│   ├── resumen-semanal.php     # [ACTIVO] Sección específica
│   ├── round-robin.php         # [ACTIVO] Sección específica
│   ├── serie-americas-2026.php # [ACTIVO] Sección específica
│   ├── serie-comodin-2025-26.php # [ACTIVO] Sección específica
│   └── serie-del-caribe-2026.php # [ACTIVO] Sección específica
│
├── legado/                     # [ARCHIVOS HUÉRFANOS] Código heredado y desactivado
│   ├── _about.php              # [HUÉRFANO] Página "Acerca de" desactivada
│   ├── _contact.php            # [HUÉRFANO] Página de contacto desactivada
│   ├── category.php            # [HUÉRFANO] Sistema de categorías heredado
│   ├── lpt.php                 # [HUÉRFANO] Sistema LPT heredado
│   ├── lpt_load.php            # [HUÉRFANO] Carga AJAX LPT heredado
│   ├── no_son_tags.php         # [HUÉRFANO] Configuración tags heredada
│   ├── tag.php                 # [HUÉRFANO] Sistema de tags heredado
│   ├── update_tags_task.php    # [HUÉRFANO] Script de tags heredado
│   ├── update_tags_task_2.php  # [HUÉRFANO] Script de tags heredado
│   ├── gestion/                # [HUÉRFANO] Herramientas admin heredadas
│   │   ├── admin_tags.php      # [HUÉRFANO] Editor tags legacy
│   │   └── list_tags.php       # [HUÉRFANO] Listador tags legacy
│   ├── includes/               # [HUÉRFANO] Componentes LPT heredados
│   │   ├── header_lista_posts_tag.php
│   │   ├── lista_posts_tag.ini
│   │   ├── lista_posts_tag.php
│   │   ├── lista_posts_tag_desktop.php
│   │   ├── lista_posts_tag_mobile.php
│   │   └── lpt_test_validation.php
│   ├── docs estadistica/       # [HUÉRFANO] Documentación módulo estadísticas heredado
│   ├── scripts_indexing/       # [HUÉRFANO] Scripts de indexación heredados
│   └── pag_inicio_temp_regular/ # [RESPALDO] Archivos originales pre-modificación página inicio
│
└── post/                       # [CONTENIDO] Artículos del blog
    └── *.php                   # 150+ archivos de artículos
```

---

## 2. Inventario y Estado de Archivos

### Puntos de Entrada Frontend
| Archivo | Rol | Estado | Riesgo |
|---------|-----|--------|--------|
| `index.php` | Router principal. Carga versión móvil o desktop. | **Crítico** | Alto. Si falla, el sitio cae completo. |
| `lista_posts.php` | Página de búsqueda y archivo histórico. | Activo | Medio. |
| `seccion/*.php` | Páginas de secciones dinámicas (7 archivos). | Activo | Bajo (individual). |
| `post/*.php` | Entradas directas a artículos (SEO). | Activo | Bajo (individual). |

### Componentes Críticos del Sistema
| Archivo | Rol | Estado | Riesgo |
|---------|-----|--------|--------|
| `config.php` | Constantes globales (Rutas, URLs). | **Crítico** | Crítico. Afecta todo el sistema. |
| `posts_manifest.php` | Índice de contenidos (Array en memoria). | **Crítico** | Crítico. Error de sintaxis rompe el sitio (500). |
| `includes/home_data_provider.php` | Lógica de presentación de la portada. | **Crítico** | Alto. Controla qué se ve en Home. |
| `includes/seccion_renderer.php` | Motor de renderizado para secciones dinámicas. | **Crítico** | Alto. Controla todas las secciones. |

### Templates y Componentes de Presentación
| Archivo | Rol | Estado | Dependencias |
|---------|-----|--------|--------------|
| `header_common.php` | Cabecera para posts y páginas estándar. | Activo | config.php, menu.php |
| `header_index.php` | Cabecera específica para la portada. | Activo | config.php |
| `header_lista_post.php` | Cabecera para sistema de listados. | Activo | config.php |
| `header_secciones.php` | Cabecera para secciones dinámicas. | Activo | config.php |
| `footer.php` | Pie de página común. | Activo | Ninguna |
| `menu.php` | Menú de navegación principal. | Activo | config.php |

### Administración (Directorio `/gestion/`)
| Archivo | Rol | Estado | Nota |
|---------|-----|--------|------|
| `dashboard_gestion.php` | Panel principal de administración. | Activo | Punto de entrada administrativo. |
| `crear-post-admin.php` | Formulario de creación de posts. | Activo | Interface de entrada. |
| `procesar-post.php` | Crea archivos físicos y actualiza manifest. | Activo | Motor de publicación. Soporte multi-categoría. |
| `delete_post.php` | Eliminación segura de posts. | Activo | Actualiza manifest automáticamente. |
| `generate_manifest.php` | Regenera el índice `posts_manifest.php`. | Activo | Procesamiento por lotes. |
| `kanban_destacados.php` | UI para ordenar destacados de portada. | Activo | Escribe en `home_featured.json`. |
| `admin_tags_gestion.php` | Editor post-publicación de tags. | Activo | Edita archivos PHP con Regex. |
| `admin_tags_listas.php` | Gestión de listas controladas de tags. | Activo | Complementa admin_tags_gestion.php. |
| `generate_sitemap.php` | Generador de sitemap XML e IndexNow. | Activo | Integración con API de indexación. |
| `indexing_api_auth.php` | Autenticación para Google Indexing API. | Activo | Servicio de indexación automática. |
| `indexing_check_url.php` | Verificación de estado de indexación. | Activo | Monitoreo de URLs. |

---

## 3. Dependencias entre Archivos

### Dependencias Directas Frontend

#### Sistema de Portada (Home)
```
index.php
├── User-Agent Detection Logic (interno)
├── index_mobile.php (si móvil/tablet)
│   ├── header_index.php
│   │   └── config.php
│   ├── includes/home_data_provider.php
│   │   ├── posts_manifest.php
│   │   └── includes/home_featured.json
│   └── footer.php
└── index_desktop.php (si desktop)
    ├── header_index.php
    │   └── config.php
    ├── includes/home_data_provider.php
    │   ├── posts_manifest.php
    │   └── includes/home_featured.json
    └── footer.php
```

#### Sistema de Listados y Búsqueda
```
lista_posts.php
├── config.php
├── posts_manifest.php
├── header_lista_post.php
│   └── config.php
└── footer.php
```

#### Sistema de Secciones Dinámicas
```
seccion/*.php (7 archivos)
├── config.php
├── includes/seccion_renderer.php
│   ├── includes/home_data_provider.php
│   │   ├── posts_manifest.php
│   │   └── includes/home_featured.json
│   ├── header_secciones.php
│   │   └── config.php
│   └── footer.php
└── config/secciones/{nombre}.ini
```

#### Posts Individuales
```
post/*.php (150+ archivos)
├── config.php
├── header_common.php
│   ├── config.php
│   └── menu.php
└── footer.php
```

### Dependencias del Sistema de Administración

#### Creación y Gestión de Contenido
```
gestion/crear-post-admin.php → gestion/procesar-post.php
└── procesar-post.php ejecuta:
    ├── Crea archivo en post/{slug}.php
    ├── Actualiza posts_manifest.php
    └── Opcionalmente: Ejecuta generate_sitemap.php

gestion/delete_post.php
├── Elimina archivo post/{slug}.php
├── Actualiza posts_manifest.php
└── Opcionalmente: Actualiza sitemap.xml

gestion/generate_manifest.php
├── Escanea directorio post/
├── Parsea cabeceras PHP de cada post
├── Genera nuevo posts_manifest.php
└── Actualiza posts_manifest_control.php
```

#### Gestión de Portada y Destacados
```
gestion/kanban_destacados.php
├── Lee posts_manifest.php (posts disponibles)
├── Lee/Escribe includes/home_featured.json
└── Afecta: includes/home_data_provider.php (orden de destacados)
```

#### Sistema de Tags
```
gestion/admin_tags_gestion.php
├── Lee posts_manifest.php (lista posts)
├── Edita directamente archivos post/{slug}.php
├── Ejecuta process_tags.php (regeneración tags)
└── Opcionalmente: Regenera posts_manifest.php

process_tags.php
├── config.php
├── Escanea post/ para extraer tags
└── Actualiza índices internos de tags
```

#### Sistema de Indexación
```
gestion/generate_sitemap.php
├── posts_manifest.php (URLs de posts)
├── gestion/indexing_api_auth.php (autenticación)
├── Genera sitemap.xml
└── Ejecuta IndexNow API + Google Indexing API

gestion/indexing_check_url.php
└── gestion/indexing_api_auth.php
```

---

## 4. Mapa de Impacto por Archivo Crítico

### `config.php`
**Dependientes directos:** Prácticamente todos los archivos PHP del sistema (25+ archivos)
**Funcionalidad pública afectada si falla:**
- Completa. Todas las URLs, paths y constantes del sistema
- Ruptura total del sitio (Error 500)

**Funcionalidad administrativa afectada si falla:**
- Todas las herramientas de gestion/ quedan inoperativas
- No se pueden crear, editar o eliminar posts
- Panel administrativo inaccesible

### `posts_manifest.php`
**Dependientes directos:**
- `includes/home_data_provider.php` (datos de portada)
- `lista_posts.php` (listados y búsqueda)
- `category.php` y `tag.php` (filtros)
- `gestion/kanban_destacados.php` (gestión portada)
- `includes/seccion_renderer.php` (secciones dinámicas)

**Funcionalidad pública afectada si falla:**
- Portada sin contenido o Error 500
- Buscador y listados vacíos
- Categorías y tags sin posts
- Secciones dinámicas sin contenido

**Funcionalidad administrativa afectada si falla:**
- Kanban de destacados inoperativo
- Editores de tags pierden referencia a posts existentes

### `includes/home_data_provider.php`
**Dependientes directos:**
- `index_desktop.php` y `index_mobile.php`
- `includes/seccion_renderer.php` (para secciones que usan lógica de portada)

**Funcionalidad pública afectada si falla:**
- Portada (Home) queda sin contenido o Error 500
- Secciones dinámicas que usen contenido destacado fallan

**Funcionalidad administrativa afectada si falla:**
- Los cambios en `kanban_destacados.php` no se reflejan en frontend

### `includes/seccion_renderer.php`
**Dependientes directos:**
- Todos los archivos en `seccion/*.php` (7 archivos)

**Funcionalidad pública afectada si falla:**
- Todas las secciones dinámicas inoperativas (Error 500)
- URLs como `/seccion/resumen-semanal.php` devuelven error

**Funcionalidad administrativa afectada si falla:**
- No afecta directamente herramientas administrativas

### `gestion/procesar-post.php`
**Dependientes directos:**
- `gestion/crear-post-admin.php` (formulario de creación)

**Funcionalidad pública afectada si falla:**
- No se pueden crear nuevos posts (contenido estático)

**Funcionalidad administrativa afectada si falla:**
- Formulario de creación no funciona
- Flujo de publicación roto completamente

---

## 5. Archivos Huérfanos y Archivos Secundarios

### Criterios de Clasificación

**Archivos Huérfanos - Inactivos/Renombrados:**
- Archivos que fueron renombrados por razones de seguridad/mantenimiento
- No participan en el flujo funcional actual del sitio
- Se mantienen como respaldo histórico

**Archivos Huérfanos - Respaldo:**
- Versiones anteriores de archivos activos
- Mantenidos para rollback en caso de problemas
- No procesados por el sistema actual

**Archivos Huérfanos - Legacy/Secundarios:**
- Herramientas o funciones reemplazadas por versiones nuevas
- Se mantienen por compatibilidad o como fallback
- No forman parte del flujo principal actual

**Archivos Huérfanos - Verificación/Credenciales:**
- Archivos de verificación de servicios externos
- Claves de API o credenciales
- No participan en lógica de negocio del sitio

### Lista Completa de Archivos Huérfanos

**Nota Importante:** Todos los archivos listados a continuación han sido reubicados al directorio `legado/` para mantener la organización del proyecto sin eliminar código histórico.

#### Inactivos/Renombrados (ubicados en `/legado/`)
| Archivo | Estado | Ubicación Original | Justificación |
|---------|--------|-------------------|---------------|
| `_about.php` | Inactivo | Raíz → `/legado/` | Página "Acerca de" desactivada. Renombrada con `_` por seguridad. No referenciada en menús ni enlaces. Movida a legado. |
| `_contact.php` | Inactivo | Raíz → `/legado/` | Página de contacto desactivada. Renombrada con `_` por seguridad. No referenciada en menús ni enlaces. Movida a legado. |

#### Sistema Heredado de Categorías y Tags (ubicados en `/legado/`)
| Archivo | Estado | Ubicación Original | Justificación |
|---------|--------|-------------------|---------------|
| `category.php` | Legacy | Raíz → `/legado/` | Sistema de categorías heredado. No se detectaron referencias activas en el código. Movido a legado. |
| `tag.php` | Legacy | Raíz → `/legado/` | Sistema de tags heredado. No se detectaron referencias activas en el código. Movido a legado. |
| `no_son_tags.php` | Legacy | Raíz → `/legado/` | Configuración de exclusiones de tags heredada. No se detectaron usos activos. Movido a legado. |

#### Sistema LPT Heredado (ubicados en `/legado/`)
| Archivo | Estado | Ubicación Original | Justificación |
|---------|--------|-------------------|---------------|
| `lpt.php` | Legacy | Raíz → `/legado/` | Sistema de Lista de Posts por Tag heredado. Reemplazado por sistema de secciones. Movido a legado. |
| `lpt_load.php` | Legacy | Raíz → `/legado/` | Carga AJAX para LPT heredado. Dependiente de lpt.php. Movido a legado. |
| `includes/header_lista_posts_tag.php` | Legacy | `/includes/` → `/legado/includes/` | Cabecera específica para LPT heredado. Movido a legado. |
| `includes/lista_posts_tag.ini` | Legacy | `/includes/` → `/legado/includes/` | Configuración LPT heredada. Movido a legado. |
| `includes/lista_posts_tag.php` | Legacy | `/includes/` → `/legado/includes/` | Template wrapper LPT heredado. Movido a legado. |
| `includes/lista_posts_tag_desktop.php` | Legacy | `/includes/` → `/legado/includes/` | Vista desktop LPT heredada. Movido a legado. |
| `includes/lista_posts_tag_mobile.php` | Legacy | `/includes/` → `/legado/includes/` | Vista móvil LPT heredada. Movido a legado. |
| `includes/lpt_test_validation.php` | Legacy | `/includes/` → `/legado/includes/` | Tests del sistema LPT heredado. Movido a legado. |

#### Scripts de Mantenimiento Heredados (ubicados en `/legado/`)
| Archivo | Estado | Ubicación Original | Justificación |
|---------|--------|-------------------|---------------|
| `update_tags_task.php` | Legacy | Raíz → `/legado/` | Script de mantenimiento de tags heredado. No se detectaron referencias activas. Movido a legado. |
| `update_tags_task_2.php` | Legacy | Raíz → `/legado/` | Script auxiliar de tags heredado. No se detectaron referencias activas. Movido a legado. |

#### Herramientas Administrativas Heredadas (ubicados en `/legado/gestion/`)
| Archivo | Estado | Ubicación Original | Justificación |
|---------|--------|-------------------|---------------|
| `gestion/admin_tags.php` | Legacy | `/gestion/` → `/legado/gestion/` | Versión anterior del editor de tags. Reemplazado por `admin_tags_gestion.php`. Movido a legado. |
| `gestion/list_tags.php` | Legacy | `/gestion/` → `/legado/gestion/` | Herramienta simple de listado de tags. Reemplazado por `admin_tags_listas.php`. Movido a legado. |

#### Respaldos de Página Inicio (ubicados en `/legado/pag_inicio_temp_regular/`)
| Archivo | Estado | Ubicación Original | Justificación |
|---------|--------|-------------------|---------------|
| `index_desktop.php` | Respaldo | Raíz → `/legado/pag_inicio_temp_regular/` | Versión original de portada desktop antes de reordenamiento de secciones. |
| `index_mobile.php` | Respaldo | Raíz → `/legado/pag_inicio_temp_regular/` | Versión original de portada móvil antes de reordenamiento de secciones. |
| `home_data_provider.php` | Respaldo | `/includes/` → `/legado/pag_inicio_temp_regular/` | Versión original del proveedor de datos antes de agregar block_nueva. |
| `home_desktop.css` | Respaldo | `/css/` → `/legado/pag_inicio_temp_regular/` | Estilos originales de portada desktop. |
| `home_mobile.css` | Respaldo | `/css/` → `/legado/pag_inicio_temp_regular/` | Estilos originales de portada móvil. |

#### Respaldos (ubicados en raíz)
| Archivo | Estado | Ubicación | Justificación |
|---------|--------|-----------|---------------|
| `sitemap.old.xml` | Respaldo | `/` | Versión anterior del sitemap. Mantenido como backup antes de implementar generación automática. No procesado por crawlers. |

#### Verificación/Credenciales
| Archivo | Estado | Ubicación | Justificación |
|---------|--------|-----------|---------------|
| `google380f126c61185383.html` | Verificación | `/` | Archivo de verificación para Google Search Console. Requerido por Google pero no participa en lógica del sitio. |
| `e48a97f544db4ca0a331f4c830ccf202.txt` | Credencial | `/` | Clave para IndexNow API (Bing). Archivo estático de verificación. No procesado por el CMS. |
| `gestion/meridiano-mbb-4ba1b54b57a9.json` | Credencial | `/gestion/` | Credencial de servicio para Google Indexing API. Solo accedida por `indexing_api_auth.php`. |

#### Scripts de Logging/Debug (Huérfanos menores)
| Archivo | Estado | Ubicación | Justificación |
|---------|--------|-----------|---------------|
| `gestion/scripts/indexing_first_load.log` | Log | `/gestion/scripts/` | Archivo de log generado automáticamente. No forma parte del código fuente. |
| `gestion/scripts/indexing_first_load_2.php.html` | Debug | `/gestion/scripts/` | Parece un artifact de debug o output HTML. No referenciado en código. |

### Archivos NO Huérfanos (Aclaración)

Los siguientes archivos, aunque podrían parecer secundarios, **SÍ** forman parte del flujo principal:

- `README.md`: Documentación activa del proyecto
- `robots.txt`: Configuración SEO activa
- `.htaccess`, `.litespeed_flag`: Configuración de servidor activa
- `posts_manifest_control.php`: Control de versiones del manifest (funcionalidad activa)
- Todo el directorio `docs/`: Documentación técnica activa del proyecto
- Todo el directorio `config/`: Configuraciones activas del sistema de secciones

---

## 6. Detección de Inconsistencias y Riesgos

### Riesgos Críticos Identificados

1. **Doble fuente de verdad:**
   El contenido vive en `/post/*.php` pero el índice vive en `posts_manifest.php`. Si se borra un archivo vía FTP sin usar `delete_post.php` (o sin regenerar el manifest), habrá enlaces rotos (404) en el sitio hasta la próxima regeneración.

2. **Dependencia crítica de `home_data_provider.php`:**
   Toda la lógica de la portada (móvil y desktop) está centralizada aquí. Un error en este archivo afecta ambas versiones del sitio y las secciones dinámicas.

3. **Centralización de configuración:**
   `config.php` es un punto único de fallo. Su corrupción afecta TODO el sistema sin excepción.

### Archivos Legacy con Riesgo de Confusión - MITIGADO

**Acción Realizada:** Todos los archivos legacy han sido reubicados al directorio `/legado/` para evitar confusión y mantener el código organizado.

1. **Sistema de tags duplicado - RESUELTO:**
   - `admin_tags.php` (movido a `/legado/gestion/`)
   - `list_tags.php` (movido a `/legado/gestion/`)
   - **Estado:** Los archivos activos `admin_tags_gestion.php` y `admin_tags_listas.php` permanecen en `/gestion/`
   - **Riesgo eliminado:** No hay confusión posible, los archivos legacy están claramente separados

2. **Archivos inactivos accesibles - RESUELTO:**
   - `_about.php` y `_contact.php` movidos a `/legado/`
   - **Estado:** Ya no son accesibles desde la raíz pública del sitio
   - **Riesgo eliminado:** Contenido desactualizado ya no es visible públicamente

3. **Sistema LPT heredado - ARCHIVADO:**
   - Todos los archivos del sistema LPT heredado movidos a `/legado/` y `/legado/includes/`
   - **Estado:** El sistema LPT ha sido descontinuado
   - **Riesgo eliminado:** No hay código duplicado que pueda causar confusión

4. **Sistemas de categorías y tags heredados - ARCHIVADOS:**
   - `category.php`, `tag.php`, `no_son_tags.php` movidos a `/legado/`
   - **Estado:** Funcionalidad integrada en sistema de secciones
   - **Riesgo eliminado:** Código legacy claramente separado

### Sistema Activo - Sin Cambios Funcionales

**Sistema de Secciones Dinámicas:**
- Directorio `seccion/` con 7 archivos - **ACTIVO**
- Motor `includes/seccion_renderer.php` - **ACTIVO**
- Configuración en `config/secciones/` - **ACTIVO**
- **Estado:** Funcionando correctamente, sin cambios

---

## 7. Notas de Actualización

**Fecha Auditoría:** 2 de enero de 2026 (Actualización 2)

**Cambios Organizacionales Realizados:**

- **Creación del directorio `/legado/`:** Se ha establecido un área dedicada para código heredado, descontinuado o en desuso.

- **Reubicación masiva de archivos heredados:**
  - 9 archivos movidos desde raíz a `/legado/`
  - 2 herramientas administrativas movidas a `/legado/gestion/`
  - 6 componentes del sistema LPT movidos a `/legado/includes/`
  - Total: 17 archivos reubicados sin eliminación

- **Respaldo de archivos pre-modificación (3 ene 2026):**
  - 5 archivos copiados a `/legado/pag_inicio_temp_regular/` antes de modificar página de inicio
  - Archivos respaldados: `index_desktop.php`, `index_mobile.php`, `home_data_provider.php`, `home_desktop.css`, `home_mobile.css`
  - 1 archivo adicional copiado: `gestion/kanban_destacados.php`

- **Plan de implementación documentado (3 ene 2026):**
  - Creado `docs/PLAN_IMPLEMENTACION_LA_FINAL.md` para cambio de "Round Robin" a "La Final"
  - Plan completo con respaldos, verificaciones y procedimiento de rollback

- **Mejora en la organización del proyecto:**
  - Separación clara entre código activo y heredado
  - Eliminación de confusión sobre qué archivos están en uso
  - Mantenimiento del historial de código sin afectar la estructura activa

**Cambios Estructurales Identificados desde Auditoría Anterior (17 dic 2025):**

- **Sistema de Secciones Dinámicas:** Se documentó el directorio `seccion/` con 7 páginas específicas y su motor de renderizado `seccion_renderer.php`.

- **Ampliación de Herramientas Administrativas:** Se identificaron herramientas adicionales como `indexing_api_auth.php`, `indexing_check_url.php` y credenciales de servicios.

- **Configuraciones Modulares:** Se documentó el sistema de configuración por secciones en `config/secciones/` con archivos `.ini` individuales.

- **Documentación Expandida:** Se identificó documentación adicional incluyendo módulo de estadísticas y referencias de API.

- **Sistema LPT Descontinuado:** El sistema de "Lista de Posts por Tag" ha sido identificado como heredado y archivado en `/legacy/`.

**Estado del Sistema:**
- **Estabilidad:** El core del sistema (manifest, config, home_data_provider, seccion_renderer) se mantiene estable
- **Organización Mejorada:** La reubicación de archivos legacy mejora significativamente la claridad del proyecto
- **Mantenibilidad:** La separación clara entre componentes activos y heredados facilita el mantenimiento
- **Sin Pérdida de Historial:** Todo el código heredado se mantiene disponible en `/legado/` para consulta

**Próximas Acciones Recomendadas:**
1. ✅ **COMPLETADO:** Reubicación de archivos legacy al directorio `/legado/`
2. ✅ **COMPLETADO:** Creación de respaldo pre-modificación en `/legado/pag_inicio_temp_regular/`
3. Considerar protección adicional del directorio `/legado/` en `.htaccess` para evitar acceso público
4. Establecer monitoreo específico para el sistema de secciones dinámicas
5. Revisar periódicamente la coherencia entre `posts_manifest.php` y contenido real en `post/`
6. Documentar procedimientos de respaldo para configuraciones críticas en `config/`
