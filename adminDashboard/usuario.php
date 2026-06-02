<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['usuario_id'];

$admin = $pdo->query("SELECT id,grado FROM usuarios WHERE id = $id")->fetchAll(PDO::FETCH_ASSOC);

if(count($admin) != 1){

	header("Location: dashboard.php");
    exit;

}

$mensaje = "";
$tipo_mensaje = "success";

// Procesar Formulario de Modificación
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_edit'])) {
    
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $grado = intval($_POST['grado']);
	
	$idUsuario = 0;
	
	do{
		
		$idUsuario = rand(100000, 999999);
		$rep=$pdo->query("SELECT grado FROM usuarios WHERE idUsuario = $idUsuario")->fetchAll(PDO::FETCH_ASSOC);
		
	}while(count($rep)>0);
	
	$char1=chr(random_int(97, 122));
	$char2=chr(random_int(97, 122));
	$char3=chr(random_int(97, 122));
	$char4=chr(random_int(97, 122));
	$char5=chr(random_int(97, 122));
	$char6=chr(random_int(97, 122));
 
	$password=$char1.$char2.$char3.$char4.$char5.$char6;
 
    if (!empty($nombre) && !empty($apellido) && ($grado==1 || $grado==2)) {
    
		try {
			
			$pdo->beginTransaction();
                
            $stmt = $pdo->prepare("INSERT INTO usuarios (idUsuario,nombre,apellido,password,grado) VALUES (?,?,?,?,?)");
            $stmt->execute([$idUsuario,$nombre, $apellido, password_hash($password, PASSWORD_DEFAULT),$grado]);
            $pdo->commit();
            $mensaje = "Usuario agregado con éxito.( USUARIO: ".$idUsuario." PASSWORD: ".$password." )";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensaje = "Error al agregar usuario: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
        
    } else {
        $mensaje = "Por favor, rellene todos los campos del usuario.";
        $tipo_mensaje = "danger";
    }
        
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Anadir Usuario</title>
    <link rel="stylesheet" href="../archivos/css/dashboard.css">
</head>
<body>

<header>
    <h1>Anadir Usuario</h1>
    <nav>
        <a href="dashboard.php">← Volver al Listado Principal</a>
    </nav>
</header>

<div class="container" style="max-width: 600px;">
    
    <?php if(!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Datos:</h2>
        <form action="usuario.php" method="POST">
            <input type="hidden" name="action_edit" value="1">

            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" name="nombre" class="form-control" required >
            </div>
            
            <div class="form-group">
                <label>Apellido:</label>
                <input type="text" name="apellido" class="form-control" required >
            </div>

			<div class="form-group">
                <label>Grado(Nivel de Acceso):</label>
                <input type="number" name="grado" step="1" min="1" max="2" class="form-control" required >
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">Guardar Cambios</button>
            <a href="dashboard.php" class="btn btn-secondary" style="width:100%; margin-top: 10px; text-align:center;">Cancelar</a>

        </form>

    </div>

</div>

</body>
</html>
