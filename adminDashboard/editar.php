<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

function asignarImagen($categoria_id){

	switch($categoria_id){

		case "1":
			return "placeHolder_hamburguesa.png";

		case "2":
			return "placeHolderEntrantes.png";

		case "3":
			return "postrePlace.png";

		default:
			break;
						
	}
				
	return "patatas_fritas.jpg";

}


$mensaje = "";
$tipo_mensaje = "success";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verificar que exista el elemento a editar
$stmt = $pdo->prepare("SELECT * FROM platos WHERE id = ?");
$stmt->execute([$id]);
$plato = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$plato) {
    die("plato no encontrada.");
}

// Procesar Formulario de Modificación
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_edit'])) {
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']);
    $categoria_id = intval($_POST['categoria_id']);
    $ingredientes_sel = isset($_POST['ingredientes']) ? $_POST['ingredientes'] : [];
    $alergenos_sel = isset($_POST['alergenos']) ? $_POST['alergenos'] : [];

    if (!empty($nombre) && $precio > 0 && $categoria_id > 0) {
        try {
            $pdo->beginTransaction();

            $imagen = isset($_POST["imagen"])?$_POST["imagen"]:asignarImagen($categoria_id);
            // Actualizar tabla principal
            $stmt_up = $pdo->prepare("UPDATE platos SET nombre = ?, precio = ?, categoria_id = ?, imagen = ? WHERE id = ?");
            $stmt_up->execute([$nombre, $precio, $categoria_id,$imagen, $id]);

            // Limpiar relaciones previas
            $pdo->prepare("DELETE FROM plato_ingredientes WHERE plato_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM plato_alergenos WHERE plato_id = ?")->execute([$id]);

            // Reinsertar ingredientes seleccionados
            if (!empty($ingredientes_sel)) {
                $stmt_ing = $pdo->prepare("INSERT INTO plato_ingredientes (plato_id, ingrediente_id) VALUES (?, ?)");
                foreach ($ingredientes_sel as $ing_id) {
                    $stmt_ing->execute([$id, $ing_id]);
                }
            }

            // Reinsertar alérgenos seleccionados
            if (!empty($alergenos_sel)) {
                $stmt_ale = $pdo->prepare("INSERT INTO plato_alergenos (plato_id, alergeno_id) VALUES (?, ?)");
                foreach ($alergenos_sel as $ale_id) {
                    $stmt_ale->execute([$id, $ale_id]);
                }
            }

            $pdo->commit();
            
            // Recargar datos actualizados
            $stmt->execute([$id]);
            $plato = $stmt->fetch(PDO::FETCH_ASSOC);
            $mensaje = "plato modificada correctamente.";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensaje = "Error al actualizar: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    } else {
        $mensaje = "Campos obligatorios incompletos.";
        $tipo_mensaje = "danger";
    }
}

// Cargar catálogos maestros para los campos del formulario
$categorias = $pdo->query("SELECT * FROM categorias")->fetchAll(PDO::FETCH_ASSOC);
$ingredientes = $pdo->query("SELECT * FROM ingredientes")->fetchAll(PDO::FETCH_ASSOC);
$alergenos = $pdo->query("SELECT * FROM alergenos")->fetchAll(PDO::FETCH_ASSOC);

// Obtener ingredientes y alérgenos ya marcados previamente en este elemento
$ingredientes_actuales = $pdo->query("SELECT ingrediente_id FROM plato_ingredientes WHERE plato_id = $id")->fetchAll(PDO::FETCH_COLUMN);
$alergenos_actuales = $pdo->query("SELECT alergeno_id FROM plato_alergenos WHERE plato_id = $id")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modificar plato</title>
    <link rel="stylesheet" href="../archivos/css/dashboard.css">
</head>
<body>

<header>
    <h1>Modificar Elemento</h1>
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
        <h2>Editar: <?php echo htmlspecialchars($plato['nombre']); ?></h2>
        <form action="editar.php?id=<?php echo $id; ?>" method="POST">
            <input type="hidden" name="action_edit" value="1">

            <div class="form-group">
                <label>Nombre de plato</label>
                <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($plato['nombre']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Precio (€)</label>
                <input type="number" name="precio" step="0.01" class="form-control" value="<?php echo htmlspecialchars($plato['precio']); ?>" required>
            </div>

            <div class="form-group">
                <label>Imagen</label>
                <input name="imagen" type="file">
            </div>

            <div class="form-group">
                <label>Categoría</label>
                <select name="categoria_id" class="form-control" required>
                    <?php foreach($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($plato['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Ingredientes</label>
                <div class="checkbox-group">
                    <?php foreach($ingredientes as $ing): ?>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="ingredientes[]" value="<?php echo $ing['id']; ?>" <?php echo in_array($ing['id'], $ingredientes_actuales) ? 'checked' : ''; ?>> 
                            <?php echo htmlspecialchars($ing['nombre']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Alérgenos</label>
                <div class="checkbox-group">
                    <?php foreach($alergenos as $ale): ?>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="alergenos[]" value="<?php echo $ale['id']; ?>" <?php echo in_array($ale['id'], $alergenos_actuales) ? 'checked' : ''; ?>> 
                            <?php echo htmlspecialchars($ale['nombre']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">Guardar Cambios</button>
            <a href="dashboard.php" class="btn btn-secondary" style="width:100%; margin-top: 10px; text-align:center;">Cancelar</a>
        </form>
    </div>
</div>

</body>
</html>
