<?php
$base_path = "../";
include '../auth.php';
check_auth('admin');

$portfolioFile = __DIR__ . '/../data/portfolio.php';
$portfolioItems = require $portfolioFile;
$portfolioImageDir = __DIR__ . '/../assets/images/portfolio/';
$portfolioImagePathPrefix = 'assets/images/portfolio/';
$defaultPortfolioImage = 'assets/images/portfolio-placeholder-1.jpg';

function save_portfolio_items($filePath, $items) {
    $export = var_export($items, true);
    $content = "<?php\n\nreturn " . $export . ";\n";
    file_put_contents($filePath, $content);
}

function slugify_portfolio($text) {
    $slug = strtolower(trim((string) $text));
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
    $slug = trim((string) $slug, '-');
    return $slug !== '' ? $slug : 'caso';
}

function build_unique_portfolio_slug($desiredSlug, $items) {
    $slug = slugify_portfolio($desiredSlug);
    $baseSlug = $slug;
    $counter = 2;
    $existingSlugs = array_map(function ($item) {
        return $item['slug'] ?? '';
    }, $items);

    while (in_array($slug, $existingSlugs, true)) {
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }

    return $slug;
}

function handle_portfolio_image_upload($directory, $pathPrefix, $slug) {
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    if (!isset($_FILES['image_file']) || !is_array($_FILES['image_file'])) {
        return '';
    }

    if (($_FILES['image_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return '';
    }

    $tmpName = $_FILES['image_file']['tmp_name'];
    $originalName = $_FILES['image_file']['name'] ?? 'portfolio-image';
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $allowedExtensions, true)) {
        return '';
    }

    $safeSlug = preg_replace('/[^a-z0-9-]+/i', '-', $slug);
    $fileName = $safeSlug . '-' . date('YmdHis') . '.' . $extension;
    $destination = $directory . $fileName;

    if (!move_uploaded_file($tmpName, $destination)) {
        return '';
    }

    return $pathPrefix . $fileName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_portfolio_item') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $alt = trim($_POST['alt'] ?? '');
    $slugInput = trim($_POST['slug'] ?? '');

    if ($title === '' || $category === '' || $summary === '' || $alt === '') {
        $error_msg = "Completa titulo, categoria, resumen y texto alternativo para crear el caso.";
    } else {
        $slug = build_unique_portfolio_slug($slugInput !== '' ? $slugInput : $title, $portfolioItems);
        $uploadedImagePath = handle_portfolio_image_upload($portfolioImageDir, $portfolioImagePathPrefix, $slug);
        $imagePath = $uploadedImagePath !== '' ? $uploadedImagePath : trim($_POST['image'] ?? '');

        if ($imagePath === '') {
            $imagePath = $defaultPortfolioImage;
        }

        $portfolioItems[] = [
            'slug' => $slug,
            'title' => $title,
            'category' => $category,
            'summary' => $summary,
            'image' => $imagePath,
            'alt' => $alt,
            'featured' => isset($_POST['featured']),
            'visible' => true,
            'metrics' => [
                [
                    'value' => trim($_POST['metric_1_value'] ?? ''),
                    'label' => trim($_POST['metric_1_label'] ?? ''),
                ],
                [
                    'value' => trim($_POST['metric_2_value'] ?? ''),
                    'label' => trim($_POST['metric_2_label'] ?? ''),
                ],
            ],
        ];

        save_portfolio_items($portfolioFile, $portfolioItems);
        $success_msg = "Nuevo caso de portafolio creado.";
        $portfolioItems = require $portfolioFile;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_portfolio_item') {
    $slug = $_POST['slug'] ?? '';
    $updated = false;
    $uploadedImagePath = handle_portfolio_image_upload($portfolioImageDir, $portfolioImagePathPrefix, $slug);

    foreach ($portfolioItems as &$item) {
        if (($item['slug'] ?? '') !== $slug) {
            continue;
        }

        $item['title'] = trim($_POST['title'] ?? $item['title']);
        $item['category'] = trim($_POST['category'] ?? $item['category']);
        $item['summary'] = trim($_POST['summary'] ?? $item['summary']);
        $item['image'] = $uploadedImagePath !== ''
            ? $uploadedImagePath
            : trim($_POST['image'] ?? $item['image']);
        $item['alt'] = trim($_POST['alt'] ?? $item['alt']);
        $item['featured'] = isset($_POST['featured']);
        $item['metrics'] = [
            [
                'value' => trim($_POST['metric_1_value'] ?? ($item['metrics'][0]['value'] ?? '')),
                'label' => trim($_POST['metric_1_label'] ?? ($item['metrics'][0]['label'] ?? '')),
            ],
            [
                'value' => trim($_POST['metric_2_value'] ?? ($item['metrics'][1]['value'] ?? '')),
                'label' => trim($_POST['metric_2_label'] ?? ($item['metrics'][1]['label'] ?? '')),
            ],
        ];
        $updated = true;
        break;
    }
    unset($item);

    if ($updated) {
        save_portfolio_items($portfolioFile, $portfolioItems);
        $success_msg = "Portafolio actualizado correctamente.";
        $portfolioItems = require $portfolioFile;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_portfolio_visibility') {
    $slug = $_POST['slug'] ?? '';
    $updated = false;

    foreach ($portfolioItems as &$item) {
        if (($item['slug'] ?? '') !== $slug) {
            continue;
        }

        $item['visible'] = !($item['visible'] ?? true);
        $updated = true;
        $visibility_msg = !empty($item['visible'])
            ? "Caso mostrado de nuevo en el portafolio."
            : "Caso ocultado del portafolio publico.";
        break;
    }
    unset($item);

    if ($updated) {
        save_portfolio_items($portfolioFile, $portfolioItems);
        $success_msg = $visibility_msg;
        $portfolioItems = require $portfolioFile;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_portfolio_item') {
    $slug = $_POST['slug'] ?? '';
    $beforeCount = count($portfolioItems);

    $portfolioItems = array_values(array_filter($portfolioItems, function ($item) use ($slug) {
        return ($item['slug'] ?? '') !== $slug;
    }));

    if (count($portfolioItems) !== $beforeCount) {
        save_portfolio_items($portfolioFile, $portfolioItems);
        $success_msg = "Caso eliminado del portafolio.";
        $portfolioItems = require $portfolioFile;
    }
}

$page_title = "Silvex | Portafolio";
$current_page = "admin";
$body_class = "page-admin";
include '../header.php';
?>

<section class="page-panel page-panel--full animate-liquid">
    <?php if (isset($success_msg)): ?>
        <div style="background: rgba(76, 175, 80, 0.2); color: #81c784; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(76, 175, 80, 0.3);">
            <?php echo htmlspecialchars($success_msg, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($error_msg)): ?>
        <div style="background: rgba(255, 107, 107, 0.18); color: #ffb3b3; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(255, 107, 107, 0.35);">
            <?php echo htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; gap: 1rem; flex-wrap: wrap;">
        <div>
            <h1>Gestion de Portafolio</h1>
            <p>Edita casos de exito, metricas y rutas de imagen del portafolio publico.</p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <button type="button" onclick="openCreatePortfolioModal()" class="cta" style="min-width: auto;">+ Nuevo Caso</button>
            <a href="index.php" class="cta" style="min-width: auto; background: rgba(255,255,255,0.1);">Volver</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
        <?php foreach ($portfolioItems as $item): ?>
            <?php
            $metrics = $item['metrics'] ?? [];
            $metric1 = $metrics[0] ?? ['value' => '', 'label' => ''];
            $metric2 = $metrics[1] ?? ['value' => '', 'label' => ''];
            $isVisible = $item['visible'] ?? true;
            ?>
            <div class="premium-glass" style="display: block; padding: 1.4rem; border-radius: 24px;">
                <div style="aspect-ratio: 16 / 10; overflow: hidden; border-radius: 18px; margin-bottom: 1rem; background: rgba(255,255,255,0.04);">
                    <img src="<?php echo htmlspecialchars($base_path . ltrim($item['image'], '/'), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['alt'], ENT_QUOTES, 'UTF-8'); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;">
                    <div>
                        <div style="font-size: 0.8rem; opacity: 0.6; margin-bottom: 0.3rem;"><?php echo htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <h3 style="margin: 0; font-size: 1.5rem;"><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <div style="margin-top: 0.45rem; font-size: 0.78rem; font-weight: 700; color: <?php echo $isVisible ? '#81c784' : '#ffb3b3'; ?>;">
                            <?php echo $isVisible ? 'Visible en la web' : 'Oculto en la web'; ?>
                        </div>
                    </div>
                    <button
                        type="button"
                        onclick="openPortfolioModal(this)"
                        style="color: #2ec8ef; background: transparent; border: 0; cursor: pointer; font-weight: 700; padding: 0;"
                        data-slug="<?php echo htmlspecialchars($item['slug'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-title="<?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-category="<?php echo htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-summary="<?php echo htmlspecialchars($item['summary'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-image="<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-alt="<?php echo htmlspecialchars($item['alt'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-featured="<?php echo !empty($item['featured']) ? '1' : '0'; ?>"
                        data-metric-1-value="<?php echo htmlspecialchars($metric1['value'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-metric-1-label="<?php echo htmlspecialchars($metric1['label'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-metric-2-value="<?php echo htmlspecialchars($metric2['value'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-metric-2-label="<?php echo htmlspecialchars($metric2['label'], ENT_QUOTES, 'UTF-8'); ?>"
                    >Editar</button>
                </div>
                <p style="margin-top: 0.8rem; font-size: 0.95rem; line-height: 1.35; opacity: 0.88;"><?php echo htmlspecialchars($item['summary'], ENT_QUOTES, 'UTF-8'); ?></p>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: 1rem;">
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="toggle_portfolio_visibility">
                        <input type="hidden" name="slug" value="<?php echo htmlspecialchars($item['slug'], ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="cta" style="min-width: auto; padding: 0.75rem 1rem; background: <?php echo $isVisible ? 'rgba(255,255,255,0.1)' : 'rgba(46, 200, 239, 0.18)'; ?>;">
                            <?php echo $isVisible ? 'Ocultar' : 'Mostrar'; ?>
                        </button>
                    </form>
                    <form method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este caso del portafolio?');" style="margin: 0;">
                        <input type="hidden" name="action" value="delete_portfolio_item">
                        <input type="hidden" name="slug" value="<?php echo htmlspecialchars($item['slug'], ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="cta" style="min-width: auto; padding: 0.75rem 1rem; background: rgba(255, 107, 107, 0.18); color: #ffd6d6; border-color: rgba(255, 107, 107, 0.35);">
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<div id="portfolio-create-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); z-index: 1000; justify-content: center; align-items: center; padding: 1rem; overflow-y: auto;">
    <div class="premium-glass" style="width: 100%; max-width: 720px; max-height: calc(100vh - 2rem); padding: 2rem; border-radius: 30px; overflow-y: auto;">
        <h2 style="margin-bottom: 1.5rem; color: #2ec8ef;">Crear Nuevo Caso de Portafolio</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create_portfolio_item">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Titulo</label>
                    <input type="text" name="title" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Categoria</label>
                    <input type="text" name="category" required value="Campaña Publicitaria" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Slug (opcional)</label>
                    <input type="text" name="slug" placeholder="ej: nueva-marca" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Texto Alternativo</label>
                    <input type="text" name="alt" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Resumen</label>
                <textarea name="summary" rows="4" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none; resize: vertical;"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Ruta de Imagen</label>
                    <input type="text" name="image" placeholder="assets/images/mi-caso.jpg" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Subir Imagen</label>
                    <input type="file" name="image_file" accept=".jpg,.jpeg,.png,.webp" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Metrica 1 Valor</label>
                    <input type="text" name="metric_1_value" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Metrica 1 Etiqueta</label>
                    <input type="text" name="metric_1_label" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Metrica 2 Valor</label>
                    <input type="text" name="metric_2_value" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Metrica 2 Etiqueta</label>
                    <input type="text" name="metric_2_label" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
            </div>

            <label style="display: inline-flex; gap: 0.5rem; align-items: center; margin-bottom: 1.6rem; font-size: 0.9rem;">
                <input type="checkbox" name="featured" checked>
                Mostrar como destacado
            </label>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="cta" style="flex: 1;">Crear Caso</button>
                <button type="button" onclick="closeCreatePortfolioModal()" class="cta" style="flex: 1; background: rgba(255,255,255,0.1);">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<div id="portfolio-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); z-index: 1000; justify-content: center; align-items: center; padding: 1rem; overflow-y: auto;">
    <div class="premium-glass" style="width: 100%; max-width: 720px; max-height: calc(100vh - 2rem); padding: 2rem; border-radius: 30px; overflow-y: auto;">
        <h2 style="margin-bottom: 1.5rem; color: #2ec8ef;">Editar Item de Portafolio</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_portfolio_item">
            <input type="hidden" name="slug" id="portfolio-slug">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Titulo</label>
                    <input type="text" name="title" id="portfolio-title" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Categoria</label>
                    <input type="text" name="category" id="portfolio-category" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Resumen</label>
                <textarea name="summary" id="portfolio-summary" rows="4" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none; resize: vertical;"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Ruta de Imagen</label>
                    <input type="text" name="image" id="portfolio-image" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Texto Alternativo</label>
                    <input type="text" name="alt" id="portfolio-alt" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Subir Nueva Imagen</label>
                <input type="file" name="image_file" accept=".jpg,.jpeg,.png,.webp" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                <div style="font-size: 0.75rem; opacity: 0.6; margin-top: 0.4rem;">Si subes un archivo, reemplaza la ruta de imagen actual automaticamente.</div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Metrica 1 Valor</label>
                    <input type="text" name="metric_1_value" id="portfolio-metric-1-value" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Metrica 1 Etiqueta</label>
                    <input type="text" name="metric_1_label" id="portfolio-metric-1-label" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Metrica 2 Valor</label>
                    <input type="text" name="metric_2_value" id="portfolio-metric-2-value" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Metrica 2 Etiqueta</label>
                    <input type="text" name="metric_2_label" id="portfolio-metric-2-label" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
            </div>

            <label style="display: inline-flex; gap: 0.5rem; align-items: center; margin-bottom: 1.6rem; font-size: 0.9rem;">
                <input type="checkbox" name="featured" id="portfolio-featured">
                Mostrar como destacado
            </label>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="cta" style="flex: 1;">Guardar Cambios</button>
                <button type="button" onclick="closePortfolioModal()" class="cta" style="flex: 1; background: rgba(255,255,255,0.1);">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreatePortfolioModal() {
    document.getElementById('portfolio-create-modal').style.display = 'flex';
}

function closeCreatePortfolioModal() {
    document.getElementById('portfolio-create-modal').style.display = 'none';
}

function openPortfolioModal(button) {
    document.getElementById('portfolio-slug').value = button.dataset.slug || '';
    document.getElementById('portfolio-title').value = button.dataset.title || '';
    document.getElementById('portfolio-category').value = button.dataset.category || '';
    document.getElementById('portfolio-summary').value = button.dataset.summary || '';
    document.getElementById('portfolio-image').value = button.dataset.image || '';
    document.getElementById('portfolio-alt').value = button.dataset.alt || '';
    document.getElementById('portfolio-metric-1-value').value = button.dataset.metric1Value || '';
    document.getElementById('portfolio-metric-1-label').value = button.dataset.metric1Label || '';
    document.getElementById('portfolio-metric-2-value').value = button.dataset.metric2Value || '';
    document.getElementById('portfolio-metric-2-label').value = button.dataset.metric2Label || '';
    document.getElementById('portfolio-featured').checked = button.dataset.featured === '1';
    document.getElementById('portfolio-modal').style.display = 'flex';
}

function closePortfolioModal() {
    document.getElementById('portfolio-modal').style.display = 'none';
}

<?php if (isset($error_msg)): ?>
document.addEventListener('DOMContentLoaded', function () {
    openCreatePortfolioModal();
});
<?php endif; ?>
</script>

<?php include '../footer.php'; ?>
