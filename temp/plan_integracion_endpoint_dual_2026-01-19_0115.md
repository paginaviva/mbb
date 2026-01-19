# Plan de Integración: Unificación de procesar-post.php (Formulario + API)

**Fecha:** 19 de enero, 2026  
**Archivo objetivo:** `gestion/procesar-post.php`  
**Objetivo:** Convertir el endpoint en dual mode (Formulario Web + API REST)

---

## 📊 **SITUACIÓN ACTUAL**

El archivo [gestion/procesar-post.php](gestion/procesar-post.php) actualmente:
- ✅ Funciona correctamente como procesador de formulario web
- ✅ Valida y parsea el bloque `[DATOS_DOCUMENTO]`
- ✅ Crea archivos PHP de posts exitosamente
- ❌ Solo responde con HTML/redirecciones (no soporta API)
- ❌ Función `mostrarErrores()` está acoplada a HTML
- ❌ No detecta el tipo de petición (web vs API)

**Objetivo:** Convertirlo en un endpoint dual que funcione para:
1. **Modo Formulario**: Mantener comportamiento actual (HTML/redirecciones)
2. **Modo API**: Responder con JSON (éxito/errores)

---

## 🎯 **ETAPAS DEL PLAN**

### **ETAPA 0: Preparación y Backup**
**Objetivo:** Crear punto de restauración y estructura base

**Acciones:**
1. Crear backup de `procesar-post.php` como `procesar-post.php.backup`
2. Verificar que `config.php` tiene todas las constantes necesarias
3. Documentar estado actual

**Entregables:**
- Archivo de backup creado
- Documento de verificación de constantes

**⚠️ Pruebas requeridas:** Ninguna (solo preparación)

---

### **ETAPA 1: Detección del Tipo de Petición**
**Objetivo:** Añadir mecanismo para identificar si la petición es API o formulario

**Acciones:**
1. Agregar función `esApiRequest()` al inicio del archivo que detecte:
   - Header `Accept: application/json`
   - Header `X-Requested-With: XMLHttpRequest`
   - Parámetro POST `api=1` o `api=true`
   - Content-Type `application/json`
2. Crear variable global `$isApiMode` basada en la detección
3. No modificar ninguna lógica existente aún

**Código a añadir (después de la línea 2):**
```php
// ============ DETECCIÓN DE MODO API ============

/**
 * Detecta si la petición proviene de un cliente API
 * @return bool true si es petición API, false si es formulario web
 */
function esApiRequest() {
    // Detectar por header Accept
    if (isset($_SERVER['HTTP_ACCEPT']) && 
        strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        return true;
    }
    
    // Detectar por X-Requested-With (AJAX)
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        return true;
    }
    
    // Detectar por parámetro explícito
    if (isset($_POST['api']) || isset($_GET['api'])) {
        return true;
    }
    
    // Detectar por Content-Type JSON
    if (isset($_SERVER['CONTENT_TYPE']) && 
        strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        return true;
    }
    
    return false;
}

// Variable global para modo de operación
$isApiMode = esApiRequest();
```

**Entregables:**
- Función `esApiRequest()` implementada
- Variable `$isApiMode` definida

**✅ Validación antes de continuar:**
- Agregar `var_dump($isApiMode);` temporal y probar desde formulario web (debe ser `false`)
- **🔴 PRUEBA EN SERVIDOR COMPARTIDO NECESARIA:** El usuario debe hacer una petición con `Accept: application/json` y verificar que `$isApiMode` sea `true`

**Comando de prueba:**
```bash
# Desde servidor compartido
curl -X POST https://www.meridiano.com/gestion/procesar-post.php \
  -H "Accept: application/json" \
  -F "datos_documento=test"
```

---

### **ETAPA 2: Refactorización de Respuestas de Error**
**Objetivo:** Separar la lógica de errores para soportar JSON y HTML

**Acciones:**
1. Crear nueva función `enviarRespuesta($exito, $mensaje, $datos, $errores)`
2. Mantener `mostrarErrores()` existente pero renombrarla a `mostrarErroresHTML()`
3. Hacer que `enviarRespuesta()` decida según `$isApiMode`
4. NO reemplazar aún las llamadas a `mostrarErrores()` en el código

**Código a añadir (después de la función `esApiRequest()`):**
```php
// ============ FUNCIÓN UNIFICADA DE RESPUESTA ============

/**
 * Envía respuesta según el modo de operación (API o Formulario)
 * @param bool $exito Indica si la operación fue exitosa
 * @param string $mensaje Mensaje descriptivo de la operación
 * @param array $datos Datos adicionales (para API mode)
 * @param array $errores Lista de errores (si los hay)
 */
function enviarRespuesta($exito, $mensaje = '', $datos = [], $errores = []) {
    global $isApiMode;
    
    if ($isApiMode) {
        // Modo API: responder con JSON
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code($exito ? 200 : 400);
        }
        
        echo json_encode([
            'success' => $exito,
            'message' => $mensaje,
            'data' => $datos,
            'errors' => $errores,
            'timestamp' => date('c')
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    } else {
        // Modo Formulario: comportamiento tradicional
        if (!$exito) {
            mostrarErroresHTML($errores);
        } else {
            // Redirigir al post creado
            if (isset($datos['url_post'])) {
                if (!headers_sent()) {
                    header('Location: ' . $datos['url_post']);
                }
                exit;
            }
        }
    }
}
```

**Cambio de nombre:** Renombrar función `mostrarErrores()` (línea ~275) a `mostrarErroresHTML()`

**Entregables:**
- Función `enviarRespuesta()` creada
- Función `mostrarErrores()` renombrada a `mostrarErroresHTML()`

**✅ Validación antes de continuar:**
- Verificar que el formulario web sigue funcionando normalmente (sin cambios visibles)
- **🔴 PRUEBA EN SERVIDOR COMPARTIDO NECESARIA:** No es necesaria aún, solo verificar compilación PHP sin errores

---

### **ETAPA 3: Soporte JSON para Entrada de Datos**
**Objetivo:** Permitir recibir `datos_documento` desde JSON body (además de form-data)

**Acciones:**
1. Modificar la validación inicial (líneas 5-8) para soportar JSON
2. Extraer `datos_documento` del JSON body si `Content-Type` es `application/json`

**Código a reemplazar (líneas 5-8):**
```php
// ============ VALIDACIÓN INICIAL Y OBTENCIÓN DE DATOS ============

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isApiMode) {
        enviarRespuesta(false, 'Método no permitido', [], ['Se requiere método POST']);
    } else {
        header('Location: crear-post-admin.php');
        exit;
    }
}

// Obtener datos según el tipo de petición
$datos_documento = null;

if ($isApiMode && isset($_SERVER['CONTENT_TYPE']) && 
    strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    // Petición API con JSON body
    $json_input = file_get_contents('php://input');
    $json_data = json_decode($json_input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        enviarRespuesta(false, 'JSON inválido', [], [
            'Error al parsear JSON: ' . json_last_error_msg()
        ]);
    }
    
    $datos_documento = $json_data['datos_documento'] ?? null;
} else {
    // Petición tradicional de formulario (form-data o x-www-form-urlencoded)
    $datos_documento = $_POST['datos_documento'] ?? null;
}

// Validar que se recibieron los datos
if (!$datos_documento) {
    if ($isApiMode) {
        enviarRespuesta(false, 'Datos faltantes', [], [
            'El campo datos_documento es requerido'
        ]);
    } else {
        header('Location: crear-post-admin.php');
        exit;
    }
}

$datos_raw = $datos_documento;
$errores = [];
```

**Entregables:**
- Soporte para recibir datos desde JSON body
- Validación mejorada con respuestas apropiadas según modo

**✅ Validación antes de continuar:**
- Probar formulario web (debe seguir funcionando)
- **🔴 PRUEBA EN SERVIDOR COMPARTIDO NECESARIA:** El usuario debe enviar una petición POST con JSON:

**Comando de prueba:**
```bash
# Test con JSON body
curl -X POST https://www.meridiano.com/gestion/procesar-post.php \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"datos_documento":"[DATOS_DOCUMENTO]\nNombreArchivoHTML: test.php\n..."}'

# Test con form-data (debe seguir funcionando)
curl -X POST https://www.meridiano.com/gestion/procesar-post.php \
  -H "Accept: application/json" \
  -F "datos_documento=@test_data.txt"
```

---

### **ETAPA 4: Reemplazo de Puntos de Error**
**Objetivo:** Convertir todas las llamadas a `mostrarErrores()` para usar `enviarRespuesta()`

**Acciones:**
1. Reemplazar todas las llamadas a `mostrarErrores($errores)` con llamadas a `enviarRespuesta()`
2. Total de reemplazos: 6 lugares

**Ubicaciones específicas y reemplazos:**

1. **Línea ~157** - Validación de campos obligatorios:
```php
if (!empty($errores)) {
    enviarRespuesta(false, 'Error en validación de campos', [], $errores);
}
```

2. **Línea ~194** - Directorio no existe:
```php
if (!is_dir($dir_posts)) {
    enviarRespuesta(false, 'Error de configuración', [], [
        "El directorio de posts no existe: {$dir_posts}"
    ]);
}
```

3. **Línea ~197** - Sin permisos de escritura:
```php
if (!is_writable($dir_posts)) {
    enviarRespuesta(false, 'Error de permisos', [], [
        "El directorio de posts no tiene permisos de escritura: {$dir_posts}"
    ]);
}
```

4. **Línea ~201** - Archivo ya existe:
```php
if (file_exists($ruta_post)) {
    enviarRespuesta(false, 'Archivo duplicado', [], [
        "El archivo {$nombre_archivo_php} ya existe."
    ]);
}
```

5. **Línea ~261** - file_put_contents falló:
```php
if (file_put_contents($ruta_post, $codigo_php) === false) {
    enviarRespuesta(false, 'Error al escribir archivo', [], [
        "No se pudo crear el archivo {$nombre_archivo_php}."
    ]);
}
```

6. **Línea ~263** - Exception al escribir:
```php
} catch (Exception $e) {
    enviarRespuesta(false, 'Error de sistema', [], [
        "Error al crear el archivo: " . $e->getMessage()
    ]);
}
```

**Entregables:**
- Todos los errores responden según el modo (HTML o JSON)

**✅ Validación antes de continuar:**
- Probar el formulario con datos inválidos (debe mostrar página de error HTML)
- **🔴 PRUEBA EN SERVIDOR COMPARTIDO NECESARIA:** Enviar petición API con datos inválidos y verificar respuesta JSON con errores

**Comandos de prueba:**
```bash
# Test 1: Campo faltante
curl -X POST https://www.meridiano.com/gestion/procesar-post.php \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"datos_documento":"[DATOS_DOCUMENTO]\nNombreArchivoHTML: test.php"}' | jq

# Test 2: Archivo duplicado
curl -X POST https://www.meridiano.com/gestion/procesar-post.php \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"datos_documento":"...archivo existente..."}' | jq
```

---

### **ETAPA 5: Manejo del Flujo de Éxito**
**Objetivo:** Hacer que el éxito responda con JSON en modo API

**Acciones:**
1. Modificar el bloque de éxito (después de crear el archivo, línea ~270)
2. Construir respuesta con datos del post creado
3. Usar `enviarRespuesta()` en vez de redirección directa

**Código a reemplazar (líneas 270-273):**
```php
// ============ RESPUESTA SEGÚN MODO ============

// Construir URL del post
$url_post = rtrim(SITE_URL, '/') . '/post/' . urlencode($nombre_archivo_php);

// Preparar datos de respuesta completos
$datos_respuesta = [
    'archivo' => $nombre_archivo_php,
    'url_post' => $url_post,
    'ruta_fisica' => $ruta_post,
    'titulo' => $titulo_visible,
    'subtitulo' => $subtitulo_visible,
    'autor' => $autor_visible,
    'fecha' => $fecha_visible,
    'categoria' => $category,
    'categorias' => $categories,
    'tags' => $tags,
    'imagen_fondo' => $imagen_fondo,
    'og_image' => $og_image_nombre,
    'twitter_image' => $twitter_image_nombre,
    'created_at' => date('Y-m-d H:i:s')
];

// Enviar respuesta según el modo
enviarRespuesta(true, 'Post creado exitosamente', $datos_respuesta, []);
```

**Entregables:**
- Respuesta unificada para éxito (redirección o JSON según modo)

**✅ Validación antes de continuar:**
- Crear un post desde el formulario web (debe redirigir al post)
- **🔴 PRUEBA EN SERVIDOR COMPARTIDO NECESARIA:** Crear un post vía API y verificar respuesta JSON con todos los datos del post creado

**Comando de prueba (con datos completos):**
```bash
curl -X POST https://www.meridiano.com/gestion/procesar-post.php \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d @test_post_completo.json | jq
```

**Ejemplo de respuesta esperada:**
```json
{
  "success": true,
  "message": "Post creado exitosamente",
  "data": {
    "archivo": "test-api-post.php",
    "url_post": "https://www.meridiano.com/post/test-api-post.php",
    "titulo": "Título del Post",
    "autor": "Redacción Meridiano BB",
    "categoria": "Categoría Principal",
    "tags": ["tag1", "tag2"],
    "created_at": "2026-01-19 01:15:00"
  },
  "errors": [],
  "timestamp": "2026-01-19T01:15:00-04:00"
}
```

---

### **ETAPA 6: Mejoras Finales y Hardening**
**Objetivo:** Añadir seguridad y mejoras opcionales

**Acciones:**
1. Añadir validación de `headers_sent()` antes de enviar headers
2. Añadir logging de errores críticos
3. Añadir headers CORS opcionales para API (si se necesita)
4. Manejar peticiones OPTIONS (preflight)
5. Mejorar mensajes de error con más contexto

**Código a añadir al inicio del archivo (después de include config.php):**
```php
// ============ CONFIGURACIÓN DE HEADERS Y CORS (OPCIONAL) ============

// Manejar peticiones OPTIONS para CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (!headers_sent()) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept, X-Requested-With');
        header('Access-Control-Max-Age: 86400'); // 24 horas
        http_response_code(200);
    }
    exit;
}

// Añadir CORS headers para API (si se requiere acceso desde otros dominios)
// NOTA: Comentar estas líneas si no se necesita acceso externo
if (esApiRequest()) {
    if (!headers_sent()) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type, Accept, X-Requested-With');
    }
}
```

**Mejora en función `enviarRespuesta()`:**
```php
function enviarRespuesta($exito, $mensaje = '', $datos = [], $errores = []) {
    global $isApiMode;
    
    // Verificar si los headers ya fueron enviados
    if (headers_sent($file, $line)) {
        error_log("ADVERTENCIA: Headers ya enviados en {$file}:{$line}");
        if ($isApiMode) {
            // Si es API y headers ya enviados, solo imprimir JSON
            echo json_encode([
                'success' => $exito,
                'message' => $mensaje,
                'data' => $datos,
                'errors' => $errores,
                'timestamp' => date('c'),
                'warning' => 'Headers already sent'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
    }
    
    if ($isApiMode) {
        // Modo API: responder con JSON
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($exito ? 200 : 400);
        
        // Log de errores críticos
        if (!$exito && !empty($errores)) {
            error_log("API Error en procesar-post.php: " . json_encode($errores));
        }
        
        echo json_encode([
            'success' => $exito,
            'message' => $mensaje,
            'data' => $datos,
            'errors' => $errores,
            'timestamp' => date('c')
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    } else {
        // Modo Formulario: comportamiento tradicional
        if (!$exito) {
            // Log de errores también en modo formulario
            error_log("Form Error en procesar-post.php: " . json_encode($errores));
            mostrarErroresHTML($errores);
        } else {
            // Redirigir al post creado
            if (isset($datos['url_post'])) {
                header('Location: ' . $datos['url_post']);
                exit;
            }
        }
    }
}
```

**Entregables:**
- Script robusto con validaciones adicionales
- Logging de errores implementado
- CORS configurado (opcional)
- Headers verificados antes de envío

**✅ Validación antes de continuar:**
- Hacer batería de pruebas completa (formulario y API)
- **🔴 PRUEBA EN SERVIDOR COMPARTIDO NECESARIA:** Pruebas exhaustivas de ambos modos

**Suite de pruebas:**
```bash
# Test 1: Formulario tradicional (browser)
# - Usar navegador para crear post

# Test 2: API con JSON
curl -X POST https://www.meridiano.com/gestion/procesar-post.php \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d @datos_post.json | jq

# Test 3: API con form-data
curl -X POST https://www.meridiano.com/gestion/procesar-post.php \
  -H "Accept: application/json" \
  -F "datos_documento=@datos.txt" | jq

# Test 4: CORS preflight
curl -X OPTIONS https://www.meridiano.com/gestion/procesar-post.php \
  -H "Origin: https://example.com" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type" \
  -v

# Test 5: Errores de validación
curl -X POST https://www.meridiano.com/gestion/procesar-post.php \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"datos_documento":"[DATOS_DOCUMENTO]\nNombreArchivoHTML: test.php"}' | jq

# Test 6: Archivo duplicado
# (usar datos de un post existente)
```

---

### **ETAPA 7: Documentación y Entrega**
**Objetivo:** Documentar el uso del endpoint dual

**Acciones:**
1. Crear archivo `docs/API_CREAR_POST.md` con documentación completa
2. Crear ejemplos de uso práctico
3. Crear script de pruebas automatizado
4. Actualizar comentarios en el código

**Contenido de la documentación:**

#### `docs/API_CREAR_POST.md`

```markdown
# API: Crear Post - Documentación

## Descripción
Endpoint dual que permite crear posts en Meridiano Blog tanto desde formulario web como desde API REST.

## URL
**POST** `https://www.meridiano.com/gestion/procesar-post.php`

## Modos de Operación

### 1. Modo Formulario (Web)
- **Content-Type**: `application/x-www-form-urlencoded` o `multipart/form-data`
- **Respuesta**: Redirección HTTP al post creado o página HTML de error

### 2. Modo API (REST)
- **Content-Type**: `application/json` o form-data con header `Accept: application/json`
- **Respuesta**: JSON con resultado de la operación

## Parámetros

### Campo Requerido
- `datos_documento` (string): Bloque completo [DATOS_DOCUMENTO] con todas las secciones

### Estructura del Bloque [DATOS_DOCUMENTO]

```
[DATOS_DOCUMENTO]
NombreArchivoHTML: nombre-del-post.php
UrlPublica: https://www.meridiano.com/nombre-del-post

[HEAD]
TituloDocumento: Título para SEO y metadatos
MetaDescription: Descripción para SEO
OgType: article
OgImage: https://www.meridiano.com/assets/img/imagen.webp
OgUrl: https://www.meridiano.com/post/nombre-del-post.php
OgSiteName: Meridiano Blog de Béisbol Caribeño
TwitterCard: summary_large_image
TwitterTitle: Título para Twitter
TwitterDescription: Descripción para Twitter
TwitterImage: https://www.meridiano.com/assets/img/imagen.webp
AutorMeta: Redacción Meridiano BB

[CABECERA_VISUAL]
ImagenFondo: assets/img/post-bg.webp
TituloVisible: Título visible del post
SubtituloVisible: Subtítulo del post
AutorVisible: Redacción Meridiano BB
FechaVisible: 19 de enero, 2026

[CONTENIDO]
<p>Contenido HTML del post aquí...</p>

[CATEGORIAS]
Categoría Principal, Categoría Secundaria

[ETIQUETAS]
tag1, tag2, tag3
```

## Ejemplos de Uso

### Ejemplo 1: Desde JavaScript (Fetch API)

```javascript
const datosDocumento = `[DATOS_DOCUMENTO]
NombreArchivoHTML: post-ejemplo-api.php
...`;

fetch('https://www.meridiano.com/gestion/procesar-post.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    datos_documento: datosDocumento
  })
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    console.log('Post creado:', data.data.url_post);
  } else {
    console.error('Errores:', data.errors);
  }
});
```

### Ejemplo 2: Desde cURL (Bash)

```bash
# Con JSON body
curl -X POST https://www.meridiano.com/gestion/procesar-post.php \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "datos_documento": "[DATOS_DOCUMENTO]\n..."
  }' | jq

# Con form-data
curl -X POST https://www.meridiano.com/gestion/procesar-post.php \
  -H "Accept: application/json" \
  -F "datos_documento=@datos_post.txt" | jq
```

### Ejemplo 3: Desde Python

```python
import requests
import json

datos_documento = """[DATOS_DOCUMENTO]
NombreArchivoHTML: post-python.php
..."""

response = requests.post(
    'https://www.meridiano.com/gestion/procesar-post.php',
    json={'datos_documento': datos_documento},
    headers={'Accept': 'application/json'}
)

result = response.json()
if result['success']:
    print(f"Post creado: {result['data']['url_post']}")
else:
    print(f"Errores: {result['errors']}")
```

### Ejemplo 4: Desde PHP

```php
<?php
$datosDocumento = "[DATOS_DOCUMENTO]\n...";

$ch = curl_init('https://www.meridiano.com/gestion/procesar-post.php');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['datos_documento' => $datosDocumento]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json'
    ],
    CURLOPT_RETURNTRANSFER => true
]);

$response = curl_exec($ch);
$result = json_decode($response, true);

if ($result['success']) {
    echo "Post creado: " . $result['data']['url_post'];
} else {
    echo "Errores: " . implode(', ', $result['errors']);
}
```

## Respuestas

### Respuesta Exitosa (HTTP 200)

```json
{
  "success": true,
  "message": "Post creado exitosamente",
  "data": {
    "archivo": "nombre-del-post.php",
    "url_post": "https://www.meridiano.com/post/nombre-del-post.php",
    "ruta_fisica": "/path/to/post/nombre-del-post.php",
    "titulo": "Título del Post",
    "subtitulo": "Subtítulo del post",
    "autor": "Redacción Meridiano BB",
    "fecha": "19 de enero, 2026",
    "categoria": "Categoría Principal",
    "categorias": ["Categoría Principal", "Categoría Secundaria"],
    "tags": ["tag1", "tag2", "tag3"],
    "imagen_fondo": "assets/img/post-bg.webp",
    "og_image": "imagen.webp",
    "twitter_image": "imagen.webp",
    "created_at": "2026-01-19 01:15:00"
  },
  "errors": [],
  "timestamp": "2026-01-19T01:15:00-04:00"
}
```

### Respuesta con Error (HTTP 400)

```json
{
  "success": false,
  "message": "Error en validación de campos",
  "data": [],
  "errors": [
    "TituloDocumento es obligatorio.",
    "MetaDescription es obligatorio.",
    "AutorMeta es obligatorio."
  ],
  "timestamp": "2026-01-19T01:15:00-04:00"
}
```

## Códigos de Estado HTTP

| Código | Significado | Descripción |
|--------|-------------|-------------|
| 200 | OK | Post creado exitosamente |
| 400 | Bad Request | Error de validación o datos faltantes |
| 405 | Method Not Allowed | Método diferente a POST |
| 500 | Internal Server Error | Error del servidor |

## Tipos de Errores

### Errores de Validación
- Campos obligatorios faltantes (11 campos requeridos)
- Formato de datos incorrecto

### Errores de Sistema
- Directorio de posts no existe
- Sin permisos de escritura
- Archivo ya existe
- Error al escribir archivo físico

### Errores de Formato
- JSON inválido (solo en modo API)
- Estructura de [DATOS_DOCUMENTO] incorrecta

## Notas Importantes

1. **Después de crear el post**: Debes ejecutar el "Manifest Generator" desde el panel de administración para que el post aparezca en los listados.

2. **Imágenes**: Las imágenes referenciadas deben existir en `assets/img/` antes de crear el post.

3. **Nombres de archivo**: Se recomienda usar nombres en minúsculas con guiones, sin espacios ni caracteres especiales.

4. **Fecha**: El campo `FechaVisible` es requerido. Se recomienda usar formato: "DD de mes, YYYY"

5. **CORS**: Si necesitas acceder desde un dominio diferente, verifica que los headers CORS estén habilitados.

## Seguridad

- El endpoint valida el método POST
- No hay autenticación implementada (considerar añadir si se expone públicamente)
- Los errores se registran en el log de PHP del servidor
- Se valida la existencia del directorio y permisos antes de escribir

## Solución de Problemas

### "Headers already sent"
Verifica que no haya salida antes del procesamiento (espacios, BOM, etc.)

### "JSON inválido"
Verifica que el JSON esté correctamente formateado y escapado

### "Archivo ya existe"
Verifica que el `NombreArchivoHTML` sea único

### "Sin permisos de escritura"
Verifica los permisos del directorio `post/` (debe tener permisos 755 o 775)
```

**Entregables:**
- Documentación completa de la API
- Ejemplos en múltiples lenguajes
- Guía de solución de problemas
- Comentarios actualizados en el código fuente

**✅ Validación Final:**
- Documentación completa y clara
- Ejemplos probados y funcionales
- Código comentado adecuadamente

---

## 📋 **CHECKLIST DE VALIDACIÓN POR ETAPA**

| Etapa | Descripción | Validación Local | Prueba Servidor | Estado |
|-------|-------------|------------------|-----------------|---------|
| 0 | Preparación y Backup | ✅ Backup creado | ❌ No necesaria | ⏳ Pendiente |
| 1 | Detección del Tipo de Petición | ✅ Detección funciona | 🔴 **Requerida** | ⏳ Pendiente |
| 2 | Refactorización de Respuestas | ✅ Compilación OK | ❌ No necesaria | ⏳ Pendiente |
| 3 | Soporte JSON para Entrada | ✅ Formulario funciona | 🔴 **Requerida** | ⏳ Pendiente |
| 4 | Reemplazo de Puntos de Error | ✅ Errores HTML OK | 🔴 **Requerida** | ⏳ Pendiente |
| 5 | Manejo del Flujo de Éxito | ✅ Redirección OK | 🔴 **Requerida** | ⏳ Pendiente |
| 6 | Mejoras Finales y Hardening | ✅ Todo funciona | 🔴 **Requerida** | ⏳ Pendiente |
| 7 | Documentación y Entrega | ✅ Docs completas | ❌ No necesaria | ⏳ Pendiente |

---

## 🎯 **RESUMEN DE CAMBIOS POR ETAPA**

### Cambios en el código

| Etapa | Líneas Afectadas | Tipo de Cambio | Complejidad |
|-------|-----------------|----------------|-------------|
| 1 | Después línea 2 | Añadir función | 🟢 Baja |
| 2 | Después línea 2 + Línea 275 | Añadir función + renombrar | 🟢 Baja |
| 3 | Líneas 5-10 | Reemplazar validación | 🟡 Media |
| 4 | 6 ubicaciones | Reemplazar llamadas | 🟡 Media |
| 5 | Líneas 270-273 | Reemplazar éxito | 🟡 Media |
| 6 | Inicio + función | Añadir validaciones | 🟡 Media |
| 7 | Comentarios | Documentar | 🟢 Baja |

---

## 🚀 **INSTRUCCIONES DE INICIO**

Para comenzar la implementación:

1. **Revisar este plan completo**
2. **Confirmar inicio de ETAPA 0**
3. **Proceder etapa por etapa**
4. **Validar cada etapa antes de continuar**
5. **Realizar pruebas en servidor compartido cuando se indique**

---

## 📝 **NOTAS ADICIONALES**

### Beneficios de este enfoque
- ✅ Sin duplicación de código
- ✅ Mantenimiento centralizado
- ✅ Retrocompatibilidad con formulario web
- ✅ API REST moderna
- ✅ Respuestas estructuradas JSON
- ✅ Logging de errores
- ✅ CORS configurable

### Consideraciones de seguridad
- ⚠️ No hay autenticación implementada (considerar añadir tokens/API keys si se expone públicamente)
- ⚠️ Validar que solo usuarios autorizados tengan acceso al endpoint
- ⚠️ Considerar rate limiting para prevenir abuso

### Mantenimiento futuro
- El código queda preparado para añadir autenticación fácilmente
- Se puede extender para soportar otros formatos (XML, YAML)
- La estructura permite añadir más validaciones sin modificar el flujo principal

---

**Documento generado:** 19 de enero, 2026  
**Versión:** 1.0  
**Estado:** Listo para implementación
