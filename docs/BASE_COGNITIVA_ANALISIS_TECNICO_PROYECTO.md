# Base Cognitiva y Análisis Técnico del Proyecto

**Fecha de Generación:** 2 de enero de 2026 (Actualización Mayor)
**Proyecto:** MBB_20251124_H (Meridiano Béisbol Blog)
**Nota:** Este documento es el resultado de un análisis técnico basado en la observación, lectura e inferencia de los archivos del proyecto. No debe ser modificado manualmente a menos que cambie la arquitectura base. Su propósito es dotar a cualquier agente de inteligencia artificial del contexto necesario para operar sobre el sistema sin suposiciones.

---

## 1. Propósito e Intención del Proyecto

**Problema que resuelve:**
El proyecto elimina la necesidad de una infraestructura compleja (base de datos SQL, frameworks pesados) para gestionar un blog de noticias de alto tráfico sobre béisbol venezolano (LVBP) en un entorno de hosting compartido con recursos limitados.

**Público Objetivo:**
Usuarios interesados en noticias rápidas, resúmenes diarios y estadísticas de la Liga Venezolana de Béisbol Profesional (LVBP).

**Objetivos Funcionales:**
- Publicación rápida de artículos sin latencia de base de datos.
- Clasificación flexible por categorías y etiquetas (multi-categoría soportada).
- Visualización diferenciada y optimizada para dispositivos móviles y de escritorio.
- Gestión editorial sencilla (creación, edición, destacado de artículos) sin requerir conocimientos técnicos.
- Secciones temáticas dinámicas para coberturas especiales (Round Robin, Serie del Caribe, etc.).

**Objetivos No Funcionales:**
- **Simplicidad:** Arquitectura de archivos planos (.php) para portabilidad total.
- **Rendimiento:** Carga inmediata al servir archivos estáticos/PHP puros con índice precargado en memoria.
- **Mantenibilidad:** Código estructurado sin dependencias ocultas ni compiladores.
- **SEO:** URLs amigables, sitemap automático, integración con IndexNow API.

**Lo que NO es:**
- No es un CMS genérico (está acoplado a la lógica de un blog de noticias deportivas).
- No tiene gestión de usuarios/roles (panel admin protegido por servidor web).
- No es una SPA ni usa frameworks JavaScript modernos (solo JS vanilla para interacciones específicas).

---

## 2. Visión General del Sistema - Descripción Operativa

### ¿Qué es este sistema?

El sitio funciona como un **sistema de publicación basado en archivos con renderizado PHP** que simula el comportamiento de un CMS tradicional sin requerir base de datos. Cada componente del sistema tiene un rol específico:

**CONTENIDO** = Archivos PHP individuales en `/post/`
- Cada artículo es un archivo `.php` con metadatos en variables PHP al inicio y HTML estático del cuerpo del artículo.
- Los archivos son **autónomos**: contienen toda la información necesaria para renderizarse.
- Ejemplo: `/post/tres-jonrones-heroes.php` contiene `$post_title`, `$post_date`, `$categories`, `$tags`, `$excerpt`, etc., seguidos del HTML del artículo.

**ÍNDICE** = `posts_manifest.php` (array PHP precargado en memoria)
- Es un archivo PHP que define un array gigante `$posts` con metadatos de TODOS los artículos.
- Se carga una vez por request y permanece en memoria durante el renderizado.
- **Función crítica:** Permite listar, filtrar y ordenar artículos sin leer archivos individuales del disco.
- **Doble fuente de verdad:** El contenido real está en `/post/`, pero el índice en `posts_manifest.php` debe estar sincronizado.

**CONFIGURACIÓN** = Archivos de configuración dispersos
- `config.php`: Constantes globales (URLs, rutas, configuración del sitio).
- `includes/home_featured.json`: Estado del tablero Kanban (orden manual de destacados en portada).
- `config/secciones/*.ini`: Configuraciones individuales de secciones temáticas.
- `config/sidebar_global.ini`: Enlaces del menú lateral.

### ¿Por qué existe la separación entre área pública y administrativa?

**Área Pública (Frontend):** Raíz del sitio
- **Operación:** Solo lectura. Los archivos PHP renderizan HTML consumiendo datos del manifest precargado.
- **Performance:** Al no escribir en disco ni modificar archivos, cada request es extremadamente rápido.
- **Resiliencia:** Si el área administrativa falla o está en mantenimiento, el sitio público sigue funcionando al 100%.

**Área Administrativa (Backend):** Directorio `/gestion/`
- **Operación:** Escritura y procesamiento pesado (crear archivos, regenerar manifest, actualizar sitemap).
- **Aislamiento:** Las tareas pesadas no afectan la velocidad del sitio público.
- **Seguridad:** El directorio `/gestion/` puede protegerse fácilmente mediante `.htaccess` sin afectar el sitio público.

**Implicaciones:**
- **Mantenimiento:** Los cambios editoriales (crear/editar posts) requieren regenerar el manifest, pero esto no afecta a usuarios que ya están navegando.
- **Rendimiento:** El sitio público puede servir miles de páginas por minuto sin degradación, ya que solo lee un índice en memoria.
- **Escalabilidad horizontal:** El sitio puede servirse desde múltiples servidores copiando los archivos (no hay estado compartido más allá de los archivos).

---

## 3. Modelado de Contenido y Metadatos del Artículo

### Formato de un Artículo en `/post/`

Cada archivo en el directorio `/post/` sigue esta estructura:

```php
<?php
// METADATOS (Variables PHP al inicio del archivo)
$post_title = "Título del Artículo";
$post_date = "27 de noviembre de 2025";
$post_author = "Nombre del Autor";
$post_author_link = "https://twitter.com/usuario";

// CATEGORÍAS (Sistema Multi-Categoría)
$categories = ['Magallanes', 'LVBP']; // Lista completa de categorías
$category = 'Magallanes'; // Categoría principal (compatibilidad)

// ETIQUETAS
$tags = ['jonrón', 'resumen-diario', 'venezuela'];

// IMÁGENES Y SEO
$featured_image = 'assets/img/imagen-destacada.webp';
$excerpt = "Resumen breve del artículo para redes sociales y previews.";

// Open Graph (Redes Sociales)
$og_type = 'article';
$og_title = $post_title;
$og_description = $excerpt;
$og_image = SITE_URL . $featured_image;
$og_url = SITE_URL . '/post/slug-del-articulo.php';

// Cargar plantilla común
include '../header_common.php';
?>

<!-- CONTENIDO HTML DEL ARTÍCULO -->
<article>
  <h1><?php echo $post_title; ?></h1>
  <!-- ... resto del HTML ... -->
</article>

<?php include '../footer.php'; ?>
```

### Metadatos Obligatorios

| Campo | Tipo | Descripción | Uso en el Sistema |
|-------|------|-------------|-------------------|
| `$post_title` | string | Título del artículo | Renderizado en listas, portada, SEO |
| `$post_date` | string | Fecha en formato español natural | Ordenamiento, filtrado por mes |
| `$categories` | array | Lista de categorías | Filtrado, clasificación multi-categoría |
| `$category` | string | Categoría principal | Compatibilidad con código legacy |
| `$tags` | array | Etiquetas del artículo | Filtrado, agrupación temática |
| `$featured_image` | string | Ruta de imagen destacada | Previews, redes sociales |
| `$excerpt` | string | Extracto breve | Tarjetas de preview, SEO |

### Reglas de Multi-Categoría

**Matriz de Categorías (`$categories`):**
- Es un array PHP que contiene TODAS las categorías a las que pertenece el artículo.
- Un artículo puede tener 1 o más categorías.
- Ejemplo: `$categories = ['Magallanes', 'LVBP', 'Resumen Diario'];`
- **Uso:** El manifest almacena este array completo, permitiendo filtrar por cualquiera de las categorías.

**Categoría Simple (`$category`):**
- Es una cadena que contiene la categoría **principal** o **primera** del artículo.
- **Propósito:** Compatibilidad con código legacy que espera un valor singular.
- **Regla:** Debe coincidir con el primer elemento de `$categories`.
- Ejemplo: Si `$categories = ['Magallanes', 'LVBP']`, entonces `$category = 'Magallanes'`.

**Implicación Técnica:**
- El sistema moderno usa `$categories` para todas las operaciones de filtrado.
- El campo `$category` se mantiene solo para retrocompatibilidad.
- Al crear un nuevo artículo, `procesar-post.php` genera automáticamente `$category` como la primera entrada de `$categories`.

---

## 4. Estructura General del Sitio

### Puntos de Entrada Públicos

| Archivo | Función | Parámetros | Renderiza |
|---------|---------|------------|-----------|
| `index.php` | Router principal | User-Agent | `index_desktop.php` o `index_mobile.php` |
| `lista_posts.php` | Buscador y archivo histórico | `category`, `tag`, `month`, `search` | Lista filtrable de posts |
| `post/*.php` | Artículos individuales | - | HTML del artículo completo |
| `seccion/*.php` | Secciones temáticas | - | Vista especializada con filtrado específico |

### Vistas y Plantillas

**Vistas de Portada:**
- `index_desktop.php`: Portada para desktop con diseño multi-columna
- `index_mobile.php`: Portada para móviles con diseño vertical optimizado
- **Diferencia clave:** No es responsive CSS, son archivos HTML completamente diferentes para máxima optimización.

**Cabeceras Especializadas:**
- `header_index.php`: Cabecera para portada (título del sitio, descripción general)
- `header_common.php`: Cabecera para posts y páginas internas (incluye menú completo)
- `header_lista_post.php`: Cabecera para sistema de listados
- `header_secciones.php`: Cabecera para secciones dinámicas (título y metadatos configurables)

**Componentes Comunes:**
- `menu.php`: Menú de navegación principal (cargado por cabeceras)
- `footer.php`: Pie de página común a todo el sitio
- `post.php`: Plantilla base heredada para artículos individuales (si se usa)

### Datos y Persistencia

**Índice Maestro:**
- `posts_manifest.php`: Array PHP con metadatos de todos los artículos
  - Estructura: `$posts = [['id' => '...', 'title' => '...', 'date' => '...', ...], ...]`
  - Se carga mediante `include` en cada request que necesita listar posts

**Configuración de Portada:**
- `includes/home_featured.json`: Estado del tablero Kanban
  - Array JSON con IDs de posts en orden manual
  - Ejemplo: `["abc123", "def456", "ghi789"]`
  - Modifica el orden de "Lo más reciente" en portada

**Control de Versiones:**
- `posts_manifest_control.php`: Metadatos sobre la última regeneración del manifest
  - Timestamp, número de posts procesados, errores, etc.

**Configuración de Secciones:**
- `config/secciones/*.ini`: Archivos INI con configuración por sección
  - Definen: título, subtítulo, imagen de fondo, categorías/tags a mostrar
  - Ejemplo: `round-robin.ini` configura la sección "Round Robin 2025-26"

### Administración (Módulo `/gestion/`)

**Panel de Control:**
- `dashboard_gestion.php`: Página principal con enlaces a todas las herramientas

**Gestión de Contenido:**
- `crear-post-admin.php`: Formulario de creación de artículos
- `procesar-post.php`: Motor que parsea el formato de entrada y genera archivos `.php`
- `delete_post.php`: Eliminador seguro de posts con actualización de manifest

**Gestión de Portada:**
- `kanban_destacados.php`: Interfaz drag & drop para ordenar posts destacados en portada
  - Escribe en `includes/home_featured.json`

**Gestión de Metadatos:**
- `admin_tags_gestion.php`: Editor avanzado para modificar categorías/tags post-publicación
- `admin_tags_listas.php`: Gestión de listas controladas de tags

**Regeneración de Índices:**
- `generate_manifest.php`: Indexador que escanea `/post/` y regenera `posts_manifest.php`
  - Procesamiento por lotes para evitar timeouts
  - Lee metadatos de cada archivo PHP mediante inclusión bufferizada

**SEO e Indexación:**
- `generate_sitemap.php`: Generador de `sitemap.xml`
  - Compara con `sitemap.old.xml` para detectar cambios
  - Notifica a IndexNow API (Bing) y Google Indexing API
- `indexing_api_auth.php`: Módulo de autenticación para Google Indexing API
- `indexing_check_url.php`: Verificador de estado de indexación de URLs

---

## 5. Arquitectura Técnica

**Patrón Adoptado:** **Flat-File CMS customizado**.
No se usa MVC tradicional. Se usa un patrón de **Page Controller** (cada archivo PHP es una ruta) apoyado por **Componentes de Inclusión** (headers, data providers).

**Decisiones Clave:**
- **Sin Base de Datos:** Se usa `posts_manifest.php` (array PHP nativo). *Implicación:* Lectura O(1) en memoria, pero escalabilidad limitada por RAM si crece a miles de posts.
- **Posts como Archivos PHP:** Cada artículo es un script ejecutable. *Implicación:* Máxima flexibilidad en presentación individual, pero requiere regenerar manifest para indexar cambios.
- **Frontend Dual:** HTML separado para móvil/desktop. *Implicación:* Mayor trabajo de mantenimiento UI, pero optimización extrema de carga y UX específica.

**Dependencias Externas:**
- **Bootstrap 5 (CDN):** Framework CSS.
- **SortableJS (CDN):** Para el Kanban.
- **Google Analytics / Clarity:** Scripts de terceros.
- **IndexNow:** API de notificación a buscadores.

---

## 5. Arquitectura Técnica

**Patrón Adoptado:** **Flat-File CMS customizado**.
No se usa MVC tradicional. Se usa un patrón de **Page Controller** (cada archivo PHP es una ruta) apoyado por **Componentes de Inclusión** (headers, data providers, renderers).

**Decisiones Clave:**
- **Sin Base de Datos:** Se usa `posts_manifest.php` (array PHP nativo). 
  - *Ventaja:* Lectura O(1) en memoria, velocidad extrema.
  - *Implicación:* Escalabilidad limitada por RAM del servidor si crece a miles de posts.
  - *Límite estimado:* ~2000-5000 posts según `memory_limit` del servidor.

- **Posts como Archivos PHP:** Cada artículo es un script ejecutable. 
  - *Ventaja:* Máxima flexibilidad en presentación individual, sin necesidad de templates complejos.
  - *Implicación:* Requiere regenerar manifest para indexar cambios en metadatos.

- **Frontend Dual:** HTML separado para móvil/desktop. 
  - *Ventaja:* Optimización extrema de carga y UX específica por dispositivo.
  - *Implicación:* Mayor trabajo de mantenimiento UI, cambios deben aplicarse en ambos archivos.

- **Secciones Dinámicas con Renderer:**
  - *Patrón:* Todas las secciones usan `includes/seccion_renderer.php` como motor común.
  - *Configuración:* Cada sección tiene su archivo `.ini` en `config/secciones/`.
  - *Ventaja:* Agregar secciones nuevas es trivial (crear `.php` + `.ini`).

**Dependencias Externas:**
- **Bootstrap 5 (CDN):** Framework CSS para estilos base.
- **SortableJS (CDN):** Librería drag & drop para el Kanban.
- **Google Analytics / Clarity:** Scripts de analítica (terceros).
- **IndexNow API:** Notificación a Bing sobre cambios de contenido.
- **Google Indexing API:** Solicitud de indexación rápida a Google.

---

## 6. Flujo de Datos - Modo Lectura (Usuario Final)

### Flujo 1: Acceso a Portada

**Entrada:** Usuario solicita `https://www.meridiano.com/`

1. **Ruteo (`index.php`):**
   - Lee `User-Agent` del request HTTP
   - Detecta si es móvil/tablet o desktop mediante regex
   - **Decisión:** Include `index_mobile.php` O `index_desktop.php`

2. **Carga de Vista:**
   - La vista seleccionada ejecuta `include 'includes/home_data_provider.php'`
   
3. **Proveedor de Datos (`home_data_provider.php`):**
   - **Carga Manifest:** `include __DIR__ . '/../posts_manifest.php';` → Array `$posts` disponible
   - **Carga Destacados:** Lee `includes/home_featured.json` → Array de IDs manuales
   - **Ejecuta `get_home_data()`:**
     - Ordena posts por fecha (desc)
     - Aplica filtros específicos:
       - **Bloque A:** Último post con tag "resumen-diario"
       - **Bloque B+C:** Posts destacados manuales + relleno cronológico
       - **Bloque D:** Posts con tag "resumen-semanal"
       - **Bloque E:** Posts con categoría "destacados"
     - **Deduplicación:** Marca posts ya usados para no repetirlos
     - **Inyección Patrocinado:** Busca post más reciente con tag "artículo-patrocinado"
   - **Salida:** Array estructurado:
     ```php
     $data = [
       'block_a' => [...], // Resumen diario
       'block_b_c' => [...], // Lo más reciente (10 posts)
       'block_d' => [...], // Resumen semanal
       'block_e' => [...], // Destacados
       'sponsored' => null|[...] // Artículo patrocinado
     ]
     ```

4. **Renderizado (Vista):**
   - Itera sobre `$data['block_a']`, `$data['block_b_c']`, etc.
   - Genera HTML con estructura específica por dispositivo
   - **Desktop:** Layout multi-columna, sidebar
   - **Móvil:** Layout vertical apilado

5. **Salida:** HTML completo enviado al navegador

**Archivos Involucrados:**
- `index.php` → `index_desktop.php` o `index_mobile.php` → `header_index.php` + `includes/home_data_provider.php` + `footer.php` → `posts_manifest.php` + `includes/home_featured.json`

---

### Flujo 2: Acceso a Artículo Individual

**Entrada:** Usuario solicita `https://www.meridiano.com/post/titulo-articulo.php`

1. **Ejecución Directa:**
   - El servidor web (Apache/LiteSpeed) localiza el archivo físico `/post/titulo-articulo.php`
   - Ejecuta el PHP del archivo

2. **Carga de Metadatos:**
   - Las variables PHP al inicio del archivo se inicializan: `$post_title`, `$post_date`, etc.

3. **Carga de Plantilla:**
   - `include '../header_common.php';`
     - Carga `config.php` (constantes globales)
     - Carga estilos CSS
     - Carga `menu.php` (navegación)
     - Usa variables del artículo para `<title>`, Open Graph, etc.

4. **Renderizado del Cuerpo:**
   - El HTML estático del artículo se procesa
   - PHP puede evaluar expresiones si están presentes (ej: `<?php echo $post_title; ?>`)

5. **Carga de Pie:**
   - `include '../footer.php';` → Scripts de analítica, cierre de HTML

6. **Salida:** HTML completo del artículo

**Archivos Involucrados:**
- `post/titulo-articulo.php` → `header_common.php` → `config.php` + `menu.php` → `footer.php`

---

### Flujo 3: Acceso a Listado (Búsqueda/Archivo)

**Entrada:** Usuario solicita `https://www.meridiano.com/lista_posts.php?category=Magallanes`

1. **Ejecución de Controlador (`lista_posts.php`):**
   - Lee parámetros GET: `category`, `tag`, `month`, `search`

2. **Carga de Datos:**
   - `include 'includes/home_data_provider.php';`
     - Carga `posts_manifest.php` → Array `$posts`

3. **Filtrado en Memoria:**
   - **Por categoría:** Itera `$posts`, verifica si `$post['categories']` contiene el valor solicitado
   - **Por tag:** Verifica si `$post['tags']` contiene el valor solicitado
   - **Por mes:** Parsea `$post['date']`, extrae mes/año, compara
   - **Por búsqueda (search):** 
     - Para cada post filtrado, hace `file_get_contents("post/{$slug}.php")`
     - Busca el término en el contenido del archivo
     - **Nota:** Esta es la operación más costosa del sistema

4. **Ordenamiento:**
   - Ordena posts filtrados por fecha descendente

5. **Renderizado:**
   - Itera sobre posts filtrados
   - Genera tarjetas de preview con título, fecha, extracto, imagen

6. **Salida:** HTML con lista de artículos

**Archivos Involucrados:**
- `lista_posts.php` → `header_lista_post.php` + `includes/home_data_provider.php` → `posts_manifest.php` + archivos en `/post/` (si hay búsqueda) → `footer.php`

---

### Flujo 4: Acceso a Sección Temática

**Entrada:** Usuario solicita `https://www.meridiano.com/seccion/round-robin.php`

1. **Carga de Configuración:**
   - El archivo `seccion/round-robin.php` ejecuta:
     - `require_once '../config.php';`
     - `require_once '../includes/seccion_renderer.php';`
     - `$seccion_slug = 'round-robin';`

2. **Motor de Renderizado (`seccion_renderer.php`):**
   - Lee `config/secciones/round-robin.ini`
   - **Extrae configuración:**
     - `titulo_principal`, `subtitulo`, `imagen_fondo`
     - `categorias` y/o `tags` para filtrar
   - **Carga datos:** `include 'home_data_provider.php';` → `posts_manifest.php`
   - **Filtra posts:** Aplica filtros de categoría/tag definidos en `.ini`
   - **Ordena:** Por fecha descendente

3. **Renderizado:**
   - Carga `header_secciones.php` (usa variables de configuración para título, meta, imagen)
   - Genera lista de posts filtrados
   - Agrega botones de navegación a otras secciones (desde `config/sidebar_global.ini`)
   - Carga `footer.php`

4. **Salida:** HTML de la sección con posts relevantes

**Archivos Involucrados:**
- `seccion/round-robin.php` → `config.php` + `includes/seccion_renderer.php` → `config/secciones/round-robin.ini` + `includes/home_data_provider.php` → `posts_manifest.php` + `header_secciones.php` + `footer.php`

---

## 7. Flujo de Datos - Modo Escritura (Administración)

### Flujo 1: Creación de Artículo (Ciclo de Vida Editorial Completo)

**Paso 1: Formulario de Entrada**
- **Archivo:** `gestion/crear-post-admin.php`
- **Acción:** El editor llena formulario con sintaxis especial de bloques `[SECCION]`
- **Ejemplo de entrada:**
  ```
  [DATOS_DOCUMENTO]
  titulo: Tres jonrones, 21 hits: La leyenda pendiente
  fecha: 27 de noviembre de 2025
  autor: Redacción Meridiano
  categorias: Magallanes, LVBP, Resumen Diario
  etiquetas: jonrón, venezuela, magallanes
  
  [CABECERA_VISUAL]
  imagen: assets/img/tres-jonrones.webp
  extracto: Magallanes logró una hazaña histórica...
  
  [CONTENIDO]
  <p>El contenido del artículo va aquí...</p>
  ```

**Paso 2: Procesamiento y Generación de Archivo**
- **Archivo:** `gestion/procesar-post.php`
- **Proceso:**
  1. **Parseo:** Usa regex para extraer secciones `[DATOS_DOCUMENTO]`, `[CABECERA_VISUAL]`, `[CONTENIDO]`
  2. **Validación:** Verifica campos obligatorios (título, fecha, categorías)
  3. **Generación de Metadatos:**
     - Genera slug: `titulo-del-post` (minúsculas, sin acentos, guiones)
     - Genera ID: `md5(substr($slug, 0, 20))` → Hash corto único
     - Parsea categorías: Si `"Magallanes, LVBP"` → `$categories = ['Magallanes', 'LVBP']`
     - Asigna `$category = $categories[0]` (primera categoría como principal)
  4. **Construcción del Archivo PHP:**
     - Plantilla de archivo con variables PHP al inicio
     - Contenido HTML del artículo
     - Includes de `header_common.php` y `footer.php`
  5. **Escritura:** `file_put_contents("../post/{$slug}.php", $php_code)`

**Paso 3: Actualización del Manifest**
- **Opción A (Automática):** `procesar-post.php` llama a `generate_manifest.php`
- **Opción B (Manual):** El editor ejecuta `generate_manifest.php` desde el dashboard

**Paso 4: Regeneración del Manifest**
- **Archivo:** `gestion/generate_manifest.php`
- **Proceso:**
  1. **Escaneo:** `scandir('../post/')` → Lista de archivos `.php`
  2. **Iteración por Lotes:** Procesa 25 archivos por ejecución (evitar timeouts)
  3. **Extracción de Metadatos:** Para cada archivo:
     ```php
     ob_start();
     include "../post/{$file}";
     ob_end_clean();
     // Variables $post_title, $post_date, etc. ahora disponibles
     ```
  4. **Construcción de Entrada:**
     ```php
     $posts[] = [
       'id' => $id,
       'title' => $post_title,
       'date' => $post_date,
       'slug' => $slug,
       'categories' => $categories,
       'tags' => $tags,
       'excerpt' => $excerpt,
       'image' => $featured_image,
       // ... más campos
     ];
     ```
  5. **Escritura:** Genera archivo PHP:
     ```php
     <?php
     $posts = [ /* array gigante */ ];
     ?>
     ```
  6. **Control:** Actualiza `posts_manifest_control.php` con timestamp, número de posts, errores

**Paso 5: Actualización del Sitemap**
- **Opción A (Manual):** El editor ejecuta `gestion/generate_sitemap.php`
- **Opción B (Automática):** Configurado como cron job o trigger post-publicación

**Paso 6: Generación del Sitemap**
- **Archivo:** `gestion/generate_sitemap.php`
- **Proceso:**
  1. **Backup:** `copy('sitemap.xml', 'sitemap.old.xml')`
  2. **Carga Manifest:** `include '../posts_manifest.php';`
  3. **Generación XML:**
     ```xml
     <?xml version="1.0"?>
     <urlset>
       <url>
         <loc>https://www.meridiano.com/post/slug.php</loc>
         <lastmod>2025-11-27</lastmod>
         <changefreq>monthly</changefreq>
         <priority>0.8</priority>
       </url>
       <!-- ... para cada post ... -->
     </urlset>
     ```
  4. **Detección de Cambios:**
     - Compara `sitemap.xml` (nuevo) con `sitemap.old.xml`
     - Identifica: URLs nuevas, URLs eliminadas, URLs modificadas
  5. **Escritura:** `file_put_contents('sitemap.xml', $xml)`

**Paso 7: Notificación a Motores de Búsqueda**
- **IndexNow API (Bing):**
  - Envía request POST a `https://api.indexnow.org/indexnow`
  - Cuerpo: `{"host": "meridiano.com", "key": "...", "urlList": ["url1", "url2"]}`
  - Key de verificación: Contenido de `e48a97f544db4ca0a331f4c830ccf202.txt`
  
- **Google Indexing API:**
  - Autentica mediante `gestion/indexing_api_auth.php`
  - Usa credenciales de `gestion/meridiano-mbb-4ba1b54b57a9.json`
  - Para cada URL nueva/modificada:
    - POST a `https://indexing.googleapis.com/v3/urlNotifications:publish`
    - Cuerpo: `{"url": "...", "type": "URL_UPDATED"}`

**Resultado Final:**
- ✅ Archivo de artículo creado en `/post/`
- ✅ Manifest actualizado con nuevo post
- ✅ Sitemap XML regenerado
- ✅ Motores de búsqueda notificados
- ✅ Post visible inmediatamente en portada y listados

---

### Flujo 2: Edición de Metadatos Post-Publicación

**Caso de Uso:** Corregir categorías o tags de un artículo ya publicado

**Paso 1: Selección de Post**
- **Archivo:** `gestion/admin_tags_gestion.php`
- **Acción:** El editor busca el post por título o slug

**Paso 2: Edición con Operadores**
- **Sintaxis Especial:**
  - `+categoria` → Agregar categoría
  - `-categoria` → Eliminar categoría
  - `+tag` → Agregar tag
  - `-tag` → Eliminar tag
  - `categoria1, categoria2` → Reemplazar lista completa
- **Ejemplo:** 
  - Input: `+LVBP, -Resumen Diario`
  - Resultado: Agrega "LVBP", elimina "Resumen Diario"

**Paso 3: Modificación del Archivo PHP**
- **Proceso:**
  1. Lee contenido del archivo: `file_get_contents("../post/{$slug}.php")`
  2. **Aplica Regex para Modificar Líneas:**
     - Busca: `$categories = [...]` o `$tags = [...]`
     - Reemplaza con nuevos valores
  3. Escribe archivo modificado: `file_put_contents(...)`

**Paso 4: Regeneración del Manifest**
- Ejecuta `generate_manifest.php` con parámetro `specific_slug={$slug}`
- Solo reindexes ese post específico (optimización)

**Resultado:**
- ✅ Archivo PHP modificado con nuevos metadatos
- ✅ Manifest actualizado
- ✅ Cambios visibles inmediatamente en listados

---

### Flujo 3: Gestión de Destacados en Portada (Kanban)

**Paso 1: Acceso al Kanban**
- **Archivo:** `gestion/kanban_destacados.php`
- **Carga datos:** `include '../includes/home_data_provider.php';` → Lee `posts_manifest.php`

**Paso 2: Visualización**
- Muestra los posts más recientes en tarjetas drag & drop (SortableJS)
- Lee orden actual de `includes/home_featured.json`

**Paso 3: Reordenamiento**
- El editor arrastra tarjetas para cambiar orden
- JavaScript captura nuevo orden: `['id1', 'id2', 'id3', ...]`

**Paso 4: Guardado**
- POST AJAX a `kanban_destacados.php?action=save`
- **Proceso:**
  ```php
  $featured_order = json_decode($_POST['order']);
  file_put_contents('../includes/home_featured.json', json_encode($featured_order));
  ```

**Resultado:**
- ✅ Archivo JSON actualizado
- ✅ Portada refleja nuevo orden en próxima carga (sin regenerar manifest)

---

### Flujo 4: Eliminación de Artículo

**Paso 1: Selección**
- **Archivo:** `gestion/delete_post.php`
- El editor ingresa el slug del post a eliminar

**Paso 2: Eliminación del Archivo**
- `unlink("../post/{$slug}.php")` → Elimina archivo físico

**Paso 3: Actualización del Manifest**
- **Opción A (Manual):** Regenerar manifest completo
- **Opción B (Optimizada):** 
  - Lee manifest actual
  - Filtra out el post eliminado
  - Reescribe manifest

**Paso 4: Actualización del Sitemap**
- Ejecuta `generate_sitemap.php`
- Detecta URL faltante → Notifica como `URL_DELETED` (status 404/410) a motores

**Resultado:**
- ✅ Archivo eliminado
- ✅ Manifest actualizado (sin entrada del post)
- ✅ Sitemap actualizado (sin URL)
- ✅ Motores notificados de eliminación

---

## 8. Dependencias y Puntos de Fallo con Impacto Funcional

---

## 8. Dependencias y Puntos de Fallo con Impacto Funcional

### Mapa de Dependencias Críticas

```
config.php (RAÍZ DEL SISTEMA)
├── Incluido por TODOS los headers
├── Define: SITE_URL, paths, constantes globales
└── Si falla: TODO EL SITIO CAE (Error 500)

posts_manifest.php (ÍNDICE MAESTRO)
├── Incluido por: home_data_provider.php, lista_posts.php, seccion_renderer.php
├── Contiene: Array $posts con metadatos de todos los artículos
└── Si falla: Portada sin contenido, listados vacíos, secciones vacías

includes/home_data_provider.php (CEREBRO DE DATOS)
├── Incluido por: index_desktop.php, index_mobile.php, lista_posts.php, seccion_renderer.php
├── Funciones: get_home_data(), filtrado, ordenamiento
├── Depende de: posts_manifest.php, home_featured.json
└── Si falla: Portada rota, secciones sin datos

includes/seccion_renderer.php (MOTOR DE SECCIONES)
├── Incluido por: seccion/*.php (7 archivos)
├── Funciones: renderizado común de secciones temáticas
├── Depende de: home_data_provider.php, config/secciones/*.ini
└── Si falla: Todas las secciones inoperativas

header_common.php → menu.php → config.php
header_index.php → config.php
header_lista_post.php → config.php
header_secciones.php → config.php
footer.php (sin dependencias críticas)

gestion/procesar-post.php
├── Escribe en: /post/*.php
├── Puede llamar a: generate_manifest.php
└── Si falla: No se pueden crear posts (flujo editorial roto)

gestion/generate_manifest.php
├── Lee: /post/*.php (todos los archivos)
├── Escribe: posts_manifest.php, posts_manifest_control.php
└── Si falla: Manifest desactualizado, posts nuevos no aparecen

gestion/generate_sitemap.php
├── Lee: posts_manifest.php
├── Escribe: sitemap.xml, sitemap.old.xml
├── Llama: IndexNow API, Google Indexing API
└── Si falla: SEO degradado, motores no notificados
```

### Puntos de Fallo - Análisis por Archivo Crítico

#### `config.php`

**Dependientes Directos:** 25+ archivos (prácticamente todo el sistema)

**Impacto si Falla:**
- **Sitio Público:** Error 500 en TODAS las páginas. El sitio cae completamente.
- **Razón:** Define constantes globales como `SITE_URL`, sin las cuales los includes y enlaces fallan.
- **Tipo de Fallo:** Parse error (sintaxis PHP incorrecta) o fatal error (constante ya definida).

**Impacto Editorial:**
- **Admin:** Todas las herramientas de `/gestion/` inoperativas.
- **Flujo de Trabajo:** Bloqueado completamente hasta reparación.

**Mitigación:**
- Backup automático antes de cualquier edición.
- Validación de sintaxis PHP antes de sobrescribir.
- Nunca editar directamente en producción sin pruebas locales.

---

#### `posts_manifest.php`

**Dependientes Directos:**
- `includes/home_data_provider.php`
- `lista_posts.php`
- `includes/seccion_renderer.php`
- `gestion/kanban_destacados.php`

**Impacto si Falla:**
- **Sitio Público:**
  - **Portada:** Sin contenido o Error 500 (dependiendo del tipo de fallo)
  - **Listados:** Página vacía o error
  - **Secciones:** Sin posts mostrados
  - **Posts Individuales:** Siguen funcionando (acceso directo a archivos)
- **Tipo de Fallo Común:** 
  - Sintaxis PHP incorrecta en array (coma faltante, comilla sin cerrar)
  - Comillas escapadas incorrectamente en títulos
  - Array corrupto por interrupción durante escritura

**Impacto Editorial:**
- **Kanban:** No puede cargar lista de posts para reordenar.
- **Vista Previa:** Los editores no pueden verificar cómo se ve el contenido.

**Mitigación:**
- `generate_manifest.php` hace validación de sintaxis antes de sobrescribir.
- Backup automático: `posts_manifest.php.backup` antes de cada regeneración.
- Si falla regeneración, usar `posts_manifest_control.php` para diagnosticar.

---

#### `includes/home_data_provider.php`

**Dependientes Directos:**
- `index_desktop.php`
- `index_mobile.php`
- `lista_posts.php`
- `includes/seccion_renderer.php`
- `gestion/kanban_destacados.php`

**Impacto si Falla:**
- **Sitio Público:**
  - **Portada:** Error 500 o página en blanco.
  - **Secciones:** Error al intentar filtrar posts.
  - **Listados:** Potencialmente funcional si falla solo `get_home_data()`, no si falla el include.
- **Tipo de Fallo:** 
  - Error de lógica en funciones de filtrado.
  - Función `get_home_data()` indefinida o con parámetros incorrectos.

**Impacto Editorial:**
- Los cambios en Kanban no se reflejan en portada (si falla lógica de destacados).

**Mitigación:**
- Este archivo debe tener tests manuales.
- Cambios requieren verificación en portada desktop Y móvil.

---

#### `includes/seccion_renderer.php`

**Dependientes Directos:**
- `seccion/ausencia-venezuela.php`
- `seccion/la-final-2025-26.php`
- `seccion/resumen-semanal.php`
- `seccion/round-robin.php`
- `seccion/serie-americas-2026.php`
- `seccion/serie-comodin-2025-26.php`
- `seccion/serie-del-caribe-2026.php`

**Impacto si Falla:**
- **Sitio Público:**
  - **Todas las Secciones:** Error 500 o renderizado roto.
  - URLs como `/seccion/round-robin.php` devuelven error.
- **Tipo de Fallo:**
  - Error al leer archivos `.ini`
  - Función de renderizado con bug
  - Path incorrecto a archivos de configuración

**Impacto Editorial:**
- No afecta directamente la creación de contenido, pero las secciones temáticas no se muestran.

**Mitigación:**
- Verificar TODAS las secciones después de modificar renderer.
- Configuraciones `.ini` deben validarse antes de deploy.

---

#### `gestion/procesar-post.php`

**Dependientes Directos:**
- `gestion/crear-post-admin.php` (formulario lo llama)

**Impacto si Falla:**
- **Sitio Público:** Ninguno (área administrativa aislada).
- **Flujo Editorial:**
  - **No se pueden crear posts nuevos.**
  - Formulario muestra error o genera archivos corruptos.
- **Tipo de Fallo:**
  - Regex de parseo falla con formato de entrada inesperado.
  - Error al escribir archivo en `/post/` (permisos, disco lleno).
  - Generación de slug o ID duplicado.

**Impacto Crítico para Operación:**
- El flujo de publicación está **completamente roto**.
- Alternativa de emergencia: Crear archivos PHP manualmente vía FTP (tedioso, propenso a errores).

**Mitigación:**
- Formulario debe validar formato antes de enviar.
- Hacer pruebas con diferentes formatos de entrada (títulos con comillas, acentos, etc.).
- Tener backup de `procesar-post.php` funcional.

---

#### `gestion/generate_manifest.php`

**Dependientes Directos:**
- Llamado manualmente o por `procesar-post.php`
- Escribe `posts_manifest.php` (que afecta todo el sitio)

**Impacto si Falla:**
- **Sitio Público:** 
  - **No inmediato** (manifest existente sigue sirviendo).
  - **A mediano plazo:** Posts nuevos no aparecen, cambios en metadatos no se reflejan.
- **Tipo de Fallo:**
  - Timeout por procesar demasiados archivos a la vez.
  - Error al parsear un archivo corrupto en `/post/`.
  - Disco lleno al escribir manifest nuevo.

**Impacto Editorial:**
- Posts creados no aparecen en portada ni listados.
- Ediciones de metadatos (categorías, tags) no surten efecto.

**Mitigación:**
- Procesamiento por lotes (25 archivos por ejecución).
- `posts_manifest_control.php` registra progreso para reanudar.
- Si falla, revisar log de errores PHP del servidor.

---

### Cadenas de Dependencia con Mayor Riesgo

**Cadena 1: Portada Completa**
```
config.php → index.php → index_desktop.php → header_index.php → 
includes/home_data_provider.php → posts_manifest.php + home_featured.json → 
footer.php
```
**Riesgo:** Fallo en cualquier punto tumba la portada. 7 archivos en cadena.

**Cadena 2: Creación de Post con Indexación**
```
crear-post-admin.php → procesar-post.php → /post/nuevo.php + 
generate_manifest.php → posts_manifest.php + 
generate_sitemap.php → sitemap.xml + IndexNow API
```
**Riesgo:** Fallo en medio del proceso deja sistema en estado inconsistente (post creado pero no indexado).

**Cadena 3: Sección Temática**
```
seccion/round-robin.php → config.php + seccion_renderer.php → 
config/secciones/round-robin.ini + home_data_provider.php → 
posts_manifest.php + header_secciones.php + footer.php
```
**Riesgo:** Dependencia de archivo `.ini` externo, si falta o está mal formado, la sección falla.

---

## 9. Lógica de Portada y Construcción de Bloques (Detallada)

### Arquitectura de Bloques

La portada se compone de **5 bloques** + **1 inyección** que se construyen dinámicamente:

```
┌─────────────────────────────────────────┐
│ BLOQUE A: "Ayer"                        │ ← Resumen Diario (último)
├─────────────────────────────────────────┤
│ BLOQUE B+C: "Lo más reciente"           │ ← 10 posts destacados
│ ┌───────────────────────────────────┐   │
│ │ [Inyección Patrocinado]           │   │ ← Entre posts 3 y 4
│ └───────────────────────────────────┘   │
├─────────────────────────────────────────┤
│ BLOQUE D: "Resumen Semanal"             │ ← Posts con tag específico
├─────────────────────────────────────────┤
│ BLOQUE E: "Destacados"                  │ ← Posts con categoría "Destacados"
└─────────────────────────────────────────┘
```

### Algoritmo de Construcción (función `get_home_data()`)

**Entrada:**
- `$posts`: Array completo del manifest (todos los artículos del sitio)
- `$featured_json`: Array de IDs desde `home_featured.json` (orden manual del Kanban)

**Proceso:**

**Paso 1: Ordenamiento Base**
```php
// Ordenar todos los posts por fecha descendente
usort($posts, function($a, $b) {
    return strtotime(parse_spanish_date($b['date'])) - 
           strtotime(parse_spanish_date($a['date']));
});
```

**Paso 2: Construcción de Bloque A ("Ayer")**
```php
// Buscar el último post con tag "resumen-diario"
$block_a = [];
foreach ($posts as $post) {
    if (in_array('resumen-diario', $post['tags'])) {
        $block_a[] = $post;
        $used_ids[] = $post['id']; // Marcar como usado
        break; // Solo uno
    }
}
```
**Reglas:**
- Solo **1 post** más reciente con tag `resumen-diario`
- Si no hay ninguno, bloque queda vacío
- Post usado se marca para no repetir en otros bloques

**Paso 3: Construcción de Bloque B+C ("Lo más reciente")**
```php
$block_b_c = [];
$target_count = 10;

// Paso 3.1: Agregar destacados manuales (del Kanban)
if (!empty($featured_json)) {
    foreach ($featured_json as $featured_id) {
        if (count($block_b_c) >= $target_count) break;
        if (in_array($featured_id, $used_ids)) continue; // Ya usado
        
        $post = find_post_by_id($posts, $featured_id);
        if ($post) {
            $block_b_c[] = $post;
            $used_ids[] = $featured_id;
        }
    }
}

// Paso 3.2: Rellenar con posts cronológicos (si faltan)
foreach ($posts as $post) {
    if (count($block_b_c) >= $target_count) break;
    if (in_array($post['id'], $used_ids)) continue; // Ya usado
    
    $block_b_c[] = $post;
    $used_ids[] = $post['id'];
}
```
**Reglas:**
- Objetivo: **10 posts** en total
- **Prioridad 1:** Posts en orden manual desde Kanban (`home_featured.json`)
- **Prioridad 2:** Relleno cronológico de posts más recientes no usados
- **Deduplicación:** No incluir posts ya usados en Bloque A

**Paso 4: Inyección de Artículo Patrocinado**
```php
$sponsored = null;
foreach ($posts as $post) {
    if (in_array('artículo-patrocinado', $post['tags'])) {
        if (!in_array($post['id'], $used_ids)) {
            $sponsored = $post;
            $used_ids[] = $post['id'];
            break; // Solo el más reciente
        }
    }
}
```
**Reglas:**
- Solo **1 post** más reciente con tag `artículo-patrocinado`
- No debe estar ya en otros bloques
- Se renderiza visualmente entre posts 3 y 4 de Bloque B+C

**Paso 5: Construcción de Bloque D ("Resumen Semanal")**
```php
$block_d = [];
$target_d = 5;
foreach ($posts as $post) {
    if (count($block_d) >= $target_d) break;
    if (in_array($post['id'], $used_ids)) continue; // Ya usado
    if (in_array('resumen-semanal', $post['tags'])) {
        $block_d[] = $post;
        $used_ids[] = $post['id'];
    }
}
```
**Reglas:**
- Hasta **5 posts** con tag `resumen-semanal`
- Ordenados por fecha descendente
- No repetir posts de bloques anteriores

**Paso 6: Construcción de Bloque E ("Destacados")**
```php
$block_e = [];
$target_e = 5;
foreach ($posts as $post) {
    if (count($block_e) >= $target_e) break;
    if (in_array($post['id'], $used_ids)) continue; // Ya usado
    if (in_array('Destacados', $post['categories'])) {
        $block_e[] = $post;
        $used_ids[] = $post['id'];
    }
}
```
**Reglas:**
- Hasta **5 posts** con categoría `Destacados`
- Ordenados por fecha descendente
- No repetir posts de bloques anteriores

**Salida:**
```php
return [
    'block_a' => $block_a,        // Array con 0-1 posts
    'block_b_c' => $block_b_c,    // Array con 10 posts
    'sponsored' => $sponsored,     // Post único o null
    'block_d' => $block_d,        // Array con 0-5 posts
    'block_e' => $block_e         // Array con 0-5 posts
];
```

### Importancia de la Deduplicación

**Problema Sin Deduplicación:**
Un mismo post podría aparecer en Bloque A, Bloque B+C y Bloque D simultáneamente si cumple múltiples condiciones (ej: tiene tag `resumen-diario` Y `resumen-semanal`).

**Solución - Array `$used_ids`:**
- Cada vez que un post se agrega a un bloque, su ID se añade a `$used_ids`.
- Antes de agregar un post a cualquier bloque, se verifica: `if (in_array($post['id'], $used_ids)) continue;`
- Esto garantiza **integridad editorial**: cada post aparece máximo una vez en portada.

**Orden de Prioridad (qué bloque "gana" un post):**
1. Bloque A (Resumen Diario)
2. Bloque B+C (Lo más reciente / Destacados manuales)
3. Artículo Patrocinado
4. Bloque D (Resumen Semanal)
5. Bloque E (Destacados)

---

## 10. Restricciones Reales del Entorno y Consecuencias Técnicas

### Limitaciones del Hosting Compartido

**1. Límites de Recursos PHP**
- **`memory_limit`**: Típicamente 128MB-256MB
  - **Consecuencia:** El manifest (`posts_manifest.php`) no puede crecer indefinidamente.
  - **Límite Estimado:** ~2000-5000 posts antes de problemas de memoria.
  - **Síntoma:** Warnings de memoria agotada, o errores 500 en cargas de portada.

- **`max_execution_time`**: 30-60 segundos
  - **Consecuencia:** `generate_manifest.php` no puede procesar todos los posts de una vez.
  - **Solución:** Procesamiento por lotes (25 archivos por ejecución).
  - **Implementación:** Variable de sesión o parámetro GET para reanudar progreso.

- **`post_max_size` / `upload_max_filesize`**: 8MB-32MB
  - **Consecuencia:** Imágenes muy pesadas pueden fallar al subir.
  - **Solución:** Validación en cliente, compresión previa, uso de WebP.

**2. Acceso a Filesystem Lento**
- **No hay caché de opcodes** (o limitado): Cada request incluye y parsea archivos PHP desde cero.
  - **Consecuencia:** El include de `posts_manifest.php` tiene overhead de I/O + parsing.
  - **Optimización:** Mantener manifest lo más pequeño posible (solo campos necesarios).

- **Búsqueda Full-Text en `lista_posts.php`**: Hace `file_get_contents()` sobre múltiples archivos.
  - **Consecuencia:** Búsquedas son lentas si hay cientos de posts.
  - **Límite Práctico:** Con 500+ posts, búsqueda puede tomar 2-5 segundos.
  - **Mitigación:** No hay solución sin base de datos o motor de búsqueda externo (Algolia, Elastic).

**3. Sin Acceso a CLI ni Cron Jobs Configurables**
- **Consecuencia:** No se pueden programar tareas automáticas (ej: regenerar manifest cada hora).
- **Workaround:** Ejecutar scripts manualmente desde navegador, o usar servicios externos (ej: cron web gratuito que golpea URL).

**4. Sin Base de Datos**
- **Decisión Arquitectónica:** Usar manifest en memoria en lugar de MySQL/PostgreSQL.
- **Ventaja:** Velocidad extrema de lectura (array en memoria es O(1)).
- **Desventaja:** Escalabilidad limitada, sin queries complejos (JOINs, aggregations).
- **Consecuencia de Escalar:** Si el sitio crece mucho (>5000 posts), considerar migrar a base de datos sería necesario, pero eso requiere rediseño completo.

**5. Concurrencia de Escritura Sin Locks**
- **Problema:** Si dos editores ejecutan `generate_manifest.php` simultáneamente, pueden sobrescribirse.
- **Probabilidad:** Baja (admin de un solo editor).
- **Mitigación Propuesta:** File lock con `flock()` en escritura de `posts_manifest.php` (no implementado actualmente).

### Consecuencias Técnicas de estas Restricciones

**Por qué existe el Manifest en Memoria:**
- **Sin BD:** Necesitamos estructura de datos para listar/filtrar posts.
- **Sin caché externo (Redis, Memcached):** No hay donde cachear fuera de PHP.
- **Solución:** Cargar todo en memoria una vez por request → Rápido para n < 2000.

**Por qué Regeneración de Manifest es por Lotes:**
- **Timeout:** Procesar 500+ archivos PHP toma >30 segundos.
- **Solución:** Dividir trabajo en chunks de 25, guardar progreso, reanudar con siguiente request.

**Por qué No hay Base de Datos:**
- **Hosting compartido:** Bases de datos MySQL tienen conexiones limitadas (10-20 concurrentes).
- **Costo de Latencia:** Cada query añade 10-50ms. Con manifest en memoria, es <1ms.
- **Simplicidad:** Sin BD, el proyecto es portable (copiar carpeta = clonar sitio).

**Por qué Búsqueda es Lenta:**
- **Sin índice Full-Text:** Cada búsqueda lee archivos uno por uno.
- **Trade-off:** Se sacrifica performance de búsqueda por simplicidad de arquitectura.
- **Uso Real:** La mayoría de usuarios llega por SEO (post directo), no por búsqueda interna.

---

## 11. Qué Cambiar y Qué NO Cambiar - Reglas de Mantenimiento

### Decisiones Arquitectónicas Cerradas (NO MODIFICAR sin análisis profundo)

**1. Integridad del Manifest como Requisito Estructural**
- ✅ **Regla:** `posts_manifest.php` debe estar SIEMPRE sincronizado con contenido real en `/post/`.
- ⛔ **Prohibido:** Editar `posts_manifest.php` manualmente (siempre regenerar con `generate_manifest.php`).
- ⛔ **Prohibido:** Eliminar archivos de `/post/` sin regenerar manifest (causará 404s).
- **Razón:** El manifest es la única fuente de verdad para listados. Si está corrupto o desincronizado, el sitio muestra contenido inexistente o oculta contenido real.

**2. Dualidad Desktop/Móvil sin Merge**
- ✅ **Regla:** Mantener `index_desktop.php` e `index_mobile.php` como archivos separados.
- ⛔ **Prohibido:** Intentar unificar en un solo archivo con media queries CSS.
- **Razón:** 
  - Optimización extrema: cada versión carga solo HTML necesario (no hay CSS/JS innecesario).
  - UX específica: móvil y desktop tienen flujos de navegación diferentes.
  - Performance: Mobile-first no significa mobile-only, usuarios desktop merecen experiencia optimizada.

**3. Separación entre Lectura Pública y Escritura Administrativa**
- ✅ **Regla:** Todo código de escritura (crear, editar, eliminar) debe estar en `/gestion/`.
- ⛔ **Prohibido:** Agregar funcionalidad de escritura en archivos de raíz (área pública).
- **Razón:**
  - Seguridad: `/gestion/` se protege con `.htaccess`, área pública no.
  - Performance: Código de escritura es pesado, no debe ejecutarse en requests públicos.
  - Resiliencia: Si admin falla, sitio público sigue funcionando.

**4. Manifest como Array PHP Nativo (no JSON, no XML, no Serializado)**
- ✅ **Regla:** `posts_manifest.php` debe ser código PHP válido con array `$posts`.
- ⛔ **Prohibido:** Cambiar a JSON o cualquier otro formato.
- **Razón:**
  - Velocidad: `include 'manifest.php'` es más rápido que `json_decode(file_get_contents(...))`.
  - Caché de opcodes: PHP puede cachear el bytecode compilado del array.
  - Debugging: Un array PHP es más fácil de inspeccionar que JSON serializado.

**5. Posts como Archivos PHP Autónomos**
- ✅ **Regla:** Cada post es un archivo `.php` con metadatos + HTML.
- ⛔ **Prohibido:** Usar sistema de templates compilados (Twig, Blade) o Markdown con procesamiento.
- **Razón:**
  - Simplicidad: Un post se puede editar directamente en FTP si es necesario.
  - Portabilidad: No requiere procesador externo.
  - SEO: Cada post tiene su propia URL física, no requiere routing complejo.

### Zonas Sensibles - Cambiar con Extrema Precaución

**1. `home_data_provider.php` - Cerebro de la Portada**
- **Por qué es sensible:** Cualquier cambio afecta portada desktop Y móvil.
- **Tipos de cambio peligrosos:**
  - Modificar lógica de filtrado (puede ocultar posts inadvertidamente).
  - Cambiar orden de prioridad de bloques (cambia experiencia editorial).
  - Agregar/eliminar deduplicación (puede causar posts repetidos).
- **Protocolo antes de cambiar:**
  1. Entender completamente la lógica actual.
  2. Hacer cambios en copia local.
  3. Probar con copia del manifest de producción.
  4. Verificar TODOS los bloques (A, B+C, D, E, Patrocinado) en ambas versiones (desktop/móvil).
  5. Deploy en hora de bajo tráfico.

**2. `procesar-post.php` - Motor de Publicación**
- **Por qué es sensible:** Parsea formato de entrada manual, muy propenso a regex frágiles.
- **Tipos de cambio peligrosos:**
  - Modificar regex de extracción de secciones (puede romper publicación de posts).
  - Cambiar generación de slug (puede crear URLs duplicadas).
  - Alterar template de archivo PHP generado (puede romper formato de posts).
- **Protocolo antes de cambiar:**
  1. Tener 5-10 casos de prueba con diferentes formatos de entrada (títulos con comillas, acentos, etc.).
  2. Probar cada cambio con TODOS los casos de prueba.
  3. Tener plan de rollback (backup de `procesar-post.php` funcional).

**3. `generate_manifest.php` - Regenerador del Índice**
- **Por qué es sensible:** Sobrescribe `posts_manifest.php`, si falla deja el sitio roto.
- **Tipos de cambio peligrosos:**
  - Modificar lógica de extracción de metadatos (puede perder datos).
  - Cambiar estructura del array `$posts` (rompe compatibilidad con consumers).
  - Eliminar validación de sintaxis (puede generar manifest corrupto).
- **Protocolo antes de cambiar:**
  1. SIEMPRE hacer backup de `posts_manifest.php` antes de regenerar.
  2. Validar sintaxis del manifest generado antes de sobrescribir.
  3. Si falla, tener procedimiento de rollback (restaurar backup).

**4. `config.php` - Configuración Global**
- **Por qué es sensible:** Define constantes usadas en TODO el sistema.
- **Tipos de cambio peligrosos:**
  - Cambiar valor de `SITE_URL` (rompe TODOS los enlaces absolutos).
  - Renombrar constantes (rompe todos los archivos que las usan).
  - Agregar lógica compleja (aumenta tiempo de carga de cada request).
- **Protocolo antes de cambiar:**
  1. Buscar TODOS los usos de la constante que se va a cambiar (`grep -r "SITE_URL"`).
  2. Entender impacto en cada uso.
  3. Cambiar solo si es absolutamente necesario.
  4. Probar TODA la navegación del sitio después del cambio.

### Cambios Permitidos sin Gran Riesgo

✅ **Agregar nuevos bloques en portada:** Modificar `get_home_data()` para agregar Bloque F, G, etc.
✅ **Crear nuevas secciones temáticas:** Agregar archivo en `seccion/` + `.ini` en `config/secciones/`.
✅ **Modificar estilos CSS:** Archivos en `/css/` no afectan lógica funcional.
✅ **Agregar nuevos campos a metadatos:** Agregar `$post_subtitle`, etc. en posts (regenerar manifest).
✅ **Cambiar textos estáticos:** Headers, footers, textos de interfaz.
✅ **Optimizar imágenes:** Comprimir, cambiar formato (WebP), no afecta lógica.

### Cambios Prohibidos sin Rediseño Completo

⛔ **Migrar a base de datos MySQL:** Requiere reescribir todo el sistema de lectura/escritura.
⛔ **Hacer sitio SPA (Single Page Application):** Requiere API backend + reescribir frontend.
⛔ **Eliminar dualidad desktop/móvil:** Rompe optimización actual, requiere rediseño UI completo.
⛔ **Cambiar estructura de URLs:** (`/post/slug.php` → `/slug/`) requiere redirecciones masivas + reescribir manifest.
⛔ **Agregar sistema de comentarios:** Requiere persistencia (BD) o servicio externo (Disqus), cambio arquitectónico significativo.

---

## 12. Conclusión - Manifiesto Técnico del Proyecto

El proyecto **Meridiano Béisbol Blog** es una solución pragmática y eficiente diseñada específicamente para sus restricciones de entorno (hosting compartido sin BD). No es un CMS genérico ni aspira a serlo - es un sistema de publicación de noticias deportivas optimizado para velocidad y simplicidad operativa.

### Principios Fundamentales

1. **Velocidad sobre Flexibilidad:** El manifest en memoria sacrifica escalabilidad a largo plazo por velocidad extrema en el corto-medio plazo.

2. **Simplicidad sobre Abstracción:** Archivos PHP directos en lugar de frameworks complejos permiten debugging directo y mantenimiento sin curva de aprendizaje.

3. **Resiliencia por Separación:** Aislar admin de público garantiza que el sitio de cara al usuario sea prácticamente indestructible.

4. **Optimización Específica sobre Responsiveness:** Dos versiones separadas (desktop/móvil) en lugar de una responsive entregan mejor UX y performance.

### Límites Conocidos

- **Escalabilidad:** ~2000-5000 posts antes de considerar refactorización.
- **Búsqueda:** Lenta con >500 posts, no hay solución sin BD o servicio externo.
- **Concurrencia Editorial:** Sistema mono-editor, no preparado para equipos grandes.
- **Multimedia:** No hay gestión de assets (imágenes, videos), se suben manualmente vía FTP.

### Para Mantener o Evolucionar

1. **Respetar la integridad del Manifest:** Es el corazón del sistema, cualquier corrupción tumba el sitio.
2. **Mantener separación Admin/Público:** No mezclar código de escritura en área pública.
3. **No unificar Desktop/Móvil:** La dualidad es una característica, no un bug.
4. **Monitorear tamaño del Manifest:** Si supera 500KB, considerar optimizaciones (eliminar posts viejos del manifest, archiving).
5. **Backup antes de Regenerar:** Siempre tener `posts_manifest.php.backup` antes de sobrescribir.

Este sistema está diseñado para ser administrado por operadores humanos con herramientas visuales (`/gestion/`) y mantenido técnicamente mediante intervenciones quirúrgicas en código, dada la ausencia de bases de datos relacionales. Su arquitectura es transparente, debuggable y portable - valores que priorizan la operatividad sobre la elegancia abstracta.
