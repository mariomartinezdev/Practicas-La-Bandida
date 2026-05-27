<?php 
require_once './adminDashboard/conexion.php';

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Bandida</title>
    <link rel="stylesheet" href="./archivos/css/carta.css">
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
                    <button onclick="window.location.href='?sec=1'" class="btn-categoria activo" data-filtro="todos">Todos</button>
                    <button onclick="window.location.href='?sec=2'" class="btn-categoria" data-filtro="entrantes">Para compartir</button>
                    <button onclick="window.location.href='?sec=3'" class="btn-categoria" data-filtro="burgers">Burgers</button>
                    <button onclick="window.location.href='?sec=4'" class="btn-categoria" data-filtro="postres">Postres</button>
                </nav>
            </div>
        </nav>
    </header>

    <div class="aviso">
        <p>* Pan sin Gluten +1 €</p>
    </div>
	
	<?php
	
	if(isset($_GET["sec"]) && ($_GET["sec"] == "1" || $_GET["sec"] == "2")):
	 
		$producto = $pdo->query("select imagen,nombre,precio,disponible from platos where categoria_id=2")->fetchAll(PDO::FETCH_ASSOC);
	
	?>
	<h2>Para Compartir</h2>
	
    <section id="entrantes" class="carta">
	<?php
			
		foreach($producto as $p):
			if($p["disponible"]==1):
	?>
        <div class="tarjeta_burguer" data-categoria="entrantes" data-nombre="croquetas de jamon iberico">
            <div class="imagen_burguer">
                <img src="archivos/imagenes/logos/fondo-burguer.png" alt="Hamburguesa" class="imagen-fondo">
                <img src="<?php echo htmlspecialchars("./archivos/imagenes/platos/".$p['imagen'])?>" alt="Croquetas" class="imagenEncima">
            </div>
            <div class="info_burguer">
                <h3><?php echo htmlspecialchars($p['nombre'])?></h3>
                <p class="precio"><?php echo $p['precio']?>€</p>
            </div>
        </div>
        <?php endif; ?>
	<?php endforeach;?>
    </section>
		
	<?php endif;?>
	
    
    <?php 
	
	if(isset($_GET["sec"]) && ($_GET["sec"] == "1" || $_GET["sec"] == "3")):
		$producto = $pdo->query("select id,imagen,nombre,precio,disponible from platos where categoria_id=1")->fetchAll(PDO::FETCH_ASSOC);
	
	?>
    
    <h2>Nuestras Burguers</h2>
    
    <section id="burguers" class="carta">
		
	<?php foreach($producto as $p):
		if($p["disponible"]==1):
	?>
        
        <div class="tarjeta_burguer" data-categoria="burgers" data-nombre="la bandida">
            <div class="imagen_burguer">
                <img src="archivos/imagenes/logos/fondo-burguer.png" alt="Hamburguesa" class="imagen-fondo">
                <img src="<?php echo "./archivos/imagenes/platos/".$p["imagen"]?>" alt="Burguer La Bandida" class="imagenEncima">
            </div>
            <div class="info_burguer">
                <h3><?php echo htmlspecialchars($p['nombre'])?></h3>
                <p><?php 
					$k=$p["id"];
					$ingredientes=$pdo->query("select i.nombre from platos p inner Join plato_ingredientes on p.id=plato_id inner Join ingredientes i on i.id = ingrediente_id where p.id=$k")->fetchAll(PDO::FETCH_ASSOC);
					$desc="";
					$j=0;
					foreach($ingredientes as $i){
						$desc=$desc.($j==0?$i["nombre"]:mb_strtolower($i["nombre"])).(count($ingredientes)!=$j+2?$j+1!=count($ingredientes)?", ":'':" y ");
						$j++;
					}
					echo $desc;
                ?></p>
                <p class="precio"><?php echo $p['precio']?>€</p>
            </div>
        </div>
		<?php endif;?>
	<?php endforeach;?>
	
    </section>
		
	<?php endif;?>
	
	<?php
	
	if(isset($_GET["sec"]) && ($_GET["sec"] == "1" || $_GET["sec"] == "3")):

		$producto = $pdo->query("select nombre,precio,disponible from platos where categoria_id=4")->fetchAll(PDO::FETCH_ASSOC);
	
	?>
    
    <div class="patatas">
		
		<?php foreach($producto as $p):
			if($p["disponible"]==1):
		?>
        <p><?php echo $p["nombre"]." +".$p["precio"]."€"?></p>
			<?php endif;?>
		<?php endforeach;?>
		
    </div>
		
	<?php endif;?>
	
	<?php 
	
	if(isset($_GET["sec"]) && ($_GET["sec"] == "1" || $_GET["sec"] == "4")):
		
		$producto = $pdo->query("select imagen,nombre,precio,disponible from platos where categoria_id=3")->fetchAll(PDO::FETCH_ASSOC);
	
	?>

    <h2>POSTRES</h2>
    
    <section id="postres" class="carta">
		
		<?php foreach($producto as $p):
			if($p["disponible"]==1):
		?>
        
        <div class="tarjeta_burguer" data-categoria="postres" data-nombre="torrija de brioche">
            <div class="imagen_burguer">
                <img src="archivos/imagenes/logos/fondo-burguer.png" alt="Hamburguesa" class="imagen-fondo">
                <img src="<?php echo "./archivos/imagenes/platos/".$p["imagen"] ?>" alt="Torrija de Brioche" class="imagenEncima">
            </div>
            <div class="info_burguer">
                <h3><?php echo $p["nombre"] ?></h3>
                <p class="precio"><?php echo $p["precio"] ?>€</p>
            </div>
        </div>

			<?php endif; ?>
		<?php endforeach; ?>
		
    </section>
		
	<?php endif;?>
	
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

</body>

</html>
