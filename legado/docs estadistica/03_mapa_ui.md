# MBB-estadistica – Mapa de interfaz de usuario

Ruta de documento: `/docs/estadistica/03_mapa_ui.md`

---

## 1. Visión general

* El módulo MBB-estadistica ofrece un cuadro de mando interno accesible en el directorio `/estadistica/`, protegido igual que las herramientas de gestión del sitio.
* La interfaz se centra en:

  * Mostrar el resumen del día anterior.
  * Comparar ese día con la media de los últimos tres, siete y quince días.
  * Consultar totales históricos por página, categoría y etiqueta.
* El diseño prioriza:

  * Lectura rápida.
  * Jerarquía visual clara.
  * Pocas pantallas bien definidas.
  * Navegación estable mediante un menú lateral izquierdo.

---

## 2. Layout común

Todas las pantallas comparten una estructura base:

* Encabezado superior

  * Título del módulo (por ejemplo, “MBB-estadistica”).
  * Información resumida del rango de fechas mostrado.
  * Enlace discreto de retorno al área general de gestión, si aplica.

* Menú lateral izquierdo (navegación principal)

  * Entrada “Resumen día anterior”.
  * Entrada “Comparación 3 días”.
  * Entrada “Comparación 7 días”.
  * Entrada “Comparación 15 días”.
  * Separador visual.
  * Entrada “Totales históricos por página”.
  * Entrada “Totales históricos por categoría”.
  * Entrada “Totales históricos por etiqueta”.

* Área de contenido principal

  * Zona de indicadores clave (panel de cifras grandes).
  * Zona de tablas ordenables.
  * Zona de gráficos sencillos cuando tenga sentido.

Reglas básicas:

* El menú lateral permanece visible en todas las rutas.
* El encabezado muestra siempre la etiqueta clara de la vista activa.
* No se usan filtros complejos; como máximo selección de fecha en vistas históricas, si se añadiera en fases posteriores.

---

## 3. Listado de pantallas (routes lógicas)

### 3.1. `/estadistica/` – Resumen del día anterior

Ruta lógica:

* `/estadistica/` o `/estadistica/index`

Objetivo:

* Mostrar de forma concentrada el comportamiento completo del día anterior.

Componentes clave:

* Encabezado

  * Título: “Resumen del día anterior”.
  * Subtítulo con la fecha del día analizado (por ejemplo, “Datos del 2025-02-14”).

* Panel de indicadores principales (disposición en tarjetas)

  * Total de sesiones.
  * Porcentaje de nuevas sesiones.
  * Porcentaje de rebote global.
  * Duración media de sesión (en formato legible, por ejemplo, minutos y segundos).
  * Número de sesiones con búsqueda interna.
  * Número total de búsquedas internas realizadas.

* Bloque “Orígenes de tráfico”

  * Gráfico circular o barras horizontales con distribución por canal:

    * Directo.
    * Buscador.
    * Referencia.
    * Red social.
    * Campaña.
    * Otros.
  * Tabla simple de apoyo con:

    * Canal.
    * Sesiones.
    * Porcentaje sobre el total.

* Bloque “Páginas principales del día”

  * Tabla ordenable, por defecto ordenada por sesiones o páginas vistas:

    * Columna “Página” (título o ruta amigable).
    * Columna “Sesiones”.
    * Columna “Páginas vistas”.
    * Columna “Porcentaje de rebote”.
    * Columna “Duración media asociada”.
  * Posibilidad de limitar a las diez o veinte páginas más relevantes para facilitar la lectura.

* Bloque “Distribución horaria”

  * Gráfico de barras con sesiones por hora (de 00 a 23).
  * Tabla complementaria opcional con:

    * Hora.
    * Sesiones.

* Bloque “Países principales”

  * Tabla resumida:

    * País.
    * Sesiones.
    * Porcentaje sobre el total.
  * Gráfico pequeño de barras o circular si mejora la lectura.

* Bloque “Buscador interno”

  * Listado de los términos internos más usados:

    * Término.
    * Número de búsquedas.
  * Indicador de sesiones que han utilizado el buscador.

* Bloque “Actividad por categorías y etiquetas”

  * Resumen simple:

    * Categorías con más clics en el día (nombre y número de clics).
    * Etiquetas con más clics en el día (nombre y número de clics).
  * Sin métricas de permanencia ni rebote en este bloque, solo conteos.

Reglas de presentación:

* Las tarjetas de indicadores deben resaltar con tamaño mayor y colores sobrios.
* Las tablas deben permitir ordenar por columnas clave (sesiones, rebote, duración).
* Los gráficos deben mantener una escala clara y no sobrecargar el color.

---

### 3.2. `/estadistica/comparacion-3d` – Comparación tres días

Ruta lógica:

* `/estadistica/comparacion-3d`

Objetivo:

* Comparar el día anterior con la media de los últimos tres días completos.

Componentes clave:

* Encabezado

  * Título: “Comparación 3 días”.
  * Subtítulo con:

    * Día anterior.
    * Rango de fechas de la ventana de tres días.

* Panel de comparación global

  * Tarjetas dobles con:

    * Métrica del día anterior.
    * Media de tres días.
    * Diferencia absoluta.
    * Diferencia porcentual.
  * Métricas mínimas:

    * Sesiones.
    * Porcentaje de rebote.
    * Duración media.
    * Porcentaje de nuevas sesiones.

* Bloque “Páginas destacadas vs media 3 días”

  * Tabla con las páginas más relevantes:

    * Página.
    * Sesiones día anterior.
    * Media de sesiones tres días.
    * Variación porcentual.
  * Permite detectar subidas y bajadas.

* Bloque “Orígenes vs media 3 días”

  * Tabla o gráfico:

    * Canal.
    * Sesiones día anterior.
    * Media de tres días.
    * Diferencia porcentual.

Reglas de navegación:

* En el menú lateral, la opción “Comparación 3 días” queda resaltada.
* Debe existir un enlace claro para volver al “Resumen día anterior” dentro del encabezado o en un botón superior.

---

### 3.3. `/estadistica/comparacion-7d` – Comparación siete días

Ruta lógica:

* `/estadistica/comparacion-7d`

Estructura:

* Igual que la de tres días, pero usando la ventana de siete días.
* Misma disposición de panel, bloques y tablas, adaptadas a la media de siete días.

Reglas:

* La vista debe dejar claro en texto qué rango de fechas abarca la media de siete días.
* Evitar duplicar información excesiva; mantener la estructura coherente con la vista de tres días.

---

### 3.4. `/estadistica/comparacion-15d` – Comparación quince días

Ruta lógica:

* `/estadistica/comparacion-15d`

Estructura:

* Igual que la de tres y siete días, con énfasis en tendencias a medio plazo.

Componentes:

* Panel de indicadores globales.
* Tabla de páginas con variaciones frente a la media de quince días.
* Bloque de orígenes.

Regla:

* La interfaz de las tres vistas de comparación (3, 7, 15 días) debe mantener la misma estructura de secciones, para que el usuario cambie de horizonte temporal sin cambios de diseño mental.

---

### 3.5. `/estadistica/totales-paginas` – Totales históricos por página

Ruta lógica:

* `/estadistica/totales-paginas`

Objetivo:

* Ofrecer una vista acumulada de comportamiento por página.

Componentes clave:

* Encabezado

  * Título: “Totales históricos por página”.
  * Información de la última fecha de actualización de los totales.

* Controles simples

  * Selector de criterio de orden:

    * Por defecto: sesiones.
    * Alternativas: páginas vistas, rebote, duración media.
  * Caja de búsqueda textual para filtrar por título o ruta de página.

* Tabla de totales

  * Columnas mínimas:

    * Página (título y ruta).
    * Sesiones acumuladas.
    * Páginas vistas acumuladas.
    * Porcentaje de rebote histórico.
    * Duración media histórica.
  * Paginación simple si el número de páginas es grande.
  * Ordenación clicando en encabezados de columna.

Reglas de presentación:

* Destacar visualmente la columna de sesiones como referencia principal.
* Permitir que el usuario identifique rápidamente las páginas con peor rebote o menor duración (por ejemplo, mediante ordenación descendente y uso moderado de color para valores extremos).

---

### 3.6. `/estadistica/totales-categorias` – Totales históricos por categoría

Ruta lógica:

* `/estadistica/totales-categorias`

Objetivo:

* Mostrar la relevancia histórica de cada categoría en términos de clics.

Componentes clave:

* Encabezado

  * Título: “Totales históricos por categoría”.
  * Indicador de fecha de actualización.

* Controles simples

  * Ordenación:

    * Por defecto: número de clics de mayor a menor.
  * Búsqueda por nombre de categoría.

* Tabla de categorías

  * Columnas:

    * Categoría.
    * Clics acumulados.
  * Paginación si el número de categorías es elevado.

Reglas:

* No se muestran métricas de permanencia ni rebote para categorías.
* Puede añadirse un indicador de “categoría activa” (si se considera que algunas categorías ya no se utilizan) en fases posteriores, pero no forma parte del alcance mínimo.

---

### 3.7. `/estadistica/totales-etiquetas` – Totales históricos por etiqueta

Ruta lógica:

* `/estadistica/totales-etiquetas`

Objetivo:

* Mostrar la relevancia histórica de cada etiqueta a partir de los clics en sus enlaces.

Componentes clave:

* Encabezado

  * Título: “Totales históricos por etiqueta”.
  * Fecha de última actualización.

* Controles

  * Ordenación por clics (de mayor a menor).
  * Búsqueda por nombre de etiqueta.

* Tabla de etiquetas

  * Columnas:

    * Etiqueta.
    * Clics acumulados.

Reglas:

* No se muestran métricas de permanencia ni rebote para etiquetas.
* El diseño debe ser análogo al de categorías, para evitar confusión.

---

## 4. Flujos principales de usuario

### 4.1. Flujo “Consulta rápida de la situación del día anterior”

* Usuario accede al área de gestión del sitio.
* Desde allí abre la ruta `/estadistica/`.
* Visualiza:

  * Panel de indicadores globales del día anterior.
  * Páginas principales.
  * Orígenes.
  * Distribución horaria.
  * Países principales.
  * Actividad de buscador interno.
  * Resumen de categorías y etiquetas.

Resultado:

* El usuario obtiene en pocos segundos una imagen clara de cómo se comportó el sitio el día anterior.

### 4.2. Flujo “Detección de tendencias a corto y medio plazo”

* Desde el menú lateral, el usuario navega a:

  * “Comparación 3 días”.
  * Después a “Comparación 7 días”.
  * Después a “Comparación 15 días”.
* En cada vista:

  * Observa cómo cambian las métricas clave del día anterior frente a la media del periodo.
  * Revisa las tablas de páginas destacadas para identificar subidas o caídas.

Resultado:

* El usuario identifica tendencias recientes sin necesidad de construir informes manualmente.

### 4.3. Flujo “Análisis estructural por página”

* El usuario abre “Totales históricos por página”.
* Filtra o ordena la tabla:

  * Por sesiones, para encontrar las páginas más importantes.
  * Por rebote, para localizar problemas de contenido.
  * Por duración, para ver contenidos más absorbentes.
* Si lo desea, usa el campo de búsqueda para localizar una página concreta.

Resultado:

* El usuario entiende qué páginas sostienen el tráfico y cuáles requieren atención.

### 4.4. Flujo “Peso de categorías y etiquetas”

* El usuario abre “Totales históricos por categoría”.

  * Identifica qué categorías concentran más clics.
* El usuario abre “Totales históricos por etiqueta”.

  * Ve qué etiquetas resultan más atractivas para los visitantes.

Resultado:

* El usuario dispone de criterio para reforzar o reestructurar categorías y etiquetas.

---

## 5. Reglas básicas de navegación

* El menú lateral siempre permite saltar de una vista a otra sin pasos intermedios.
* La pantalla de “Resumen día anterior” actúa como referencia principal y punto de retorno.
* Las rutas de comparación (3, 7, 15 días) comparten estructura casi idéntica para favorecer la comprensión:

  * Cambio de horizonte temporal sin cambio mental de diseño.
* Las tablas deben:

  * Mostrar por defecto las filas más relevantes en la parte superior.
  * Permitir ordenación por campos clave sin recarga confusa.
* En toda la interfaz:

  * Evitar sobrecarga de información en una sola vista.
  * Priorizar paneles de resumen en la parte superior y tablas detalladas por debajo.
  * Usar una paleta de colores sobria, reservando indicaciones más intensas para los casos extremos (por ejemplo, rebote muy alto).

Con este mapa de interfaz de usuario, MBB-estadistica dispone de una estructura clara de pantallas y flujos, centrada en el día anterior y en la comparación con rangos cortos, con vistas históricas sencillas por página, categoría y etiqueta.
