<?php
// kanban_destacados.php - Gestor visual tipo kanban para artículos destacados
include '../includes/home_data_provider.php';

$json_file = '../includes/home_featured.json';
$message = '';
$message_type = '';

// Procesar guardado si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $featured_ids = json_decode($_POST['featured_ids'], true);
    
    if (is_array($featured_ids) && count($featured_ids) <= 9) {
        $data_to_save = [
            'updated_at' => time(),
            'ids' => $featured_ids,
            'count' => count($featured_ids)
        ];
        
        $write_result = file_put_contents($json_file, json_encode($data_to_save, JSON_PRETTY_PRINT));
        
        if ($write_result !== false) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Cambios guardados exitosamente']);
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error al guardar']);
            exit;
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Máximo 9 artículos permitidos']);
        exit;
    }
}

// Cargar datos actuales
$featured_data = [];
if (file_exists($json_file)) {
    $featured_data = json_decode(file_get_contents($json_file), true);
}
$featured_ids = $featured_data['ids'] ?? [];

// Obtener todos los posts ordenados
$sorted_posts = sort_posts_by_date($posts);

// Normalizar posts con IDs
$all_posts_normalized = [];
foreach ($sorted_posts as $slug => $post) {
    $post = enrich_post($post, $slug);
    $all_posts_normalized[$post['id']] = $post;
}

// Separar en destacados y disponibles
$featured_posts = [];
$available_posts = [];

// Primero, agregar destacados en orden
foreach ($featured_ids as $id) {
    if (isset($all_posts_normalized[$id])) {
        $featured_posts[] = $all_posts_normalized[$id];
    }
}

// Luego, agregar disponibles (últimos 15 que no estén destacados)
$count = 0;
foreach ($all_posts_normalized as $id => $post) {
    if (!in_array($id, $featured_ids) && $count < 15) {
        $available_posts[] = $post;
        $count++;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kanban Destacados - Meridiano Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            padding: 20px 0;
        }
        .kanban-header {
            background: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .kanban-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .kanban-column {
            background: #ffffff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            min-height: 600px;
            display: flex;
            flex-direction: column;
        }
        .kanban-column-header {
            padding: 10px 15px;
            margin: -15px -15px 15px -15px;
            border-radius: 8px 8px 0 0;
            font-weight: 600;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .kanban-column#available .kanban-column-header {
            background: #e3f2fd;
            color: #1976d2;
        }
        .kanban-column#featured .kanban-column-header {
            background: #e8f5e9;
            color: #388e3c;
        }
        .kanban-column#removed .kanban-column-header {
            background: #ffebee;
            color: #d32f2f;
        }
        .kanban-cards {
            flex: 1;
            overflow-y: auto;
            padding: 5px;
        }
        .kanban-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 10px;
            cursor: move;
            transition: all 0.2s;
        }
        .kanban-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        .kanban-card.sortable-ghost {
            opacity: 0.4;
        }
        .kanban-card.sortable-drag {
            opacity: 0.8;
            transform: rotate(2deg);
        }
        .card-title {
            font-size: 14px;
            font-weight: 600;
            color: #212529;
            margin-bottom: 6px;
            line-height: 1.4;
        }
        .card-date {
            font-size: 12px;
            color: #6c757d;
        }
        .counter-badge {
            background: white;
            color: #388e3c;
            padding: 4px 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
        }
        .counter-badge.full {
            background: #d32f2f;
            color: white;
        }
        .save-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
        .kanban-column#featured.limit-warning {
            border: 3px solid #ffc107;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="kanban-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2">📋 Kanban Destacados</h1>
                    <p class="text-muted mb-0">Gestor visual de artículos destacados en portada</p>
                </div>
                <a href="../index.php" target="_blank" class="btn btn-outline-secondary">Ver Portada</a>
            </div>
        </div>

        <div class="kanban-container">
            <!-- Columna 1: Disponibles -->
            <div class="kanban-column" id="available">
                <div class="kanban-column-header">
                    <span>📚 Artículos Disponibles</span>
                    <span class="badge bg-light text-dark"><?php echo count($available_posts); ?></span>
                </div>
                <div class="kanban-cards" id="available-cards">
                    <?php foreach ($available_posts as $post): ?>
                    <div class="kanban-card" data-id="<?php echo htmlspecialchars($post['id']); ?>">
                        <div class="card-title"><?php echo htmlspecialchars($post['title']); ?></div>
                        <div class="card-date"><?php echo htmlspecialchars($post['date']); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Columna 2: Destacados -->
            <div class="kanban-column" id="featured">
                <div class="kanban-column-header">
                    <span>⭐ Destacados en Portada</span>
                    <span class="counter-badge" id="featured-counter">0/9</span>
                </div>
                <div class="kanban-cards" id="featured-cards">
                    <?php foreach ($featured_posts as $post): ?>
                    <div class="kanban-card" data-id="<?php echo htmlspecialchars($post['id']); ?>">
                        <div class="card-title"><?php echo htmlspecialchars($post['title']); ?></div>
                        <div class="card-date"><?php echo htmlspecialchars($post['date']); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Columna 3: Eliminados -->
            <div class="kanban-column" id="removed">
                <div class="kanban-column-header">
                    <span>🗑️ Eliminados</span>
                    <span class="badge bg-light text-dark" id="removed-counter">0</span>
                </div>
                <div class="kanban-cards" id="removed-cards">
                    <!-- Zona de descarte -->
                </div>
            </div>
        </div>

        <button class="btn btn-success btn-lg save-button" onclick="saveChanges()">
            💾 Guardar Cambios
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        const availableList = document.getElementById('available-cards');
        const featuredList = document.getElementById('featured-cards');
        const removedList = document.getElementById('removed-cards');
        const featuredCounter = document.getElementById('featured-counter');
        const removedCounter = document.getElementById('removed-counter');
        const featuredColumn = document.getElementById('featured');

        // Configurar Sortable para columna disponibles
        new Sortable(availableList, {
            group: 'posts',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: function() {
                updateCounters();
            }
        });

        // Configurar Sortable para columna destacados
        new Sortable(featuredList, {
            group: 'posts',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onAdd: function(evt) {
                // Validar límite de 9
                if (featuredList.children.length > 9) {
                    alert('⚠️ Máximo 9 artículos destacados permitidos');
                    // Mover de vuelta
                    if (evt.from === availableList) {
                        availableList.appendChild(evt.item);
                    } else {
                        removedList.appendChild(evt.item);
                    }
                }
                updateCounters();
            },
            onChange: function() {
                updateCounters();
            }
        });

        // Configurar Sortable para columna eliminados
        new Sortable(removedList, {
            group: 'posts',
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            onEnd: function() {
                updateCounters();
            }
        });

        // Actualizar contadores
        function updateCounters() {
            const featuredCount = featuredList.children.length;
            const removedCount = removedList.children.length;
            
            featuredCounter.textContent = featuredCount + '/9';
            removedCounter.textContent = removedCount;
            
            // Cambiar color del badge si está lleno
            if (featuredCount >= 9) {
                featuredCounter.classList.add('full');
                featuredColumn.classList.add('limit-warning');
            } else {
                featuredCounter.classList.remove('full');
                featuredColumn.classList.remove('limit-warning');
            }
        }

        // Guardar cambios
        function saveChanges() {
            const featuredIds = Array.from(featuredList.children)
                .map(card => card.dataset.id);
            
            if (featuredIds.length > 9) {
                alert('⚠️ No se puede guardar: máximo 9 artículos destacados');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'save');
            formData.append('featured_ids', JSON.stringify(featuredIds));
            
            fetch('kanban_destacados.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Error al guardar: ' + error);
            });
        }

        // Inicializar contadores al cargar
        updateCounters();
    </script>
</body>
</html>
