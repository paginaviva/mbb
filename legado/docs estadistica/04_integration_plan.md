# MBB-estadistica – Arquitectura conceptual e integración

Ruta de documento: `/docs/estadistica/04_integration_plan.md`

---

## 1. Objetivo del documento

* Definir la arquitectura conceptual de MBB-estadistica utilizando lenguaje de programación PHP, archivos JSON, tareas programadas con CRON y un cuadro de mando interno.
* Describir cómo se integran los componentes de captura, almacenamiento, agregación y presentación con el sitio actual del blog de béisbol en el servidor de alojamiento compartido.
* Establecer un plan de integración por fases que reduzca riesgos y permita validar el sistema de forma progresiva.

---

## 2. Visión general de la arquitectura

MBB-estadistica se compone de cinco bloques conceptuales:

* Captura de sesiones en tiempo real.
* Almacenamiento de sesiones diarias en archivos JSON.
* Procesos de agregación y mantenimiento ejecutados por CRON.
* Servicios de lectura de datos para el cuadro de mando.
* Cuadro de mando interno para visualización y análisis.

Relación simplificada entre bloques:

* El visitante navega por el sitio.
* El módulo de captura en lenguaje de programación PHP identifica la sesión, registra los eventos relevantes y escribe en el archivo JSON de sesiones del día.
* Una tarea programada diaria ejecuta uno o varios guiones en lenguaje de programación PHP que:

  * Procesan las sesiones del día anterior.
  * Generan los agregados diarios.
  * Actualizan ventanas móviles y totales históricos.
  * Limpian archivos antiguos.
* El cuadro de mando, accesible solo a administradores, lee los archivos agregados y construye paneles de información para consulta.

---

## 3. Componentes principales

### 3.1 Módulo de captura de sesiones

Responsabilidad:

* Registrar la actividad de cada sesión de usuario en el sitio y enviarla al sistema de almacenamiento de sesiones diarias.

Elementos:

* Fragmento de código en las plantillas del sitio (cabecera o pie):

  * Inserta el identificador de sesión en una cookie si no existe.
  * Realiza llamadas al lado del servidor para registrar:

    * Inicio de sesión.
    * Visitas a páginas.
    * Uso del buscador interno.
    * Clics en categorías y etiquetas.

* Punto de entrada en lenguaje de programación PHP dedicado a la captura:

  * Ubicado bajo `/estadistica/codigo/` (por ejemplo, `captura_sesion.php`).
  * Recibe los datos de cada evento (página, marca de tiempo, taxonomías, etcétera).
  * Reconstruye o actualiza la información de la sesión en memoria.
  * Escribe en el archivo JSON correspondiente al día en curso dentro de `/estadistica/datos/diarios-sesiones/`.

Decisiones clave:

* La unidad lógica es la sesión, no la página vista individual.
* La cookie de sesión se usa únicamente para agrupar eventos, no para registrar datos personales.
* No se guarda la dirección IP, solo el país de origen.

---

### 3.2 Almacenamiento de sesiones diarias (JSON crudo)

Responsabilidad:

* Almacenar, por día natural, el conjunto de sesiones con todos sus detalles, en formato JSON, como base de cálculo para agregados y ventanas.

Estructura:

* Directorio:

  * `/estadistica/datos/diarios-sesiones/`
* Nombre de archivo:

  * `stats-YYYY-MM-DD.json` (un archivo por día).
* Contenido:

  * Metadatos del día (fecha, versión del esquema).
  * Objeto `sessions` con un elemento por sesión, indexado por un identificador sintético.

Consideraciones:

* El archivo JSON crece durante el día, cada vez que se registra una nueva sesión o se actualiza una sesión existente.
* Es necesario proteger las operaciones de escritura para evitar corrupción del archivo:

  * Bloqueo de archivo o estrategia equivalente.
* Pasados sesenta días, los archivos de sesiones diarias pueden eliminarse una vez que se haya garantizado la actualización de todos los agregados y totales.

---

### 3.3 Procesos de agregación y mantenimiento (CRON)

Responsabilidad:

* Transformar las sesiones crudas en datos agregados diarios.
* Construir ventanas móviles y totales históricos.
* Mantener limpia la estructura de datos, eliminando archivos antiguos.

Elementos:

* Tarea programada en el panel de control del servidor de alojamiento compartido:

  * Configurada para ejecutarse todos los días a las 00:05 hora de Venezuela.
  * Llama a uno o varios guiones en lenguaje de programación PHP en `/estadistica/codigo/`.

Roles de los guiones principales:

* Guion de agregación diaria:

  * Lee el archivo `stats-YYYY-MM-DD.json` de sesiones del día anterior.
  * Calcula:

    * Totales globales del día.
    * Métricas por página.
    * Distribuciones por país, canal, dispositivo, hora.
    * Sumatorio de clics por categoría.
    * Sumatorio de clics por etiqueta.
  * Escribe el resultado en:

    * `/estadistica/datos/diarios-agregados/stats-YYYY-MM-DD.json`.

* Guion de actualización de ventanas móviles:

  * Lee los agregados diarios de los últimos tres, siete y quince días.
  * Fusiona los datos en archivos:

    * `stats_window_3d.json`
    * `stats_window_7d.json`
    * `stats_window_15d.json`
  * Calcula medias y totales necesarios para las vistas de comparación.

* Guion de actualización de totales históricos:

  * Recorre los agregados diarios disponibles (o un registro incremental) y actualiza:

    * `stats_totals_pages.json`
    * `stats_totals_categories.json`
    * `stats_totals_tags.json`
    * `stats_totals_global.json`
  * Mantiene coherente el historial sin necesidad de recalcular desde el principio cada vez.

* Guion de limpieza:

  * Elimina archivos antiguos en:

    * `/estadistica/datos/diarios-sesiones/`
    * `/estadistica/datos/diarios-agregados/`
  * Respeta la política de retención de sesenta días.

Decisiones clave:

* La separación de guiones permite ajustar el rendimiento:

  * Agregación diaria.
  * Ventanas móviles.
  * Totales históricos.
  * Limpieza.
* El fallo en uno de los pasos debe registrarse y no bloquear el resto de procesos si no es imprescindible.

---

### 3.4 Capa de servicios de lectura de datos

Responsabilidad:

* Proporcionar a la interfaz de usuario un acceso sencillo y unificado a los datos estadísticos almacenados en los archivos JSON.

Elementos:

* Conjunto de funciones o clases en lenguaje de programación PHP bajo `/estadistica/codigo/` que encapsulan la lectura y procesamiento de los archivos JSON:

  * Cargan los archivos correspondientes según la vista:

    * Resumen del día anterior.
    * Ventana de tres, siete o quince días.
    * Totales históricos por página, categoría o etiqueta.
  * Transforman los datos en estructuras listas para presentar en el cuadro de mando:

    * Listas ordenadas.
    * Paneles de métricas clave.
    * Distribuciones por canal, país, dispositivo, hora.
  * Ocultan los detalles del formato JSON al resto de la aplicación.

Criterios:

* Las funciones de lectura deben:

  * Validar que los archivos JSON son legibles.
  * Manejar la ausencia de datos de forma controlada (por ejemplo, devolver listas vacías).
  * Evitar cargar en memoria más datos de los necesarios para cada vista (por ejemplo, carga parcial cuando el número de páginas es elevado).

---

### 3.5 Cuadro de mando (front-end interno)

Responsabilidad:

* Presentar los datos agregados de manera clara y utilizable para el equipo de producto, integrándose con la zona de gestión existente.

Elementos:

* Controlador principal del cuadro de mando en `/estadistica/`:

  * Controla la navegación entre vistas:

    * Resumen día anterior.
    * Comparaciones de tres, siete y quince días.
    * Totales históricos por página, categoría y etiqueta.
  * Invoca a los servicios de lectura de datos.
  * Pasa los datos a las plantillas de interfaz.

* Plantillas de interfaz de usuario:

  * Estructura común de encabezado y menú lateral.
  * Paneles de información con tarjetas, tablas y gráficos simples.
  * Enfoque en:

    * Resumen del día anterior por defecto.
    * Navegación por enlaces directos en el menú lateral.
    * Lectura rápida con jerarquía visual clara.

Integración:

* Protección de acceso:

  * El directorio `/estadistica/` se protege con el mismo mecanismo que las herramientas de `/gestion/`.
* Estilo visual:

  * Se mantiene coherente con el panel de administración existente para no introducir cambios bruscos de experiencia.

---

## 4. Flujos principales de integración

### 4.1 Flujo de captura en tiempo real

* El usuario accede a cualquier página del sitio.
* El código de enlace en lenguaje de programación PHP en las plantillas:

  * Comprueba la existencia de la cookie de sesión.
  * Si no existe, genera un nuevo identificador de sesión sintético.
* En cada evento relevante:

  * Visita a página.
  * Uso del buscador interno.
  * Clic en categoría o etiqueta.
* Se envía una llamada al punto de captura bajo `/estadistica/codigo/` con la información necesaria:

  * Ruta de la página.
  * Marca de tiempo.
  * Taxonomías implicadas.
  * Datos de canal, país y dispositivo.
* El punto de captura:

  * Actualiza la estructura de la sesión en memoria.
  * Escribe o actualiza la entrada correspondiente en el archivo JSON de sesiones del día.

Resultado:

* El archivo `stats-YYYY-MM-DD.json` en `diarios-sesiones` refleja en tiempo casi real la actividad del día en términos de sesiones.

---

### 4.2 Flujo de cierre de día y consolidación

* A las 00:05 hora de Venezuela:

  * El CRON del panel de control lanza el guion de agregación diaria.
* El guion:

  * Lee el archivo de sesiones crudas del día anterior.
  * Calcula los agregados.
  * Escribe el archivo `stats-YYYY-MM-DD.json` en `diarios-agregados`.
* A continuación:

  * El guion de actualización de ventanas móviles consolida datos en `stats_window_3d.json`, `stats_window_7d.json` y `stats_window_15d.json`.
  * El guion de actualización de totales históricos actualiza los archivos de totales en `/estadistica/datos/`.
  * El guion de limpieza elimina archivos viejos conforme a la política de sesenta días.

Resultado:

* Al empezar el día, el cuadro de mando dispone de:

  * El resumen completo del día anterior.
  * Ventanas móviles actualizadas.
  * Totales históricos consistentes.

---

### 4.3 Flujo de consulta desde el cuadro de mando

* Un usuario autorizado entra en el panel administrativo y navega a `/estadistica/`.
* El controlador del cuadro de mando:

  * En la vista de inicio, carga:

    * El agregado diario del día anterior.
    * Opcionalmente, indicadores de la ventana de tres días como contexto.
  * Construye las estructuras necesarias:

    * Panel de indicadores globales.
    * Tablas de páginas principales.
    * Distribuciones por canal, país, dispositivo y hora.
    * Listados de términos de búsqueda interna.
    * Resumen de clics en categorías y etiquetas.
* Si el usuario cambia a:

  * Comparación de tres, siete o quince días:

    * El controlador lee los archivos de ventana correspondientes y recalcula las diferencias.
  * Totales por página, categoría o etiqueta:

    * El controlador lee `stats_totals_pages.json`, `stats_totals_categories.json` o `stats_totals_tags.json`.

Resultado:

* El usuario puede moverse entre vistas sin afectar la captura ni los procesos de agregación y mantenimiento.

---

## 5. Integración con el sistema existente

### 5.1 Puntos de inserción en plantillas

* Cabecera o pie global del sitio:

  * Inserción del código de gestión de cookie de sesión.
  * Inclusión del guion de envío de eventos al punto de captura.
* Plantillas de páginas de contenido:

  * Disponibilidad de:

    * Ruta de la página.
    * Información de categorías y etiquetas.
  * Esta información se envía al módulo de captura.

### 5.2 Integración con el buscador interno

* El formulario de búsqueda interna se amplía para:

  * Enviar el término de búsqueda al módulo de estadísticas.
* El módulo registra:

  * La sesión.
  * La hora.
  * El término buscado.
* Esto alimenta las métricas de:

  * Sesiones con búsqueda interna.
  * Términos internos más usados.

### 5.3 Integración con enlaces de categorías y etiquetas

* Cada enlace de categoría y etiqueta se ajusta para:

  * Lanzar un evento de clic al módulo de captura.
* El módulo sumará:

  * Un conteo por categoría o etiqueta en el contexto de la sesión.
* Estos conteos se agregan después:

  * En los agregados diarios.
  * En los totales históricos de categorías y etiquetas.

---

## 6. Consideraciones de rendimiento y seguridad

### 6.1 Rendimiento

* Los archivos JSON diarios pueden crecer, pero:

  * Solo se mantiene en memoria lo necesario para actualizar la sesión correspondiente.
  * La agregación se realiza con CRON en horarios de baja carga.
* Las vistas del cuadro de mando se basan en archivos agregados:

  * Lectura menos costosa que procesar sesiones crudas.
  * Tablas con paginación y límites para evitar sobrecarga en casos de muchos registros.

### 6.2 Seguridad y privacidad

* Ningún archivo JSON almacena dirección IP ni datos personales identificables.
* El uso de cookie se limita a:

  * Identificar sesiones.
  * Distinguir entre nuevas y recurrentes.
* El acceso al directorio `/estadistica/`:

  * Se controla con las mismas restricciones que el área de gestión:

    * Autenticación del administrador.
    * Restricción por servidor web si corresponde.
* Los archivos JSON no deben ser accesibles directamente desde el exterior:

  * Se ubican en rutas que, preferentemente, no se exponen como estáticos.
  * Su lectura se realiza solo desde guiones controlados en lenguaje de programación PHP.

---

## 7. Plan de integración por fases

### 7.1 Fase 1 – Infraestructura y directorios

* Crear estructura de directorios:

  * `/estadistica/`
  * `/estadistica/codigo/`
  * `/estadistica/datos/`
  * `/estadistica/datos/diarios-sesiones/`
  * `/estadistica/datos/diarios-agregados/`
* Definir permisos adecuados de lectura y escritura.

### 7.2 Fase 2 – Captura de sesiones

* Implementar el módulo de captura en lenguaje de programación PHP.
* Integrarlo con las plantillas actuales:

  * Cabecera o pie.
  * Buscador interno.
  * Enlaces de categorías y etiquetas.
* Validar que se generan archivos JSON diarios con sesiones correctas.

### 7.3 Fase 3 – Agregación y CRON

* Implementar guiones de:

  * Agregación diaria.
  * Ventanas móviles.
  * Totales históricos.
  * Limpieza.
* Configurar CRON en el panel de control para ejecución a las 00:05 hora de Venezuela.
* Verificar resultados de agregados y consistencia de datos.

### 7.4 Fase 4 – Cuadro de mando

* Implementar servicios de lectura de JSON.
* Diseñar y desarrollar las pantallas:

  * Resumen día anterior.
  * Comparaciones 3, 7 y 15 días.
  * Totales por página, categoría y etiqueta.
* Proteger el acceso al cuadro de mando conforme a las políticas actuales del sitio.

### 7.5 Fase 5 – Ajustes y estabilización

* Revisar consumo de recursos:

  * Tiempos de ejecución de CRON.
  * Tamaño de archivos JSON.
* Ajustar:

  * Límites de paginación en tablas.
  * Nivel de detalle de datos almacenados si fuera necesario.
* Documentar:

  * Procedimientos de recuperación ante fallos.
  * Dependencias con el sistema de ficheros y el panel de control del servidor de alojamiento compartido.

Con esta arquitectura conceptual, MBB-estadistica queda definido como un módulo autónomo pero estrechamente integrado con el sitio actual, respetando las restricciones del servidor de alojamiento compartido y apoyándose en JSON, lenguaje de programación PHP, tareas programadas con CRON y un cuadro de mando interno orientado al día anterior y a las tendencias de corto plazo.
