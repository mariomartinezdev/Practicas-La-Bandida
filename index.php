<?php 
require_once './adminDashboard/conexion.php'; // Asegúrate de que esta ruta es correcta y que el archivo existe

$sec = isset($_GET['sec']) ? (int) $_GET['sec'] : 1; // Si no se proporciona 'sec', se asigna el valor 1 por defecto

if (!in_array($sec, [1, 2, 3, 4], true)) { // Validar que 'sec' sea uno de los valores permitidos
    $sec = 1;
}

function obtenerPlatosPorCategoria(PDO $pdo, int $categoriaId): array // Función para obtener los platos de una categoría específica, incluyendo sus ingredientes
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
        LEFT JOIN plato_ingredientes pi 
            ON pi.plato_id = p.id
        LEFT JOIN ingredientes i 
            ON i.id = pi.ingrediente_id
        WHERE p.categoria_id = :categoria
          AND p.disponible = 1
        GROUP BY p.id, p.nombre, p.precio, p.imagen, p.disponible
        ORDER BY p.nombre
    "); // Consulta SQL para obtener los platos de la categoría especificada, junto con sus ingredientes concatenados en una sola cadena

    $consulta->execute([
        'categoria' => $categoriaId
    ]);

    return $consulta->fetchAll(PDO::FETCH_ASSOC); // Devuelve un array de platos, cada uno con su nombre, precio, imagen, disponibilidad e ingredientes
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Bandida</title>
    <link rel="stylesheet" href="./archivos/css/carta.css?v=<?php echo time(); ?>">
    <script src="./archivos/script/scriptphp.js?v=<?php echo time(); ?>" defer></script> <!-- Asegúrate de que la ruta al archivo JavaScript es correcta -->
    <link rel="icon" href="archivos/imagenes/logos/LOGOTIPO LA BANDIDA/ILUSTRACIÓN LA BANDIDA/ISOTIPO LINEAS LAPIZ/PNG/LA BANDIDA RAW LINES@4x-8.png" type="image/x-ico">
</head>

<body>
    <header>
        <nav class="nav">
            <div class="logo">
                <img src="archivos/imagenes/logos/LOGO COMPLETO LA BANDIDA_Mesa de trabajo 1 copia.png"
                    alt="Logo de La Bandida">
            </div>
            <div class="opciones">
    <nav class="categorias-nav">
        <button 
            type="button"
            onclick="window.location.href='?sec=1'"
            class="btn-categoria <?php echo $sec === 1 ? 'activo' : ''; ?>">
            Todos
        </button>

        <button 
            type="button"
            onclick="window.location.href='?sec=2'"
            class="btn-categoria <?php echo $sec === 2 ? 'activo' : ''; ?>">
            Para compartir
        </button>

        <button 
            type="button"
            onclick="window.location.href='?sec=3'"
            class="btn-categoria <?php echo $sec === 3 ? 'activo' : ''; ?>">
            Burgers
        </button>

        <button 
            type="button"
            onclick="window.location.href='?sec=4'"
            class="btn-categoria <?php echo $sec === 4 ? 'activo' : ''; ?>">
            Postres
        </button>
    </nav>

    <div class="buscador-menu">
        <label for="buscar-plato" class="solo-lectores">
            Buscar plato, ingrediente o precio
        </label>

        <input 
            type="search" 
            id="buscar-plato" 
            placeholder="Buscar plato, ingrediente o precio..."
            autocomplete="off">
    </div>
</div>
        </nav>
    </header>

    <div class="aviso">
        <p>* Pan sin Gluten +1 €</p>
    </div>
    <p id="sin-resultados" class="oculto">
    No se han encontrado platos con ese nombre, ingrediente o precio.
   </p>
	
	<?php
	
	if ($sec === 1 || $sec === 2): ?> <!-- Si 'sec' es 1 (Todos) o 2 (Para Compartir), muestra la sección de "Para Compartir" -->

    <?php $productos = obtenerPlatosPorCategoria($pdo, 2); // Obtiene los platos de la categoría "Para Compartir" ?>

    <h2 class="titulo-categoria" data-categoria="entrantes">
        Para Compartir
    </h2>

    <section id="entrantes" class="carta">

        <?php foreach ($productos as $p): ?> <!-- Recorre los platos de "Para Compartir" y muéstralos en la sección correspondiente -->

            <div class="tarjeta_burguer"
                 data-categoria="entrantes"
                 data-nombre="<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                 data-ingredientes="<?php echo htmlspecialchars($p['ingredientes'], ENT_QUOTES, 'UTF-8'); ?>"
                 data-precio="<?php echo htmlspecialchars((string) $p['precio'], ENT_QUOTES, 'UTF-8'); ?>">

                <div class="imagen_burguer">
                    <img 
                        src="archivos/imagenes/logos/fondo-burguer.png" 
                        alt="" 
                        class="imagen-fondo">

                    <img 
                        src="<?php echo htmlspecialchars('./archivos/imagenes/platos/' . $p['imagen'], ENT_QUOTES, 'UTF-8'); ?>" 
                        alt="<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?>" 
                        class="imagenEncima">
                </div>

                <div class="info_burguer">
                    <h3>
                        <?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                    </h3>

                    <p class="precio">
                        <?php echo htmlspecialchars($p['precio'], ENT_QUOTES, 'UTF-8'); ?>€
                    </p>
                </div>
            </div>

        <?php endforeach; ?>

    </section>

<?php endif; ?>
	
    
    <?php if ($sec === 1 || $sec === 3): ?> <!-- Si 'sec' es 1 (Todos) o 3 (Burgers), muestra la sección de "Burgers" -->

    <?php $productos = obtenerPlatosPorCategoria($pdo, 1); ?> <!-- Obtiene los platos de la categoría "Burgers" -->

    <h2 class="titulo-categoria" data-categoria="burgers">
        Nuestras Burguers
    </h2>

    <section id="burguers" class="carta">

        <?php foreach ($productos as $p): ?> <!-- Recorre los platos de "Burgers" y muéstralos en la sección correspondiente -->

            <div class="tarjeta_burguer"
                 data-categoria="burgers"
                 data-nombre="<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                 data-ingredientes="<?php echo htmlspecialchars($p['ingredientes'], ENT_QUOTES, 'UTF-8'); ?>"
                 data-precio="<?php echo htmlspecialchars((string) $p['precio'], ENT_QUOTES, 'UTF-8'); ?>">

                <div class="imagen_burguer">
                    <img 
                        src="archivos/imagenes/logos/fondo-burguer.png" 
                        alt="" 
                        class="imagen-fondo">

                    <img 
                        src="<?php echo htmlspecialchars('./archivos/imagenes/platos/' . $p['imagen'], ENT_QUOTES, 'UTF-8'); ?>" 
                        alt="<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?>" 
                        class="imagenEncima">
                </div>

                <div class="info_burguer">
                    <h3>
                        <?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                    </h3>

                    <p>
                        <?php echo htmlspecialchars($p['ingredientes'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>

                    <p class="precio">
                        <?php echo htmlspecialchars($p['precio'], ENT_QUOTES, 'UTF-8'); ?>€
                    </p>
                </div>
            </div>

        <?php endforeach; ?>

    </section>

<?php endif; ?>
	
	<?php if ($sec === 1 || $sec === 3): ?> <!-- Si 'sec' es 1 (Todos) o 3 (Burgers), muestra la sección de "Extras" -->

    <?php
        $consultaExtras = $pdo->prepare("
            SELECT nombre, precio
            FROM platos
            WHERE categoria_id = :categoria
              AND disponible = 1
            ORDER BY nombre
        "); // Consulta SQL para obtener los extras disponibles, ordenados por nombre

        $consultaExtras->execute([
            'categoria' => 4
        ]);

        $extras = $consultaExtras->fetchAll(PDO::FETCH_ASSOC); // Devuelve un array de extras, cada uno con su nombre y precio
    ?>

    <div class="patatas extra-categoria">

        <?php foreach ($extras as $extra): ?> <!-- Recorre los extras y muéstralos en la sección correspondiente -->

            <p>
                <?php echo htmlspecialchars($extra['nombre'], ENT_QUOTES, 'UTF-8'); ?> <!-- Muestra el nombre del extra, asegurándose de escapar cualquier carácter especial para evitar problemas de seguridad -->
                +<?php echo htmlspecialchars($extra['precio'], ENT_QUOTES, 'UTF-8'); ?>€ <!-- Muestra el precio del extra, precedido por un signo "+" para indicar que es un costo adicional -->
            </p>

        <?php endforeach; ?>

    </div>

<?php endif; ?>
	
	<?php if ($sec === 1 || $sec === 4): ?> <!-- Si 'sec' es 1 (Todos) o 4 (Postres), muestra la sección de "Postres" -->

    <?php $productos = obtenerPlatosPorCategoria($pdo, 3); ?> <!-- Obtiene los platos de la categoría "Postres" -->

    <h2 class="titulo-categoria" data-categoria="postres">
        Postres
    </h2>

    <section id="postres" class="carta">

        <?php foreach ($productos as $p): ?> <!-- Recorre los platos de "Postres" y muéstralos en la sección correspondiente -->

            <div class="tarjeta_burguer"
                 data-categoria="postres"
                 data-nombre="<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
                 data-ingredientes="<?php echo htmlspecialchars($p['ingredientes'], ENT_QUOTES, 'UTF-8'); ?>"
                 data-precio="<?php echo htmlspecialchars((string) $p['precio'], ENT_QUOTES, 'UTF-8'); ?>">

                <div class="imagen_burguer">
                    <img 
                        src="archivos/imagenes/logos/fondo-burguer.png" 
                        alt="" 
                        class="imagen-fondo">

                    <img 
                        src="<?php echo htmlspecialchars('./archivos/imagenes/platos/' . $p['imagen'], ENT_QUOTES, 'UTF-8'); ?>" 
                        alt="<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?>" 
                        class="imagenEncima">
                </div>

                <div class="info_burguer">
                    <h3>
                        <?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                    </h3>

                    <p class="precio">
                        <?php echo htmlspecialchars($p['precio'], ENT_QUOTES, 'UTF-8'); ?>€
                    </p>
                </div>
            </div>

        <?php endforeach; ?>

    </section>

<?php endif; ?>
	
    <section id="contacto">
        <h2>Contacto y Ubicación</h2>
        <p>¿Tienes alguna duda? ¡Estaremos encantados de atenderte!</p>
        <p>
            <strong>Dirección:</strong><br>
            <a href="https://www.google.com/maps/search/?api=1&query=Carrer+de+la+Verge+del+Pilar+13+03330+Crevillent+Alicante" target="_blank" class="enlace-mapas">
                Carrer de la Verge del Pilar, 13, 03330 Crevillent, Alicante
            </a>
        </p>
        <p>
            <strong>Teléfono:</strong><br>
            <a href="tel:+34722829096" class="enlace-telefono">722 82 90 96</a>
        </p>
        <p>
            <strong>Síguenos:</strong><br>
            <a href="https://www.instagram.com/labandida_oficial/" target="_blank" class="enlace-instagram">
                @labandida_oficial
            </a>
        </p>
    </section>

    <footer>
        <img src="archivos/imagenes/logos/LOGOTIPO LA BANDIDA/ILUSTRACIÓN LA BANDIDA/ISOTIPO LINEAS LIMPIAS/SVG/LA BANDIDA CLEAN LINES.svg"
            alt="Logo de La Bandida" class="logo-footer">
        <p>De bandidos. Para bandidos.</p>
    </footer>
    <button id="volver-arriba" class="oculto" aria-label="Volver arriba"> <!-- El botón para volver arriba, inicialmente oculto -->
      ↑
    </button>
</body>

</html>
