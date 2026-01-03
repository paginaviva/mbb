# MBB-estadistica – Modelo de datos y estructura de directorios

Ruta de documento: `/docs/estadistica/02_data_model.md`

---

## 1. Visión general del modelo

* El sistema de estadísticas se basa íntegramente en archivos JSON, sin base de datos.
* La unidad básica de captura es la sesión.
* Para cada día existen dos niveles de datos:

  * Sesiones crudas del día.
  * Agregados diarios a partir de esas sesiones.
* Sobre los agregados diarios se construyen:

  * Ventanas móviles (tres, siete y quince días).
  * Totales históricos por página, categoría, etiqueta y globales.

---

## 2. Estructura de directorios bajo `/estadistica/`

### 2.1 Raíz del módulo

* `/estadistica/`

  * Contendrá la entrada al cuadro de mando (por ejemplo, un archivo en lenguaje de programación PHP para la interfaz), así como recursos estáticos específicos de la interfaz de estadísticas si se requieren (hojas de estilo, guiones del lado del cliente).

### 2.2 Código

* `/estadistica/codigo/`

  * Contendrá el código en lenguaje de programación PHP relacionado con:

    * Captura de sesiones.
    * Escritura en archivos JSON de sesiones diarias.
    * Procesos de agregación diaria.
    * Procesos de actualización de ventanas móviles y totales históricos.
    * Controladores del cuadro de mando.

  * Ejemplos de tipos de archivos que residirán aquí (nombres orientativos, no contractuales):

    * `captura_sesion.php`
    * `cron_agregar_diario.php`
    * `cron_actualizar_ventanas.php`
    * `dashboard_controller.php`

### 2.3 Datos

* `/estadistica/datos/`

  * Directorio raíz para todos los JSON de datos.

  * Subdirectorios:

    * `/estadistica/datos/diarios-sesiones/`

      * Contiene las sesiones crudas por día.
      * Un archivo por día:

        * Nombre: `stats-YYYY-MM-DD.json`
        * Ejemplo: `stats-2025-02-15.json`
    * `/estadistica/datos/diarios-agregados/`

      * Contiene los agregados diarios a partir de las sesiones crudas.
      * Un archivo por día:

        * Nombre: `stats-YYYY-MM-DD.json`
        * Mismo formato de nombre que en `diarios-sesiones`, pero contenido distinto.

  * Archivos JSON en el propio directorio `/estadistica/datos/` (sin más subnivel):

    * Ventanas móviles:

      * `stats_window_3d.json`
      * `stats_window_7d.json`
      * `stats_window_15d.json`
    * Totales históricos:

      * `stats_totals_pages.json`
      * `stats_totals_categories.json`
      * `stats_totals_tags.json`
      * `stats_totals_global.json`

---

## 3. Convenciones generales del modelo JSON

* Codificación:

  * Todos los archivos JSON deben guardarse en codificación UTF-8 sin marca de orden de bytes.
* Formato de fecha:

  * `YYYY-MM-DD` para fechas de día.
* Formato de fecha y hora:

  * ISO 8601, con zona horaria de Venezuela explícita cuando aplique.
  * Ejemplo: `2025-02-15T23:59:30-04:00`.
* Tipos básicos:

  * `string` para textos, identificadores, fechas y horas.
  * `integer` para contadores sin decimales.
  * `number` (coma flotante) para medias, porcentajes y duraciones medias.
  * `boolean` para indicadores lógicos.
  * `array` para listas ordenadas de elementos.
  * `object` para agrupaciones de campos.
* Versión de esquema:

  * Cada JSON incluirá un campo `schema_version` dentro de un bloque de metadatos, para facilitar cambios futuros compatibles.

---

## 4. Modelo de datos: sesiones diarias

Archivo por día en:
`/estadistica/datos/diarios-sesiones/stats-YYYY-MM-DD.json`

### 4.1 Estructura general

* Objeto raíz con dos claves principales:

  * `meta`:

    * Metadatos del archivo.
  * `sessions`:

    * Colección de sesiones del día indexadas por un identificador sintético.

Ejemplo simplificado de estructura (no exhaustivo):

```json
{
  "meta": {
    "date": "2025-02-15",
    "schema_version": 1
  },
  "sessions": {
    "sess_20250215_0001": {
      "...": "..."
    },
    "sess_20250215_0002": {
      "...": "..."
    }
  }
}
```

### 4.2 Campo `meta`

* `meta.date`

  * Tipo: `string`
  * Formato: `YYYY-MM-DD`
  * Restricción: debe coincidir con la fecha del nombre del archivo.
* `meta.schema_version`

  * Tipo: `integer`
  * Valor inicial: `1`
  * Uso: identificar la versión del esquema.

Se pueden añadir en el futuro otros campos de metadatos si fuera necesario (por ejemplo, número total de sesiones registradas).

### 4.3 Identificador de sesión

* Clave dentro de `sessions`, por ejemplo: `sess_20250215_0001`.
* Tipo lógico: identificador sintético de sesión.
* Requisitos:

  * Debe ser único dentro de cada archivo diario.
  * Debe ser una cadena de texto sin espacios, apta como clave de objeto JSON.

### 4.4 Estructura mínima de cada sesión

Cada elemento de `sessions` será un objeto con la siguiente estructura mínima:

* `session_id`

  * Tipo: `string`
  * Igual al identificador usado como clave en el objeto `sessions` (duplicado interno para trazabilidad).

* `started_at`

  * Tipo: `string`
  * Descripción: fecha y hora de inicio de la sesión en formato ISO 8601, incluida la zona horaria.

* `ended_at`

  * Tipo: `string`
  * Descripción: fecha y hora de fin de la sesión en formato ISO 8601.
  * Restricción: debe ser mayor o igual que `started_at`.

* `is_new_session`

  * Tipo: `boolean`
  * Descripción: indica si es una sesión nueva (sin cookie previa identificada) o recurrente.

* `client`

  * Tipo: `object`
  * Campos:

    * `device_type`

      * Tipo: `string`
      * Valores permitidos:

        * `desktop`
        * `mobile`
        * `tablet`
        * `other`
    * `country`

      * Tipo: `string`
      * Valores recomendados:

        * Código de país en formato ISO 3166-1 alfa dos (por ejemplo, `VE`, `US`, `ES`).
    * `user_agent`

      * Tipo: `string`
      * Opcional; puede recortarse o no incluirse si no es necesario a largo plazo.

* `source`

  * Tipo: `object`
  * Campos:

    * `channel`

      * Tipo: `string`
      * Valores sugeridos:

        * `direct`
        * `search`
        * `referral`
        * `social`
        * `campaign`
        * `other`
    * `referrer`

      * Tipo: `string`
      * Opcional. Dirección de la página de origen si está disponible.
    * `search_engine`

      * Tipo: `string`
      * Opcional. Nombre del buscador si se detecta (por ejemplo, `google`, `bing`).
    * `search_query_external`

      * Tipo: `string`
      * Opcional. Palabras clave externas cuando se puedan inferir.

* `navigation`

  * Tipo: `object`
  * Campos:

    * `entry_page`

      * Tipo: `string`
      * Descripción: ruta de la página de entrada (por ejemplo, `/post/...` o `/`).
    * `exit_page`

      * Tipo: `string`
      * Descripción: ruta de la página de salida.
    * `pages`

      * Tipo: `array` de objetos
      * Cada objeto representa una visita a una página:

        * `path`

          * Tipo: `string`
          * Ruta de la página, incluida la ruta relativa (por ejemplo, `/post/...`).
        * `timestamp`

          * Tipo: `string`
          * Fecha y hora de la visita a esa página en formato ISO 8601.
        * `title`

          * Tipo: `string`
          * Título de la página en el momento de la visita.
        * `categories`

          * Tipo: `array` de `string`
          * Lista de categorías asociadas a la página.
        * `tags`

          * Tipo: `array` de `string`
          * Lista de etiquetas asociadas a la página.

* `search`

  * Tipo: `object`
  * Campos:

    * `internal_queries`

      * Tipo: `array` de `string`
      * Lista de términos de búsqueda introducidos en el buscador interno durante la sesión.

* `metrics`

  * Tipo: `object`
  * Campos:

    * `duration_seconds`

      * Tipo: `number`
      * Descripción: duración total de la sesión en segundos (diferencia entre inicio y fin).
      * Restricción: valor mayor o igual que cero.
    * `is_bounce`

      * Tipo: `boolean`
      * Descripción: indica si la sesión se considera rebote según la regla definida:

        * Una sola página vista y duración menor o igual a treinta segundos.
    * `hour_bucket`

      * Tipo: `string`
      * Descripción: franja horaria simplificada de inicio de sesión, por ejemplo `00`, `01`, ... `23`.

* `taxonomies`

  * Tipo: `object`
  * Campos:

    * `categories`

      * Tipo: `array` de `string`
      * Descripción: conjunto de categorías implicadas en la sesión (unión de todas las categorías presentes en las páginas visitadas).
    * `tags`

      * Tipo: `array` de `string`
      * Descripción: conjunto de etiquetas implicadas en la sesión.

---

## 5. Modelo de datos: agregados diarios

Archivo por día en:
`/estadistica/datos/diarios-agregados/stats-YYYY-MM-DD.json`

### 5.1 Estructura general

* Objeto raíz con:

  * `meta`
  * `totals`
  * `by_page`
  * `by_country`
  * `by_source`
  * `by_device`
  * `by_hour`
  * `category_clicks`
  * `tag_clicks`

### 5.2 Campo `meta`

* `meta.date`

  * Tipo: `string`
  * Formato: `YYYY-MM-DD`
* `meta.schema_version`

  * Tipo: `integer`
  * Valor inicial: `1`

### 5.3 Campo `totals` (resumen global del día)

* `totals.sessions`

  * Tipo: `integer`
* `totals.new_sessions`

  * Tipo: `integer`
* `totals.bounce_sessions`

  * Tipo: `integer`
* `totals.bounce_rate`

  * Tipo: `number`
  * Descripción: proporción entre `bounce_sessions` y `sessions`.
* `totals.avg_duration_seconds`

  * Tipo: `number`
* `totals.internal_search_sessions`

  * Tipo: `integer`
* `totals.internal_search_terms`

  * Tipo: `object`
  * Claves: términos de búsqueda interna
  * Valores: conteo de uso en el día

Se pueden añadir otros campos de resumen si se consideran necesarios.

### 5.4 Campo `by_page`

* Estructura:

  * Objeto donde cada clave es la ruta de página (por ejemplo, `/post/amanecer-lvbp-23n-aguilas-mandan-magallanes-se-hunde.php`) y el valor es un objeto con métricas.

Campos por página:

* `sessions`

  * Tipo: `integer`
  * Número de sesiones que han visitado la página.
* `pageviews`

  * Tipo: `integer`
  * Número total de visualizaciones de la página.
* `entry_sessions`

  * Tipo: `integer`
  * Número de sesiones que han comenzado en esa página.
* `exit_sessions`

  * Tipo: `integer`
  * Número de sesiones que han terminado en esa página.
* `bounce_sessions`

  * Tipo: `integer`
  * Número de sesiones de rebote cuya única página es esta.
* `bounce_rate`

  * Tipo: `number`
  * Proporción sobre `sessions` según el criterio de rebote.
* `avg_duration_seconds`

  * Tipo: `number`
  * Tiempo medio asociado a las sesiones que incluyen la página (puede definirse según la lógica que se decida en la implementación).

### 5.5 Campo `by_country`

* Objeto con:

  * Clave: código de país (`"VE"`, `"US"`, `"ES"`, etcétera).
  * Valor: objeto con, al menos:

    * `sessions` (`integer`)
    * `new_sessions` (`integer`)

### 5.6 Campo `by_source`

* Objeto con:

  * Clave: canal (`"direct"`, `"search"`, `"referral"`, `"social"`, `"campaign"`, `"other"`).
  * Valor: objeto con:

    * `sessions` (`integer`)
    * `bounce_sessions` (`integer`)
    * `avg_duration_seconds` (`number`)

### 5.7 Campo `by_device`

* Objeto con:

  * Clave: tipo de dispositivo (`"desktop"`, `"mobile"`, `"tablet"`, `"other"`).
  * Valor: objeto con:

    * `sessions` (`integer`)
    * `bounce_sessions` (`integer`)
    * `avg_duration_seconds` (`number`)

### 5.8 Campo `by_hour`

* Objeto con:

  * Clave: hora en formato `HH` (`"00"` a `"23"`).
  * Valor: objeto con:

    * `sessions` (`integer`)
    * `new_sessions` (`integer`)

### 5.9 Campo `category_clicks`

* Objeto con:

  * Clave: nombre de categoría.
  * Valor: `integer` con el conteo de clics en enlaces de esa categoría durante el día.

Ejemplo:

```json
"category_clicks": {
  "Venezuela": 45,
  "LVBP": 32
}
```

### 5.10 Campo `tag_clicks`

* Objeto con:

  * Clave: nombre de etiqueta.
  * Valor: `integer` con el conteo de clics en enlaces de esa etiqueta durante el día.

Ejemplo:

```json
"tag_clicks": {
  "Serie del Caribe": 18,
  "Magallanes": 12
}
```

---

## 6. Ventanas móviles

Archivos ubicados en:
`/estadistica/datos/`

* `stats_window_3d.json`
* `stats_window_7d.json`
* `stats_window_15d.json`

### 6.1 Estructura general de una ventana móvil

* Objeto raíz con:

  * `meta`
  * `totals`
  * `by_page`
  * `by_country`
  * `by_source`
  * `by_device`
  * `category_clicks`
  * `tag_clicks`

### 6.2 Campo `meta` en ventanas móviles

* `meta.window_type`

  * Tipo: `string`
  * Valores:

    * `3d`
    * `7d`
    * `15d`
* `meta.start_date`

  * Tipo: `string`
  * Fecha inicial de la ventana (`YYYY-MM-DD`).
* `meta.end_date`

  * Tipo: `string`
  * Fecha final de la ventana (`YYYY-MM-DD`).
* `meta.schema_version`

  * Tipo: `integer`

### 6.3 Resto de campos

* `totals`, `by_page`, `by_country`, `by_source`, `by_device`, `category_clicks`, `tag_clicks`

  * Comparten el mismo tipo de estructura que en los agregados diarios, pero con valores ya acumulados en la ventana definida.

---

## 7. Totales históricos

Archivos ubicados en:
`/estadistica/datos/`

* `stats_totals_pages.json`
* `stats_totals_categories.json`
* `stats_totals_tags.json`
* `stats_totals_global.json`

### 7.1 `stats_totals_pages.json`

* Objeto raíz:

  * `meta`
  * `pages`

* `meta`:

  * `schema_version` (`integer`)
  * `updated_at` (`string`, fecha y hora en ISO 8601)

* `pages`:

  * Clave: ruta de página.
  * Valor: objeto con:

    * `sessions` (`integer`)
    * `pageviews` (`integer`)
    * `entry_sessions` (`integer`)
    * `exit_sessions` (`integer`)
    * `bounce_sessions` (`integer`)
    * `bounce_rate` (`number`)
    * `avg_duration_seconds` (`number`)

### 7.2 `stats_totals_categories.json`

* Objeto raíz:

  * `meta`
  * `categories`

* `meta`:

  * `schema_version`
  * `updated_at`

* `categories`:

  * Clave: nombre de categoría.
  * Valor: objeto con:

    * `clicks` (`integer`)

No se guardan métricas de permanencia ni rebote para categorías.

### 7.3 `stats_totals_tags.json`

* Objeto raíz:

  * `meta`
  * `tags`

* `meta`:

  * `schema_version`
  * `updated_at`

* `tags`:

  * Clave: nombre de etiqueta.
  * Valor: objeto con:

    * `clicks` (`integer`)

No se guardan métricas de permanencia ni rebote para etiquetas.

### 7.4 `stats_totals_global.json`

* Objeto raíz:

  * `meta`
  * `totals`

* `meta`:

  * `schema_version`
  * `updated_at`

* `totals`:

  * `sessions` (`integer`)
  * `new_sessions` (`integer`)
  * `bounce_sessions` (`integer`)
  * `bounce_rate` (`number`)
  * `avg_duration_seconds` (`number`)
  * `internal_search_sessions` (`integer`)

Se pueden añadir otros campos globales en función de la evolución del proyecto.

---

## 8. Restricciones y consideraciones

* Todos los archivos deben ser JSON válidos, sin comas finales.
* Los nombres de archivos deben respetar estrictamente el formato acordado para que los procesos de CRON puedan encontrarlos por fecha.
* La suma de datos en agregados diarios, ventanas móviles y totales históricos debe ser consistente entre sí; cualquier cambio en estos modelos debe reflejarse en los procesos de actualización.
* La ausencia de datos en una dimensión (por ejemplo, sin sesiones para una página en un rango determinado) puede representarse:

  * Omite la clave correspondiente en el objeto.
  * O incluye la clave con valores cero.
    La opción concreta se definirá en el diseño técnico, pero debe mantenerse coherente.

Este modelo proporciona una base suficientemente estructurada para implementar el sistema MBB-estadistica, permitiendo crecimiento futuro sin romper la compatibilidad con los datos ya almacenados.
