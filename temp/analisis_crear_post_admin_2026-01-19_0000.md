# Análisis y propuesta de API para crear-post-admin.php

## 1. Resumen ejecutivo y flujo de proceso

### ¿Cómo funciona `crear-post-admin.php`?

- Es un formulario web que permite crear un nuevo post en el sistema de Meridiano Blog.
- El usuario debe pegar el bloque completo `[DATOS_DOCUMENTO]` en un textarea y enviar el formulario.
- El formulario hace un POST a `procesar-post.php`.
- `procesar-post.php`:
  - Valida que la petición sea POST y que exista el campo `datos_documento`.
  - Parsea el bloque `[DATOS_DOCUMENTO]` extrayendo secciones y campos requeridos.
  - Realiza validaciones de campos obligatorios.
  - Si hay errores, muestra una página de error amigable.
  - Si todo es correcto, genera el archivo PHP del post en la ruta correspondiente, con los metadatos y contenido extraídos.
  - Redirige al usuario al nuevo post creado.

### Flujo resumido

1. Usuario accede a `crear-post-admin.php` (formulario web).
2. Pega el bloque `[DATOS_DOCUMENTO]` y envía.
3. El backend (`procesar-post.php`) procesa, valida y crea el archivo PHP del post.
4. Si hay errores, se muestran; si es exitoso, se redirige al nuevo post.

## 2. ¿Cómo duplicar como API (`crear-post-admin-api.php`)?

- Se puede crear un endpoint tipo API que reciba una petición POST (idealmente JSON, pero puede ser `application/x-www-form-urlencoded` o `multipart/form-data` para compatibilidad).
- El endpoint debe recibir el contenido del campo `datos_documento` (igual que el formulario).
- El flujo de procesamiento puede ser el mismo que en `procesar-post.php`:
  - Validar y parsear el bloque.
  - Crear el archivo.
  - En vez de redirigir, devolver una respuesta JSON con el resultado (éxito o errores).
- Se puede reutilizar casi todo el código de parsing y validación.

## 3. ¿Se puede unificar el endpoint (formulario + API)?

**Sí, es posible mantener solo `crear-post-admin.php` (o mejor, solo `procesar-post.php`) y que funcione como formulario y como API, sin duplicar código.**

### Estrategia:
- El backend (`procesar-post.php`) debe detectar el tipo de petición:
  - Si la petición es desde un navegador (formulario), responder con HTML/redirección.
  - Si la petición es AJAX/Fetch/API (por ejemplo, con header `Accept: application/json` o `X-Requested-With: XMLHttpRequest`), responder con JSON.
- Esto se puede hacer revisando los headers:
  - `Accept: application/json`
  - `X-Requested-With: XMLHttpRequest`
- Así, el mismo endpoint sirve para ambos casos, evitando duplicidad y facilitando el mantenimiento.

### Cambios mínimos sugeridos:
- Modificar `procesar-post.php` para:
  - Detectar si la petición espera JSON.
  - Si es así, devolver errores o éxito en formato JSON.
  - Si no, mantener el flujo actual (HTML/redirección).

---

## 4. Recomendaciones
- Centralizar la lógica de procesamiento en un solo archivo (`procesar-post.php`).
- Permitir que el frontend (formulario) y clientes API usen el mismo endpoint.
- Documentar el uso del endpoint para ambos casos.

---

**Archivo generado automáticamente por GitHub Copilot.**
