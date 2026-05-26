<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$tipo_mensaje = "success";

// --- PROCESAR ACCIONES DE ELIMINACIÓN ---
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $tipo = $_GET['tipo'];
    $id = intval($_GET['id']);
    
    try {
        if ($tipo == 'plato') {
            $stmt = $pdo->prepare("DELETE FROM platos WHERE id = ?");
            $stmt->execute([$id]);
        } elseif ($tipo == 'ingrediente') {
            $stmt = $pdo->prepare("DELETE FROM ingredientes WHERE id = ?");
            $stmt->execute([$id]);
        } elseif ($tipo == 'alergeno') {
            $stmt = $pdo->prepare("DELETE FROM alergenos WHERE id = ?");
            $stmt->execute([$id]);
        } elseif ($tipo == "usuario"){
			$stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
			$stmt->execute([$id]);
		}
		
        $mensaje = "Elemento eliminado correctamente.";
    } catch (PDOException $e) {
        $mensaje = "Error al eliminar: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

// --- PROCESAR ALTA DE ELEMENTOS ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action_add'])) {
    $tipo = $_POST['tipo_elemento'];
    
    if ($tipo == 'plato') {
        $nombre = trim($_POST['nombre']);
        $precio = floatval($_POST['precio']);
        $categoria_id = intval($_POST['categoria_id']);
        $ingredientes_sel = isset($_POST['ingredientes']) ? $_POST['ingredientes'] : [];
        $alergenos_sel = isset($_POST['alergenos']) ? $_POST['alergenos'] : [];
        
        if (!empty($nombre) && $precio > 0 && $categoria_id > 0) {
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("INSERT INTO platos (nombre, precio, categoria_id) VALUES (?, ?, ?)");
                $stmt->execute([$nombre, $precio, $categoria_id]);
                $plato_id = $pdo->lastInsertId();
                
                // Insertar ingredientes
                if (!empty($ingredientes_sel)) {
                    $stmt_ing = $pdo->prepare("INSERT INTO plato_ingredientes (plato_id, ingrediente_id) VALUES (?, ?)");
                    foreach ($ingredientes_sel as $ing_id) {
                        $stmt_ing->execute([$plato_id, $ing_id]);
                    }
                }
                
                // Insertar alérgenos
                if (!empty($alergenos_sel)) {
                    $stmt_ale = $pdo->prepare("INSERT INTO plato_alergenos (plato_id, alergeno_id) VALUES (?, ?)");
                    foreach ($alergenos_sel as $ale_id) {
                        $stmt_ale->execute([$plato_id, $ale_id]);
                    }
                }
                
                $pdo->commit();
                $mensaje = "plato agregada con éxito.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $mensaje = "Error al agregar plato: " . $e->getMessage();
                $tipo_mensaje = "danger";
            }
        } else {
            $mensaje = "Por favor, rellene todos los campos obligatorios de la plato.";
            $tipo_mensaje = "danger";
        }
    } else if ($tipo == 'ingrediente' || $tipo == 'alergeno') {
        $nombre = trim($_POST['nombre_simple']);
        if (!empty($nombre)) {
            try {
                $tabla = ($tipo == 'ingrediente') ? 'ingredientes' : 'alergenos';
                $stmt = $pdo->prepare("INSERT INTO $tabla (nombre) VALUES (?)");
                $stmt->execute([$nombre]);
                $mensaje = ucfirst($tipo) . " agregado con éxito.";
            } catch (PDOException $e) {
                $mensaje = "Error al agregar: El elemento ya existe o es inválido.";
                $tipo_mensaje = "danger";
            }
        } else {
            $mensaje = "El nombre no puede estar vacío.";
            $tipo_mensaje = "danger";
        }
    }
}

// --- CONSULTA FILTRADA POR CATEGORÍA O LISTADO GLOBAL ---
$categoria_filtro = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;

$query_hamb = "SELECT h.*, c.nombre AS categoria FROM platos h 
               LEFT JOIN categorias c ON h.categoria_id = c.id";
if ($categoria_filtro > 0) {
    $query_hamb .= " WHERE h.categoria_id = " . $categoria_filtro;
}
$query_hamb .= " ORDER BY h.id DESC";

$platos = $pdo->query($query_hamb)->fetchAll(PDO::FETCH_ASSOC);
$categorias = $pdo->query("SELECT * FROM categorias")->fetchAll(PDO::FETCH_ASSOC);
$ingredientes = $pdo->query("SELECT * FROM ingredientes")->fetchAll(PDO::FETCH_ASSOC);
$alergenos = $pdo->query("SELECT * FROM alergenos")->fetchAll(PDO::FETCH_ASSOC);
$usuarios  = $pdo->query("SELECT id,idUsuario,nombre,apellido,grado,fecha_registro FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    
    <meta charset="UTF-8">
    <title>Panel de Control</title>
    <link rel="stylesheet" href="dashboard.css">
	
</head>

<body>

<header>
    <h1>DASHBOARD</h1>
    <nav>
        <a href="dashboard.php">Inicio</a>
		<a href="logout.php" class="logout-btn">Cerrar Sesión</a>
    </nav>
</header>

<div class="container">
    
    <?php if(!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <!-- DISEÑO EN TABLA SIMULANDO GRID CSS NO COMPATIBLE -->
    <div class="grid">
        <div class="row">
            
            <!-- COLUNA IZQUIERDA: FORMULARIOS DE ALTA -->
            <div class="col col-30">
                
                <!-- Añadir plato -->
                <div class="card">
                    <h3>+ Plato</h3>
                    <form action="dashboard.php" method="POST">
                        <input type="hidden" name="action_add" value="1">
                        <input type="hidden" name="tipo_elemento" value="plato">
                        
                        <div class="form-group">
                            <label>Nombre de plato*</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Precio (€)*</label>
                            <input type="number" name="precio" step="0.01" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Categoría*</label>
                            <select name="categoria_id" class="form-control" required>
                                <option value="">Selecciona...</option>
                                <?php foreach($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Ingredientes</label>
                            <div class="checkbox-group">
                                <?php foreach($ingredientes as $ing): ?>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="ingredientes[]" value="<?php echo $ing['id']; ?>"> <?php echo htmlspecialchars($ing['nombre']); ?>
                                    </label>
                                <?php endforeach;; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Alérgenos</label>
                            <div class="checkbox-group">
                                <?php foreach($alergenos as $ale): ?>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="alergenos[]" value="<?php echo $ale['id']; ?>"> <?php echo htmlspecialchars($ale['nombre']); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:100%;">Guardar plato</button>
                    </form>
                </div>

                <!-- Añadir Ingrediente / Alérgeno -->
                <div class="card">
                    <h3>+ Ingrediente / Alérgeno</h3>
                    <form action="dashboard.php" method="POST">
                        <input type="hidden" name="action_add" value="1">
                        
                        <div class="form-group">
                            <label>Tipo de Registro</label>
                            <select name="tipo_elemento" class="form-control" required>
                                <option value="ingrediente">Ingrediente</option>
                                <option value="alergeno">Alérgeno</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nombre del Elemento</label>
                            <input type="text" name="nombre_simple" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-secondary" style="width:100%;">Registrar Simple</button>
                    </form>
                </div>

            </div>

            <!-- COLUNA DERECHA: CONSULTAS Y LISTADOS -->
            <div class="col col-70">
                
                <!-- Caja de Filtro por Categoría -->
                <div class="card" style="background-color: #ebedef;">
                    <form action="dashboard.php" method="GET">
                        <label style="font-weight: bold; margin-right: 10px;">Filtrar Lista de platos por Categoría:</label>
                        <select name="cat_id" onchange="this.form.submit()" class="form-control" style="width: auto; display: inline-block;">
                            <option value="0">--- Mostrar Todas ---</option>
                            <?php foreach($categorias as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($categoria_filtro == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endforeach;; ?>
                        </select>
                        <a href="dashboard.php" class="btn btn-secondary" style="padding: 5px 10px;">Limpiar</a>
                    </form>
                </div>

                <!-- Tabla General de platos -->
                <div class="card">
                    <h2>Listado de platos</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Categoría</th>
                                <th>Detalles (Ing / Alér)</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($platos)): ?>
                                <tr><td colspan="6" style="text-align:center;">No hay platos registradas en esta categoría.</td></tr>
                            <?php else: ?>
                                <?php foreach($platos as $h): 
                                    // Obtener ingredientes asignados
                                    $stmt_h_ing = $pdo->prepare("SELECT i.nombre FROM plato_ingredientes hi JOIN ingredientes i ON hi.ingrediente_id = i.id WHERE hi.plato_id = ?");
                                    $stmt_h_ing->execute([$h['id']]);
                                    $h_ingredientes = $stmt_h_ing->fetchAll(PDO::FETCH_COLUMN);

                                    // Obtener alérgenos asignados
                                    $stmt_h_ale = $pdo->prepare("SELECT a.nombre FROM plato_alergenos ha JOIN alergenos a ON ha.alergeno_id = a.id WHERE ha.plato_id = ?");
                                    $stmt_h_ale->execute([$h['id']]);
                                    $h_alergenos = $stmt_h_ale->fetchAll(PDO::FETCH_COLUMN);
                                ?>
                                <tr>
                                    <td><?php echo $h['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($h['nombre']); ?></strong></td>
                                    <td><?php echo number_format($h['precio'], 2, ',', '.'); ?> €</td>
                                    <td><span class="badge"><?php echo htmlspecialchars($h['categoria'] ?? 'Sin categoría'); ?></span></td>
                                    <td>
                                        <div>
                                            <small><strong>Ing:</strong></small>
                                            <?php foreach($h_ingredientes as $hi): ?>
                                                <span class="badge badge-ing"><?php echo htmlspecialchars($hi); ?></span>
                                            <?php endforeach;; ?>
                                        </div>
                                        <div style="margin-top: 5px;">
                                            <small><strong>Alér:</strong></small>
                                            <?php foreach($h_alergenos as $ha): ?>
                                                <span class="badge badge-ale"><?php echo htmlspecialchars($ha); ?></span>
                                            <?php endforeach;; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="editar.php?id=<?php echo $h['id']; ?>" class="btn btn-warning">Modificar</a>
                                        <a href="dashboard.php?action=delete&tipo=plato&id=<?php echo $h['id']; ?>" class="btn btn-danger" onclick="return confirm('¿Seguro de eliminar esta plato?')">Eliminar</a>
                                    </td>
                                </tr>
                                <?php endforeach;; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Tablas Secundarias Rápidas de Ingredientes y Alérgenos -->
                <div class="grid">
                    <div class="row">
                        <div class="col" style="width: 50%; padding-left:0;">
                            <div class="card">
                                <h3>Ingredientes Registrados</h3>
                                <table style="font-size: 13px;">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($ingredientes as $ing): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ing['nombre']); ?></td>
                                            <td><a href="dashboard.php?action=delete&tipo=ingrediente&id=<?php echo $ing['id']; ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar ingrediente?')">X</a></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col" style="width: 50%; padding-right:0;">
                            <div class="card">
                                <h3>Alérgenos Registrados</h3>
                                <table style="font-size: 13px;">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($alergenos as $ale): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ale['nombre']); ?></td>
                                            <td><a href="dashboard.php?action=delete&tipo=alergeno&id=<?php echo $ale['id']; ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar alérgeno?')">X</a></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

				<?php 
					
					$id = $_SESSION['usuario_id'];
					$admin = $pdo->query("SELECT id,grado FROM usuarios WHERE id = $id")->fetchAll(PDO::FETCH_ASSOC);
					if(count($admin) == 1):
				
				?>
				
				<div class="card">
                    <h2>Listado de Usuarios</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Grado</th>
                                <th>Alta</th>
                                <th>Acciones</th>
                            </tr>
                        <tbody>
                            <?php if(empty($usuarios)): ?>
                                <tr><td colspan="6" style="text-align:center;">No hay usuarios disponibles.</td></tr>
                            <?php else: 
                                foreach($usuarios as $h): 
									if($h["id"]!=$_SESSION["usuario_id"]):
							?>
                                <tr>
                                    <td><?php echo $h['idUsuario']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($h['nombre']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($h['apellido']); ?></td>
                                    <td><?php echo htmlspecialchars($h['grado'] == 0 ? "Adm":"Emp"); ?></td>
                                    <td><?php echo htmlspecialchars($h['fecha_registro']); ?></td>
           
                                    <td>
                                        <a href="dashboard.php?action=delete&tipo=usuario&id=<?php echo $h['id']; ?>" class="btn btn-danger" onclick="return confirm('¿Seguro de eliminar este usuario?')">Eliminar</a>
                                    </td>
                                    
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <td>
                       <a href="usuario.php">Add Usuario</a>
                    </td>
                    
                </div>
				<?php endif;?>

            </div>

        </div>
    </div>

</div>

</body>

</html>
