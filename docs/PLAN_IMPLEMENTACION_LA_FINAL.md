# Plan de Implementación: Cambio de "Round Robin" a "La Final"

**Fecha de creación:** 3 de enero de 2026  
**Proyecto:** MBB_20251124_H (Meridiano Béisbol Blog)  
**Objetivo:** Adaptar la página de inicio para mostrar jornadas de "La Final" en lugar de "Round Robin"  
**Estado:** Pendiente de ejecución

---

## 📋 CONTEXTO DEL CAMBIO

### Situación Actual (Después de implementación Round Robin)
La página de inicio muestra actualmente:
- **Bloque A:** Jornadas del Round Robin con etiqueta `"Resumen Diario Round Robin"`
  - Sección principal: "Round Robin Ayer" (2 posts mostrando títulos)
  - Sección secundaria: "Otros Juegos Round Robin" (4 posts mostrando títulos)
- **Bloque B+C:** "Lo más reciente" (10 artículos del Kanban)
- **Bloque D:** "Resúmenes Semanales LVBP"
- **Bloque E:** "Historias destacadas LVBP"

### Nueva Funcionalidad Solicitada
Cambiar el Bloque A para mostrar jornadas de "La Final":
- **Nueva etiqueta objetivo:** `"Resumen Diario La Final"` 
- **Estructura:** Mantener el mismo formato (2 posts + 4 adicionales, mostrando títulos)
- **Títulos visibles:**
  - "La Final Ayer" (en lugar de "Round Robin Ayer")
  - "Otros Juegos La Final" (en lugar de "Otros Juegos Round Robin")

### ⚠️ NOTA CRÍTICA
La etiqueta `"Resumen Diario La Final"` **NO EXISTE AÚN** en el manifest de posts. 

**Comportamiento esperado post-implementación:**
- El Bloque A estará **vacío** (no mostrará posts) hasta que se publiquen artículos con la etiqueta correspondiente
- Los demás bloques (B, C, D, E) continuarán funcionando normalmente
- Una vez se creen posts con etiqueta `"Resumen Diario La Final"`, estos aparecerán automáticamente en el Bloque A
- **No se requiere ninguna acción adicional** cuando se agreguen los posts con la etiqueta

---

## 🎯 ARCHIVOS AFECTADOS

### Archivos que REQUIEREN modificación:

| # | Archivo | Ruta Completa | Tipo de Cambio |
|---|---------|---------------|----------------|
| 1 | `home_data_provider.php` | `/includes/home_data_provider.php` | Cambio de etiqueta de filtrado |
| 2 | `index_desktop.php` | `/index_desktop.php` | Cambio de títulos y IDs HTML |
| 3 | `index_mobile.php` | `/index_mobile.php` | Cambio de títulos y IDs HTML |

### Archivos que NO requieren cambios:

| Archivo | Razón |
|---------|-------|
| `gestion/kanban_destacados.php` | Ya está configurado para 10 artículos (cambio previo) |
| `css/home_desktop.css` | No hay estilos específicos de "Round Robin" |
| `css/home_mobile.css` | No hay estilos específicos de "Round Robin" |
| `posts_manifest.php` | Se actualizará automáticamente cuando se creen posts con la nueva etiqueta |

---

## 📦 PASO 1: CREAR RESPALDO DE SEGURIDAD

**IMPORTANTE:** Antes de realizar cualquier cambio, crear copias de seguridad de los archivos a modificar.

### Comandos a ejecutar:

```bash
# Crear directorio de respaldo con timestamp
mkdir -p /workspaces/mbb/legado/pag_inicio_la_final_20260103

# Copiar archivos actuales (Round Robin) al respaldo
cp /workspaces/mbb/includes/home_data_provider.php /workspaces/mbb/legado/pag_inicio_la_final_20260103/
cp /workspaces/mbb/index_desktop.php /workspaces/mbb/legado/pag_inicio_la_final_20260103/
cp /workspaces/mbb/index_mobile.php /workspaces/mbb/legado/pag_inicio_la_final_20260103/

# Verificar que los archivos se copiaron correctamente
ls -lh /workspaces/mbb/legado/pag_inicio_la_final_20260103/
```

### Resultado esperado:
```
home_data_provider.php (tamaño: ~12KB)
index_desktop.php (tamaño: ~8.7KB)
index_mobile.php (tamaño: ~8.5KB)
```

---

## 🔧 PASO 2: IMPLEMENTAR CAMBIOS

### 2.1. Modificar `includes/home_data_provider.php`

**Ubicación del cambio:** Líneas aproximadas 227-243

**BUSCAR Y REEMPLAZAR:**

#### **CÓDIGO ACTUAL (Round Robin):**
```php
    // --- Lógica Bloque A (Round Robin) ---
    // Obtener posts con etiqueta "resumen diario round robin"
    // - latest_two: Los 2 más recientes para "Round Robin Ayer"
    // - others: Los siguientes 4 para "Otros Juegos Round Robin"
    $round_robin_summaries = [];
    foreach ($sorted_posts as $slug => $post) {
        $post = enrich_post($post, $slug);
        if (isset($post['tags']) && is_array($post['tags'])) {
            $tags_norm = array_map(function($t) { 
                return mb_strtolower(trim($t), 'UTF-8'); 
            }, $post['tags']);
            
            if (in_array('resumen diario round robin', $tags_norm)) {
                $round_robin_summaries[] = $post;
            }
        }
    }
    
    // Separar en 2 grupos
    $data['block_a']['latest_two'] = array_slice($round_robin_summaries, 0, 2);
    $data['block_a']['others'] = array_slice($round_robin_summaries, 2, 4);
```

#### **CÓDIGO NUEVO (La Final):**
```php
    // --- Lógica Bloque A (La Final) ---
    // Obtener posts con etiqueta "resumen diario la final"
    // - latest_two: Los 2 más recientes para "La Final Ayer"
    // - others: Los siguientes 4 para "Otros Juegos La Final"
    $la_final_summaries = [];
    foreach ($sorted_posts as $slug => $post) {
        $post = enrich_post($post, $slug);
        if (isset($post['tags']) && is_array($post['tags'])) {
            $tags_norm = array_map(function($t) { 
                return mb_strtolower(trim($t), 'UTF-8'); 
            }, $post['tags']);
            
            if (in_array('resumen diario la final', $tags_norm)) {
                $la_final_summaries[] = $post;
            }
        }
    }
    
    // Separar en 2 grupos
    $data['block_a']['latest_two'] = array_slice($la_final_summaries, 0, 2);
    $data['block_a']['others'] = array_slice($la_final_summaries, 2, 4);
```

**CAMBIOS REALIZADOS:**
1. ✅ Comentario: "Round Robin" → "La Final"
2. ✅ Nombre de variable: `$round_robin_summaries` → `$la_final_summaries`
3. ✅ Etiqueta de filtrado: `'resumen diario round robin'` → `'resumen diario la final'`
4. ✅ Comentarios descriptivos actualizados

---

### 2.2. Modificar `index_desktop.php`

**Ubicación del cambio:** Líneas aproximadas 100-135

**BUSCAR Y REEMPLAZAR:**

#### **CÓDIGO ACTUAL (Round Robin):**
```html
    <!-- BLOQUE A: Round Robin Ayer -->
    <?php if (!empty($home_data['block_a']['latest_two']) || !empty($home_data['block_a']['others'])): ?>
        <section id="block-round-robin-desktop" class="home-block-desktop">

            <!-- 2 Últimos Posts -->
            <?php if (!empty($home_data['block_a']['latest_two'])): ?>
                <div class="block-title-desktop">Round Robin Ayer</div>
                <ul class="post-list-small-desktop">
                    <?php foreach ($home_data['block_a']['latest_two'] as $post): ?>
                        <li>
                            <a href="<?php echo $post['url']; ?>" target="_blank"
                                onclick="trackHomeClick('yesterday_latest', '<?php echo $post['id']; ?>', '<?php echo addslashes($post['title']); ?>', '<?php echo $post['category']; ?>', 0)">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <!-- 4 Posts Adicionales -->
            <?php if (!empty($home_data['block_a']['others'])): ?>
                <div class="block-title-desktop mt-4">Otros Juegos Round Robin</div>
                <ul class="post-list-small-desktop">
                    <?php foreach ($home_data['block_a']['others'] as $post): ?>
                        <li>
                            <a href="<?php echo $post['url']; ?>" target="_blank"
                                onclick="trackHomeClick('yesterday_others', '<?php echo $post['id']; ?>', '<?php echo addslashes($post['title']); ?>', '<?php echo $post['category']; ?>', 0)">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

        </section>
    <?php endif; ?>
```

#### **CÓDIGO NUEVO (La Final):**
```html
    <!-- BLOQUE A: La Final Ayer -->
    <?php if (!empty($home_data['block_a']['latest_two']) || !empty($home_data['block_a']['others'])): ?>
        <section id="block-la-final-desktop" class="home-block-desktop">

            <!-- 2 Últimos Posts -->
            <?php if (!empty($home_data['block_a']['latest_two'])): ?>
                <div class="block-title-desktop">La Final Ayer</div>
                <ul class="post-list-small-desktop">
                    <?php foreach ($home_data['block_a']['latest_two'] as $post): ?>
                        <li>
                            <a href="<?php echo $post['url']; ?>" target="_blank"
                                onclick="trackHomeClick('yesterday_latest', '<?php echo $post['id']; ?>', '<?php echo addslashes($post['title']); ?>', '<?php echo $post['category']; ?>', 0)">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <!-- 4 Posts Adicionales -->
            <?php if (!empty($home_data['block_a']['others'])): ?>
                <div class="block-title-desktop mt-4">Otros Juegos La Final</div>
                <ul class="post-list-small-desktop">
                    <?php foreach ($home_data['block_a']['others'] as $post): ?>
                        <li>
                            <a href="<?php echo $post['url']; ?>" target="_blank"
                                onclick="trackHomeClick('yesterday_others', '<?php echo $post['id']; ?>', '<?php echo addslashes($post['title']); ?>', '<?php echo $post['category']; ?>', 0)">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

        </section>
    <?php endif; ?>
```

**CAMBIOS REALIZADOS:**
1. ✅ Comentario HTML: `"Round Robin Ayer"` → `"La Final Ayer"`
2. ✅ ID de sección: `block-round-robin-desktop` → `block-la-final-desktop`
3. ✅ Título principal: `"Round Robin Ayer"` → `"La Final Ayer"`
4. ✅ Subtítulo: `"Otros Juegos Round Robin"` → `"Otros Juegos La Final"`

**NOTA:** Los eventos de tracking (`trackHomeClick`) y las clases CSS **NO SE MODIFICAN** porque son genéricos.

---

### 2.3. Modificar `index_mobile.php`

**Ubicación de cambios:** 
- Primera sección: Líneas aproximadas 98-115
- Segunda sección: Líneas aproximadas 179-194

#### **CAMBIO 1: Primera sección (Los 2 posts principales)**

**CÓDIGO ACTUAL (Round Robin):**
```html
    <!-- BLOQUE A: Round Robin Ayer (2 posts) -->
    <?php if (!empty($home_data['block_a']['latest_two'])): ?>
        <section id="block-round-robin-mobile" class="home-block-mobile">
            <div class="block-title-mobile">Round Robin Ayer</div>
            <ul class="post-list-small-mobile">
                <?php foreach ($home_data['block_a']['latest_two'] as $post): ?>
                    <li>
                        <a href="<?php echo $post['url']; ?>" target="_blank"
                            onclick="trackHomeClick('yesterday_latest', '<?php echo $post['id']; ?>', '<?php echo addslashes($post['title']); ?>', '<?php echo $post['category']; ?>', 0)">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
```

**CÓDIGO NUEVO (La Final):**
```html
    <!-- BLOQUE A: La Final Ayer (2 posts) -->
    <?php if (!empty($home_data['block_a']['latest_two'])): ?>
        <section id="block-la-final-mobile" class="home-block-mobile">
            <div class="block-title-mobile">La Final Ayer</div>
            <ul class="post-list-small-mobile">
                <?php foreach ($home_data['block_a']['latest_two'] as $post): ?>
                    <li>
                        <a href="<?php echo $post['url']; ?>" target="_blank"
                            onclick="trackHomeClick('yesterday_latest', '<?php echo $post['id']; ?>', '<?php echo addslashes($post['title']); ?>', '<?php echo $post['category']; ?>', 0)">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
```

**CAMBIOS REALIZADOS:**
1. ✅ Comentario HTML: `"Round Robin Ayer (2 posts)"` → `"La Final Ayer (2 posts)"`
2. ✅ ID de sección: `block-round-robin-mobile` → `block-la-final-mobile`
3. ✅ Título visible: `"Round Robin Ayer"` → `"La Final Ayer"`

---

#### **CAMBIO 2: Segunda sección (Los 4 posts adicionales)**

**UBICACIÓN:** Esta sección aparece más abajo en el archivo, después del "Bloque D: Resúmenes Semanales"

**CÓDIGO ACTUAL (Round Robin):**
```html
    <!-- BLOQUE: Otros Juegos Round Robin -->
    <?php if (!empty($home_data['block_a']['others'])): ?>
        <section id="block-other-round-robin-mobile" class="home-block-mobile">
            <div class="block-title-mobile">Otros Juegos Round Robin</div>
            <ul class="post-list-small-mobile">
                <?php foreach ($home_data['block_a']['others'] as $post): ?>
                    <li>
                        <a href="<?php echo $post['url']; ?>" target="_blank"
                            onclick="trackHomeClick('yesterday_others', '<?php echo $post['id']; ?>', '<?php echo addslashes($post['title']); ?>', '<?php echo $post['category']; ?>', 0)">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
```

**CÓDIGO NUEVO (La Final):**
```html
    <!-- BLOQUE: Otros Juegos La Final -->
    <?php if (!empty($home_data['block_a']['others'])): ?>
        <section id="block-other-la-final-mobile" class="home-block-mobile">
            <div class="block-title-mobile">Otros Juegos La Final</div>
            <ul class="post-list-small-mobile">
                <?php foreach ($home_data['block_a']['others'] as $post): ?>
                    <li>
                        <a href="<?php echo $post['url']; ?>" target="_blank"
                            onclick="trackHomeClick('yesterday_others', '<?php echo $post['id']; ?>', '<?php echo addslashes($post['title']); ?>', '<?php echo $post['category']; ?>', 0)">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
```

**CAMBIOS REALIZADOS:**
1. ✅ Comentario HTML: `"Otros Juegos Round Robin"` → `"Otros Juegos La Final"`
2. ✅ ID de sección: `block-other-round-robin-mobile` → `block-other-la-final-mobile`
3. ✅ Título visible: `"Otros Juegos Round Robin"` → `"Otros Juegos La Final"`

---

## ✅ PASO 3: VERIFICACIÓN POST-IMPLEMENTACIÓN

### 3.1. Verificación de Sintaxis

**Comandos para verificar que no hay errores de sintaxis PHP:**

```bash
# Verificar sintaxis de home_data_provider.php
php -l /workspaces/mbb/includes/home_data_provider.php

# Verificar sintaxis de index_desktop.php
php -l /workspaces/mbb/index_desktop.php

# Verificar sintaxis de index_mobile.php
php -l /workspaces/mbb/index_mobile.php
```

**Resultado esperado:** 
```
No syntax errors detected in /workspaces/mbb/includes/home_data_provider.php
No syntax errors detected in /workspaces/mbb/index_desktop.php
No syntax errors detected in /workspaces/mbb/index_mobile.php
```

---

### 3.2. Verificación de Cambios Aplicados

**Comandos para confirmar que los cambios se aplicaron correctamente:**

```bash
echo "=== VERIFICACIÓN DE CAMBIOS ==="
echo ""
echo "1. Verificar etiqueta 'resumen diario la final' en home_data_provider.php:"
grep -n "resumen diario la final" /workspaces/mbb/includes/home_data_provider.php

echo ""
echo "2. Verificar variable \$la_final_summaries:"
grep -n "la_final_summaries" /workspaces/mbb/includes/home_data_provider.php

echo ""
echo "3. Verificar título 'La Final Ayer' en desktop:"
grep -n "La Final Ayer" /workspaces/mbb/index_desktop.php

echo ""
echo "4. Verificar título 'La Final Ayer' en mobile:"
grep -n "La Final Ayer" /workspaces/mbb/index_mobile.php

echo ""
echo "5. Verificar 'Otros Juegos La Final' en desktop:"
grep -n "Otros Juegos La Final" /workspaces/mbb/index_desktop.php

echo ""
echo "6. Verificar 'Otros Juegos La Final' en mobile:"
grep -n "Otros Juegos La Final" /workspaces/mbb/index_mobile.php

echo ""
echo "7. Verificar IDs de sección actualizados:"
grep -n "block-la-final" /workspaces/mbb/index_desktop.php
grep -n "block-la-final" /workspaces/mbb/index_mobile.php
```

---

### 3.3. Verificación Visual en Navegador

**IMPORTANTE:** Después de implementar los cambios, verificar visualmente la página de inicio.

**Pasos:**
1. Abrir en navegador: `https://www.meridiano.com/` (o URL del entorno de desarrollo)
2. Verificar que **NO** aparecen títulos de "Round Robin"
3. Verificar que aparecen los nuevos títulos:
   - "La Final Ayer"
   - "Otros Juegos La Final"
4. Verificar que estas secciones están **vacías** (sin posts) → Comportamiento esperado porque la etiqueta no existe aún
5. Verificar que los demás bloques funcionan normalmente:
   - "Lo más reciente" muestra 10 artículos
   - "Resúmenes Semanales LVBP" funciona
   - "Historias destacadas LVBP" funciona

---

## 📊 RESUMEN DE CAMBIOS

### Cambios de Texto (Títulos Visibles):
| Antes | Después |
|-------|---------|
| "Round Robin Ayer" | "La Final Ayer" |
| "Otros Juegos Round Robin" | "Otros Juegos La Final" |

### Cambios Técnicos (Código):
| Elemento | Antes | Después |
|----------|-------|---------|
| Etiqueta de filtrado | `'resumen diario round robin'` | `'resumen diario la final'` |
| Variable PHP | `$round_robin_summaries` | `$la_final_summaries` |
| ID sección desktop | `block-round-robin-desktop` | `block-la-final-desktop` |
| ID sección mobile principal | `block-round-robin-mobile` | `block-la-final-mobile` |
| ID sección mobile secundaria | `block-other-round-robin-mobile` | `block-other-la-final-mobile` |

### Elementos que NO cambian:
- ✅ Estructura de datos: `$home_data['block_a']['latest_two']` y `$home_data['block_a']['others']`
- ✅ Funciones de tracking: `trackHomeClick('yesterday_latest', ...)` y `trackHomeClick('yesterday_others', ...)`
- ✅ Clases CSS: `home-block-desktop`, `post-list-small-desktop`, etc.
- ✅ Lógica de filtrado: 2 posts más recientes + 4 adicionales
- ✅ Formato de visualización: Títulos de artículos (sin fechas)

---

## 🔄 ROLLBACK (En caso de problemas)

Si después de la implementación se detectan problemas, restaurar desde el respaldo:

```bash
# Restaurar archivos originales (Round Robin)
cp /workspaces/mbb/legado/pag_inicio_la_final_20260103/home_data_provider.php /workspaces/mbb/includes/
cp /workspaces/mbb/legado/pag_inicio_la_final_20260103/index_desktop.php /workspaces/mbb/
cp /workspaces/mbb/legado/pag_inicio_la_final_20260103/index_mobile.php /workspaces/mbb/

# Verificar que se restauraron
echo "Archivos restaurados desde respaldo"
ls -lh /workspaces/mbb/includes/home_data_provider.php
ls -lh /workspaces/mbb/index_desktop.php
ls -lh /workspaces/mbb/index_mobile.php
```

---

## 📝 NOTAS ADICIONALES PARA FUTURAS IMPLEMENTACIONES

### Patrón de cambio para otras etapas de la temporada

Este plan puede adaptarse fácilmente para otras fases de la temporada. Siguiendo el mismo patrón:

**Para "Serie del Caribe":**
- Etiqueta: `'resumen diario serie del caribe'`
- Títulos: "Serie del Caribe Ayer" / "Otros Juegos Serie del Caribe"
- Variable: `$serie_caribe_summaries`
- IDs: `block-serie-caribe-desktop`, `block-serie-caribe-mobile`

**Para "Round Robin" (volver atrás):**
- Etiqueta: `'resumen diario round robin'`
- Títulos: "Round Robin Ayer" / "Otros Juegos Round Robin"
- Variable: `$round_robin_summaries`
- IDs: `block-round-robin-desktop`, `block-round-robin-mobile`

**Para "Temporada Regular":**
- Etiqueta: `'resumen diario'`
- Títulos: "La jornada de ayer" / "Otras Jornadas"
- Variable: `$daily_summaries`
- IDs: `block-yesterday-desktop`, `block-yesterday-mobile`

---

## ✅ CHECKLIST DE EJECUCIÓN

**Antes de implementar:**
- [ ] Leer este documento completo
- [ ] Verificar que se entienden todos los cambios
- [ ] Confirmar que NO hay otros cambios pendientes que puedan generar conflictos

**Durante la implementación:**
- [ ] Crear respaldo de seguridad (Paso 1)
- [ ] Modificar `includes/home_data_provider.php` (Paso 2.1)
- [ ] Modificar `index_desktop.php` (Paso 2.2)
- [ ] Modificar `index_mobile.php` - Primera sección (Paso 2.3 - Cambio 1)
- [ ] Modificar `index_mobile.php` - Segunda sección (Paso 2.3 - Cambio 2)

**Después de implementar:**
- [ ] Verificar sintaxis PHP (Paso 3.1)
- [ ] Verificar cambios aplicados con grep (Paso 3.2)
- [ ] Verificar visualmente en navegador (Paso 3.3)
- [ ] Confirmar que Bloque A está vacío (comportamiento esperado)
- [ ] Confirmar que otros bloques funcionan normalmente

**En caso de problemas:**
- [ ] Ejecutar rollback desde respaldo
- [ ] Documentar el problema encontrado
- [ ] Revisar este plan antes de intentar nuevamente

---

## 📞 INFORMACIÓN DE CONTACTO Y REFERENCIAS

**Documentación relacionada:**
- `docs/BASE_COGNITIVA_ANALISIS_TECNICO_PROYECTO.md` - Análisis técnico completo del proyecto
- `docs/AUDITORIA_TECNICA.md` - Mapa estructural del sistema
- `docs/BITACORA_CAMBIOS_PROYECTO.md` - Historial de cambios

**Archivos de respaldo previos:**
- `legado/pag_inicio_temp_regular/` - Respaldo antes de cambio a Round Robin (3 ene 2026)
- `legado/pag_inicio_la_final_20260103/` - Respaldo antes de cambio a La Final (actual)

**Repositorio:**
- Owner: paginaviva
- Repo: mbb
- Branch: main

---

**Fin del Plan de Implementación**

**Última actualización:** 3 de enero de 2026  
**Versión:** 1.0  
**Estado:** Listo para ejecución
