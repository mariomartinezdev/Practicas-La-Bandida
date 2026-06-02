<?php
declare(strict_types=1);

require_once './adminDashboard/conexion.php';

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

$sec = filter_input(INPUT_GET, 'sec', FILTER_VALIDATE_INT) ?: 1;
$allowedSections = [1, 2, 3, 4];

if (!in_array($sec, $allowedSections, true)) {
    $sec = 1;
}

$categorias = [
    2 => ['titulo' => 'Para Compartir', 'slug' => 'entrantes', 'mostrar' => [1, 2]],
    1 => ['titulo' => 'Nuestras Burguers', 'slug' => 'burgers', 'mostrar' => [1, 3]],
    3 => ['titulo' => 'Postres', 'slug' => 'postres', 'mostrar' => [1, 4]],
];

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function obtenerPlatosPorCategoria(PDO $pdo, int $categoriaId): array
{
    $consulta = $pdo->prepare("
        SELECT 
            p.id,
            p.nombre,
            p.precio,
            p.imagen,
            p.disponible,
            COALESCE(
                GROUP_CONCAT(DISTINCT i.nombre ORDER BY i.nombre SEPARATOR ', '),
                ''
            ) AS ingredientes
        FROM platos p
        LEFT JOIN plato_ingredientes pi ON pi.plato_id = p.id
        LEFT JOIN ingredientes i ON i.id = pi.ingrediente_id
        WHERE p.categoria_id = :categoria
          AND p.disponible = 1
        GROUP BY p.id, p.nombre, p.precio, p.imagen, p.disponible
        ORDER BY p.nombre
    ");

    $consulta->execute([
        ':categoria' => $categoriaId,
    ]);

    return $consulta->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function obtenerExtras(PDO $pdo): array
{
    $consulta = $pdo->prepare("
        SELECT nombre, precio
        FROM platos
        WHERE categoria_id = :categoria
          AND disponible = 1
        ORDER BY nombre
    ");

    $consulta->execute([
        ':categoria' => 4,
    ]);

    return $consulta->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Bandida | Carta</title>
    <meta name="description" content="Descubre la carta de La Bandida: burgers, entrantes y postres artesanales.">
    <link rel="stylesheet" href="./archivos/css/carta.css">
    <script src="./archivos/script/scriptphp.js" defer></script>
    <link rel="icon" href="archivos/imagenes/logos/LOGOTIPO LA BANDIDA/ILUSTRACIÓN LA BANDIDA/ISOTIPO LINEAS LAPIZ/PNG/LA BANDIDA RAW LINES@4x-8.png" type="image/x-icon">
</head>

<body>
    <div class="layout-container">
        <header class="hero">
            <nav class="nav">
                <div class="logo">
                    <img
                        src="archivos/imagenes/logos/LOGO COMPLETO LA BANDIDA_Mesa de trabajo 1 copia.png"
                        alt="Logo de La Bandida">
                </div>

                <div class="opciones">
                    <nav class="categorias-nav" aria-label="Categorías de la carta">
                        <button type="button" onclick="window.location.href='?sec=1'" class="btn-categoria <?= $sec === 1 ? 'activo' : ''; ?>">
                            Todos
                        </button>

                        <button type="button" onclick="window.location.href='?sec=2'" class="btn-categoria <?= $sec === 2 ? 'activo' : ''; ?>">
                            Para compartir
                        </button>

                        <button type="button" onclick="window.location.href='?sec=3'" class="btn-categoria <?= $sec === 3 ? 'activo' : ''; ?>">
                            Burgers
                        </button>

                        <button type="button" onclick="window.location.href='?sec=4'" class="btn-categoria <?= $sec === 4 ? 'activo' : ''; ?>">
                            Postres
                        </button>
                    </nav>

                    <div class="buscador-menu">
                        <label for="buscar-plato" class="solo-lectores">
                            Buscar plato o ingrediente
                        </label>

                        <input
                            type="search"
                            id="buscar-plato"
                            placeholder="Buscar plato o ingrediente..."
                            autocomplete="off"
                            aria-label="Buscar plato o ingrediente">
                    </div>
                </div>
            </nav>
        </header>

        <div class="aviso">
            <p>* Pan sin Gluten +1 €</p>
        </div>

        <p id="sin-resultados" class="oculto">
            No se han encontrado platos con ese nombre o ingrediente.
        </p>

        <main>
            <?php foreach ($categorias as $categoriaId => $config): ?>
                <?php if (in_array($sec, $config['mostrar'], true)): ?>
                    <?php $productos = obtenerPlatosPorCategoria($pdo, $categoriaId); ?>

                    <section class="categoria-section">
                        <div class="categoria-header">
                            <h2 class="titulo-categoria" data-categoria="<?= e($config['slug']); ?>">
                                <?= e($config['titulo']); ?>
                            </h2>
                        </div>

                        <div id="<?= e($config['slug']); ?>" class="carta">
                            <?php foreach ($productos as $p): ?>
                                <article
                                    class="tarjeta_burguer"
                                    data-categoria="<?= e($config['slug']); ?>"
                                    data-nombre="<?= e($p['nombre']); ?>"
                                    data-ingredientes="<?= e($p['ingredientes']); ?>">

                                    <div class="imagen_burguer">
                                        <img
                                            src="archivos/imagenes/logos/fondo-burguer.png"
                                            alt=""
                                            class="imagen-fondo"
                                            loading="lazy">

                                        <img
                                            src="<?= e('./archivos/imagenes/platos/' . $p['imagen']); ?>"
                                            alt="<?= e($p['nombre']); ?>"
                                            class="imagenEncima"
                                            loading="lazy">
                                    </div>

                                    <div class="info_burguer">
                                        <div>
                                            <h3><?= e($p['nombre']); ?></h3>

                                            <?php if (!empty($p['ingredientes'])): ?>
                                                <p class="ingredientes"><?= e($p['ingredientes']); ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <p class="precio"><?= e((string) $p['precio']); ?>€</p>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if ($sec === 1 || $sec === 3): ?>
                <?php $extras = obtenerExtras($pdo); ?>

                <section class="extras-wrapper">
                    <h2>Extras</h2>

                    <div class="patatas extra-categoria">
                        <?php foreach ($extras as $extra): ?>
                            <div class="extra-item">
                                <span><?= e($extra['nombre']); ?></span>
                                <strong>+<?= e((string) $extra['precio']); ?>€</strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </main>

        <section id="contacto">
            <h2>Contacto y Ubicación</h2>
            <p>¿Tienes alguna duda? ¡Estaremos encantados de atenderte!</p>

            <div class="contact-grid">
                <div class="contact-card">
                    <strong>Dirección</strong>
                    <a href="https://www.google.com/maps/search/?api=1&query=Carrer+de+la+Verge+del+Pilar+13+03330+Crevillent+Alicante" target="_blank" rel="noopener noreferrer" class="enlace-mapas">
                        Carrer de la Verge del Pilar, 13, 03330 Crevillent, Alicante
                    </a>
                </div>

                <div class="contact-card">
                    <strong>Teléfono</strong>
                    <a href="tel:+34722829096" class="enlace-telefono">722 82 90 96</a>
                </div>

                <div class="contact-card">
                    <strong>Instagram</strong>
                    <a href="https://www.instagram.com/labandida_oficial/" target="_blank" rel="noopener noreferrer" class="enlace-instagram">
                        @labandida_oficial
                    </a>
                </div>
            </div>
        </section>

        <footer>
            <img src="archivos/imagenes/logos/LOGOTIPO LA BANDIDA/ILUSTRACIÓN LA BANDIDA/ISOTIPO LINEAS LIMPIAS/SVG/LA BANDIDA CLEAN LINES.svg"
                alt="Logo de La Bandida" class="logo-footer">
            <p>De bandidos. Para bandidos.</p>
        </footer>

        <button id="volver-arriba" class="oculto" aria-label="Volver arriba">
            ↑
        </button>
    </div>
</body>

</html>
