<!-- Navigation-->
<nav class="navbar navbar-expand-lg navbar-light" id="mainNav">
    <div class="container px-4 px-lg-5">
        <a class="navbar-brand" href="index.php">Meridiano LVBP Blog</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            Menu
            <i class="fas fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ms-auto py-4 py-lg-0">
                <li class="nav-item"><a class="nav-link px-lg-3 py-3 py-lg-4" href="<?php echo SITE_URL; ?>index.php">Portada</a></li>

                <!-- Menú Desplegable Secciones -->
                <li class="nav-item dropdown">
                    <a class="nav-link px-lg-3 py-3 py-lg-4 dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Secciones
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <?php
                        // Cargar enlaces desde sidebar_global.ini
                        $sidebar_config_path = __DIR__ . '/config/sidebar_global.ini';
                        if (file_exists($sidebar_config_path)) {
                            $sidebar_config = parse_ini_file($sidebar_config_path, true);
                            if (isset($sidebar_config['ENLACES']['enlace']) && is_array($sidebar_config['ENLACES']['enlace'])) {
                                foreach ($sidebar_config['ENLACES']['enlace'] as $link_def) {
                                    $parts = explode('|', $link_def);
                                    if (count($parts) >= 2) {
                                        $text = $parts[0];
                                        $url = $parts[1];

                                        // Excluir "Todos los artículos" e "Inicio"
                                        if ($text === 'Todos los Artículos' || $text === 'Inicio') {
                                            continue;
                                        }

                                        // Ajustar URL si es relativa para que funcione desde cualquier nivel
                                        // Asumimos que menu.php se incluye en archivos que ya tienen SITE_URL definido en config.php
                                        // Si la URL no empieza con http, le pegamos SITE_URL.
                                        $final_url = $url;
                                        if (!preg_match('/^https?:\/\//', $url)) {
                                            $url_path = ltrim($url, '/');
                                            // Usar SITE_URL si está definida, sino ruta relativa simple (fallback)
                                            $final_url = defined('SITE_URL') ? SITE_URL . $url_path : $url_path;
                                        }

                                        echo '<li><a class="dropdown-item" href="' . htmlspecialchars($final_url) . '">' . htmlspecialchars($text) . '</a></li>';
                                    }
                                }
                            }
                        }
                        ?>
                    </ul>
                </li>

                <li class="nav-item"><a class="nav-link px-lg-3 py-3 py-lg-4" href="<?php echo SITE_URL; ?>lista_posts.php">Todos los artículos</a></li>
                <!-- <li class="nav-item"><a class="nav-link px-lg-3 py-3 py-lg-4" href="about.php">About</a></li>
                <li class="nav-item"><a class="nav-link px-lg-3 py-3 py-lg-4" href="post.php">Sample Post</a></li>
                <li class="nav-item"><a class="nav-link px-lg-3 py-3 py-lg-4" href="contact.php">Contact</a></li>-->
            </ul>
        </div>
    </div>
</nav>