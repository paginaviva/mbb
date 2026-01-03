# MBB-estadistica – Alcance del sistema

Ruta de documento: `/docs/estadistica/scope.md`

---

## 1. Objetivo general del sistema

* Proporcionar un sistema propio y ligero de estadísticas para el blog de béisbol, independiente de Google Analytics 4, centrado en la lectura rápida de datos clave del comportamiento diario de las sesiones.
* Permitir un seguimiento sencillo de la trayectoria diaria del sitio mediante un cuadro de mando accesible en el entorno de administración, sin depender de consultas complejas ni análisis externos.
* Preparar los datos de forma neutral y portable, facilitando una futura migración a otro sistema sin necesidad de base de datos relacional.

---

## 2. Objetivos específicos

* Registrar cada sesión de usuario en el sitio, con información suficiente para calcular:

  * Volumen de sesiones por día.
  * Origen de las sesiones.
  * Navegación por páginas.
  * Tiempo de permanencia por sesión.
  * Porcentaje de rebote.
  * Distribución por país.
  * Uso del buscador interno.
  * Palabras de búsqueda relevantes cuando sea posible.
* Generar resúmenes diarios consolidados y ventanas móviles de tres, siete y quince días.
* Ofrecer vistas de totales históricos por:

  * Página.
  * Categoría.
  * Etiqueta.
* Mantener un sistema de consulta interno y protegido, integrado con el entorno de gestión ya existente en el servidor de alojamiento compartido.

---

## 3. Alcance funcional

### 3.1 Captura de sesiones

* Registrar una sesión cada vez que un usuario inicia una visita al sitio.
* Asociar a cada sesión:

  * Identificador sintético único.
  * Marca de tiempo de inicio de la sesión.
  * Marca de tiempo de fin de la sesión.
  * Lista ordenada de páginas visitadas.
  * Página de entrada y página de salida.
  * País de origen.
  * Origen de la sesión (directa, buscador, referencia, otros).
  * Tipo de dispositivo (móvil, ordenador, tableta).
  * Indicador de nueva sesión o recurrente, apoyándose en una cookie.
  * Búsquedas internas realizadas en el sitio.
  * Palabras de búsqueda externas inferibles, cuando sea posible.
  * Categorías y etiquetas asociadas a las páginas visitadas.
* Almacenar cada sesión en un archivo JSON diario en la ruta:

  * `/estadistica/datos/diarios-sesiones/stats-YYYY-MM-DD.json`.

### 3.2 Agregación diaria y ventanas móviles

* Ejecutar una tarea programada una vez al día (CRON del panel de control del proveedor) a las 00:05 hora de Venezuela para procesar el día anterior.
* Leer el archivo JSON de sesiones crudas del día anterior en `/estadistica/datos/diarios-sesiones/`.
* Calcular y almacenar un resumen agregado diario en otro archivo JSON en:

  * `/estadistica/datos/diarios-agregados/stats-YYYY-MM-DD.json`.
* A partir de los agregados diarios:

  * Actualizar los datos consolidados de:

    * Últimos tres días.
    * Últimos siete días.
    * Últimos quince días.
  * Actualizar los totales históricos acumulados.

### 3.3 Métricas por página

* Calcular, para cada página, como mínimo:

  * Total de sesiones en el día.
  * Tiempo medio de permanencia por sesión en esa página.
  * Porcentaje de rebote.
* Definición de rebote:

  * Una sesión se considera rebote si el usuario solo ve una página y el tiempo de permanencia total es menor o igual a treinta segundos.
* Calcular:

  * Totales diarios por página.
  * Totales para tres, siete y quince días.
  * Totales históricos acumulados por página.

### 3.4 Métricas por categoría

* Registrar un conteo para cada clic o acceso a enlaces de categoría.
* Sumar, para cada categoría:

  * Número de accesos en el día (clics en enlaces de categoría).
  * Totales para tres, siete y quince días.
  * Totales históricos acumulados.
* No se calcularán para categorías:

  * Tiempo de permanencia.
  * Porcentaje de rebote.
  * Otras métricas derivadas.

### 3.5 Métricas por etiqueta

* Registrar un conteo para cada clic o acceso a enlaces de etiqueta.
* Sumar, para cada etiqueta:

  * Número de accesos en el día (clics en enlaces de etiqueta).
  * Totales para tres, siete y quince días.
  * Totales históricos acumulados.
* No se calcularán para etiquetas:

  * Tiempo de permanencia.
  * Porcentaje de rebote.
  * Otras métricas derivadas.

---

## 4. Datos y almacenamiento

### 4.1 Archivos diarios de sesiones

* Un archivo JSON por día en:

  * `/estadistica/datos/diarios-sesiones/stats-YYYY-MM-DD.json`.
* Contenido:

  * Conjunto de sesiones del día, indexadas por un identificador sintético.
  * Cada entrada contiene todos los campos definidos para la sesión.
* Política de retención:

  * Los archivos de sesiones diarias pueden eliminarse a partir de los sesenta días.

### 4.2 Archivos diarios agregados

* Un archivo JSON de agregados por día en:

  * `/estadistica/datos/diarios-agregados/stats-YYYY-MM-DD.json`.
* Contenido:

  * Totales por página.
  * Totales por categoría.
  * Totales por etiqueta.
  * Métricas agregadas necesarias para el cuadro de mando (rebote, permanencia, distribución horaria, origen, país, dispositivo, búsquedas).
* Política de retención:

  * Los archivos agregados diarios pueden eliminarse a partir de los sesenta días, siempre que los totales históricos estén correctamente actualizados.

### 4.3 Archivos de ventanas móviles y totales históricos

* Archivos JSON en `/estadistica/datos/` (sin subdirectorio adicional) para:

  * Ventana de tres días.
  * Ventana de siete días.
  * Ventana de quince días.
  * Totales históricos por página.
  * Totales históricos por categoría.
  * Totales históricos por etiqueta.
  * Sumatoria global histórica del sitio.
* Estos archivos se actualizan diariamente a partir de los agregados diarios.
* No se definen borrados automáticos para estos archivos, salvo decisión posterior de mantenimiento.

---

## 5. Cuadro de mando e interfaces de usuario

### 5.1 Acceso y ubicación

* El cuadro de mando se ubica bajo el directorio:

  * `/estadistica/`.
* El acceso al panel:

  * Se protege con el mismo mecanismo de control que las herramientas del directorio de gestión del sitio.
  * No está destinado al público, solo a uso interno.

### 5.2 Página inicial del cuadro de mando

* Mostrar siempre el resumen del día anterior, no el día en curso.
* Indicadores mínimos:

  * Total de sesiones.
  * Distribución por origen (directa, buscador, referencia, otros).
  * Páginas más visitadas del día.
  * Países principales.
  * Distribución horaria de sesiones.
  * Porcentaje de nuevas sesiones frente a recurrentes.
  * Porcentaje de rebote global.
  * Tiempo medio de permanencia.
  * Sesiones con búsqueda interna y principales términos de búsqueda internos.
  * Datos destacados de clics en categorías y etiquetas.

### 5.3 Menú lateral

* Menú a la izquierda con enlaces a:

  * Resumen del día anterior.
  * Comparación del día anterior con:

    * Media de los últimos tres días.
    * Media de los últimos siete días.
    * Media de los últimos quince días.
  * Vista de totales históricos por página:

    * Total de visitas por página.
    * Porcentaje de permanencia por página.
    * Porcentaje de rebote por página.
  * Vista de totales históricos por categoría:

    * Total de clics en cada categoría.
  * Vista de totales históricos por etiqueta:

    * Total de clics en cada etiqueta.

### 5.4 Visualización

* Presentación clara y sintética de datos, orientada a lectura rápida:

  * Listados ordenados por importancia (por ejemplo, páginas más vistas en primer lugar).
  * Gráficos sencillos cuando resulte útil, sin sobrecarga visual.
* No se incluye exportación a archivos descargables en esta fase.
* No se incluye edición manual de datos desde el cuadro de mando.

---

## 6. Procesos automáticos

* Uso del sistema de tareas programadas del proveedor (CRON de panel de control) para ejecutar guiones en lenguaje de programación PHP que:

  * Procesan las sesiones del día anterior.
  * Generan y actualizan los archivos de agregados diarios.
  * Actualizan los archivos de ventanas móviles.
  * Actualizan los archivos de totales históricos.
* Hora de ejecución:

  * 00:05 hora de Venezuela.
* Procesos redundantes o de recuperación podrán definirse en la fase de diseño detallado, pero no forman parte del alcance mínimo.

---

## 7. Integración con la plataforma existente

* Entorno:

  * Servidor de alojamiento compartido ya utilizado por el proyecto del blog de béisbol.
  * Código existente en lenguaje de programación PHP, archivos estáticos y rutas ya definidas.
* Integración:

  * Inclusión de un módulo de captura que se integra con las plantillas actuales del sitio, respetando la arquitectura de archivos PHP.
  * Creación de un nuevo árbol de directorios bajo `/estadistica/`:

    * `/estadistica/datos/diarios-sesiones/`
    * `/estadistica/datos/diarios-agregados/`
    * `/estadistica/datos/`
    * `/estadistica/codigo/` (para los guiones de captura, agregación y cuadro de mando).
* Documentación asociada al sistema de estadísticas en:

  * `/docs/estadistica/`:

    * Este documento de alcance.
    * Documentos de modelo de datos, mapa de interfaz de usuario, plan de integración y lista de verificación de despliegue.

---

## 8. Seguridad y privacidad

* No se almacena la dirección IP de los usuarios ni otros datos personales identificables.
* Se almacena el país derivado de la visita, no los datos brutos de red.
* Se utiliza una cookie para identificar sesiones y diferenciar nuevas sesiones de recurrentes, con la finalidad exclusiva de análisis estadístico interno.
* El acceso al panel de estadísticas:

  * Se restringe al personal autorizado, con el mismo esquema de protección que otras herramientas de gestión.
* La información estadística no se ofrece públicamente ni se expone en interfaces externas en esta fase.

---

## 9. Supuestos y restricciones

* No se utiliza una base de datos relacional; todo el almacenamiento se basa en archivos JSON.
* El sistema debe funcionar dentro de las limitaciones de un servidor de alojamiento compartido:

  * Sin acceso a línea de comandos.
  * Tiempos de ejecución ajustados a las restricciones del proveedor.
* El sistema se diseña pensando en una futura migración a otra plataforma:

  * Formatos de datos neutros y fáciles de importar.
* El ámbito del sistema de estadísticas abarca todo el dominio del blog, sin otras secciones externas.

---

## 10. Fuera de alcance

* No se incluye:

  * Sustitución completa de Google Analytics 4 ni integración avanzada con este.
  * Segmentación compleja de usuarios.
  * Atribución de campañas de marketing ni análisis multicanal avanzado.
  * Edición manual de datos desde el panel.
  * Exportación de datos a otros formatos (valores separados por comas, hojas de cálculo, entre otros).
  * Optimización automatizada de contenidos en función de las estadísticas.
* No se contempla en esta fase:

  * Sistema de alertas automáticas por correo electrónico.
  * Panel público de datos.

---

## 11. Criterios de éxito

* El sistema registra sesiones diarias de forma consistente y genera archivos JSON correctos de sesiones y agregados.
* El cuadro de mando:

  * Muestra cada día el resumen completo del día anterior.
  * Permite comparar el día anterior con las medias de tres, siete y quince días.
  * Ofrece vistas claras y utilizables de totales históricos por página, categoría y etiqueta.
* La ejecución diaria programada se realiza sin errores apreciables en el servidor de alojamiento compartido.
* El equipo de producto puede interpretar los datos sin depender de Google Analytics 4 para las consultas más habituales.
* Los datos pueden reutilizarse en una futura migración sin trabajos de transformación complejos.
