<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$tipo_mensaje = "success";

function asignarImagen($categoria_id){
    switch(strval($categoria_id)){
        case "1":
            return "placeHolder_hamburguesa.png";
        case "2":
            return "placeHolderEntrantes.png";
        case "3":
            return "postrePlace.png";
        default:
            return "patatas_fritas.jpg";
    }
}

// --- PROCESAR ACCIONES DE CambiarDisponibilidad (SEGURO) ---
if (isset($_GET['cambiarDisp'])){
    $id = intval($_GET['cambiarDisp']);
    
    // Consulta segura con prepared statements
    $stmtDisp = $pdo->prepare("SELECT disponible FROM platos WHERE id = ? LIMIT 1");
    $stmtDisp->execute([$id]);
    $disp = $stmtDisp->fetch(PDO::FETCH_ASSOC);
    
    if ($disp) {
        $nuevo_estado = ($disp["disponible"] + 1) % 2;
        $stmt = $pdo->prepare("UPDATE platos SET disponible = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $id]);
    }
    
    header("Location: dashboard.php");
    exit;
}

// --- PROCESAR ACCIONES DE ELIMINACIÓN (SEGURO) ---
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
                
                $stmt = $pdo->prepare("INSERT INTO platos (nombre, precio, categoria_id, imagen, disponible) VALUES (?,?,?,?,?)");

                $disponible = isset($_POST["disponible"]) ? 1 : 0;
                
                // NOTA: Si vas a subir archivos reales, aquí deberías procesar $_FILES['imagen']
                $imagen = (!empty($_POST["imagen"])) ? $_POST["imagen"] : asignarImagen($categoria_id);

                $stmt->execute([$nombre, $precio, $categoria_id, $imagen, $disponible]);
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
                $mensaje = "Plato agregado con éxito.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $mensaje = "Error al agregar plato: " . $e->getMessage();
                $tipo_mensaje = "danger";
            }
        } else {
            $mensaje = "Por favor, rellene todos los campos obligatorios.";
            $tipo_mensaje = "danger";
        }
    } else if ($tipo == 'ingrediente' || $tipo == 'alergeno') {
        $nombre = trim($_POST['nombre_simple']);
        if (!empty($nombre)) {
            try {
                $tabla = ($tipo == 'ingrediente') ? 'ingredientes' : 'alergenos';
                // Nombre de tabla estático controlado por código, seguro frente a inyección
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

// --- CONSULTA FILTRADA SEGURA ---
$categoria_filtro = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;

if ($categoria_filtro > 0) {
    $query_hamb = "SELECT h.*, c.nombre AS categoria FROM platos h 
                   LEFT JOIN categorias c ON h.categoria_id = c.id 
                   WHERE h.categoria_id = ? 
                   ORDER BY h.id DESC";
    $stmt_platos = $pdo->prepare($query_hamb);
    $stmt_platos->execute([$categoria_filtro]);
    $platos = $stmt_platos->fetchAll(PDO::FETCH_ASSOC);
} else {
    $query_hamb = "SELECT h.*, c.nombre AS categoria FROM platos h 
                   LEFT JOIN categorias c ON h.categoria_id = c.id 
                   ORDER BY h.id DESC";
    $platos = $pdo->query($query_hamb)->fetchAll(PDO::FETCH_ASSOC);
}

$categorias = $pdo->query("SELECT * FROM categorias")->fetchAll(PDO::FETCH_ASSOC);
$ingredientes = $pdo->query("SELECT * FROM ingredientes")->fetchAll(PDO::FETCH_ASSOC);
$alergenos = $pdo->query("SELECT * FROM alergenos")->fetchAll(PDO::FETCH_ASSOC);
$usuarios  = $pdo->query("SELECT id, idUsuario, nombre, apellido, grado, fecha_registro FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);

// Verificar permisos de admin de manera segura
$id_sesion = intval($_SESSION['usuario_id']);
$stmt_admin = $pdo->prepare("SELECT id, grado FROM usuarios WHERE id = ?");
$stmt_admin->execute([$id_sesion]);
$admin = $stmt_admin->fetchAll(PDO::FETCH_ASSOC);
$es_admin = (count($admin) == 1);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control</title>
    <link rel="stylesheet" href="../archivos/css/dashboard.css">
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

    <div class="grid">
        <div class="row">
            
            <div class="col col-30">
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
                            <label>Imagen</label>
                            <input name="imagen" type="text" class="form-control" placeholder="nombre_imagen.png (o dejar vacío)">
                        </div>
                        <div class="subform">
                            <input name="disponible" type="checkbox" value="1" checked>
                            <label style="display:inline;">Disponible</label>
                        </div>
                        <div class="form-group">
                            <label>Ingredientes</label>
                            <div class="checkbox-group">
                                <?php foreach($ingredientes as $ing): ?>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="ingredientes[]" value="<?php echo $ing['id']; ?>"> <?php echo htmlspecialchars($ing['nombre']); ?>
                                    </label>
                                <?php endforeach; ?>
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

            <div class="col col-70">
                <div class="card" style="background-color: #ebedef;">
                    <form action="dashboard.php" method="GET">
                        <label style="font-weight: bold; margin-right: 10px;">Filtrar Lista por Categoría:</label>
                        <select name="cat_id" onchange="this.form.submit()" class="form-control" style="width: auto; display: inline-block;">
                            <option value="0">--- Mostrar Todas ---</option>
                            <?php foreach($categorias as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($categoria_filtro == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <a href="dashboard.php" class="btn btn-secondary" style="padding: 5px 10px;">Limpiar</a>
                    </form>
                </div>

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
                                <tr><td colspan="6" style="text-align:center;">No hay platos registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach($platos as $h): 
                                    // Consultas preparadas para los detalles
                                    $stmt_h_ing = $pdo->prepare("SELECT i.nombre FROM plato_ingredientes hi JOIN ingredientes i ON hi.ingrediente_id = i.id WHERE hi.plato_id = ?");
                                    $stmt_h_ing->execute([$h['id']]);
                                    $h_ingredientes = $stmt_h_ing->fetchAll(PDO::FETCH_COLUMN);

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
                                            <?php endforeach; ?>
                                        </div>
                                        <div style="margin-top: 5px;">
                                            <small><strong>Alér:</strong></small>
                                            <?php foreach($h_alergenos as $ha): ?>
                                                <span class="badge badge-ale"><?php echo htmlspecialchars($ha); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="dashboard.php?cambiarDisp=<?php echo $h['id']; ?>" class="<?php echo $h['disponible']==1 ? 'btn btn-success' : 'btn btn-secondary';?>">
                                            <?php echo $h['disponible'] == 1 ? "Disponible" : "No Disponible";?>
                                        </a>
                                        <a href="editar.php?id=<?php echo $h['id']; ?>" class="btn btn-warning">Modificar</a>
                                        <a href="dashboard.php?action=delete&tipo=plato&id=<?php echo $h['id']; ?>" class="btn btn-danger" onclick="return confirm('¿Seguro de eliminar este plato?')">Eliminar</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="grid">
                    <div class="row">
                        <div class="col" style="width: 50%; padding-left:0;">
                            <div class="card">
                                <h3>Ingredientes</h3>
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
                                            <td><a href="dashboard.php?action=delete&tipo=ingrediente&id=<?php echo $ing['id']; ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar?')">X</a></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col" style="width: 50%; padding-right:0;">
                            <div class="card">
                                <h3>Alérgenos</h3>
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
                                            <td><a href="dashboard.php?action=delete&tipo=alergeno&id=<?php echo $ale['id']; ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar?')">X</a></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if($es_admin): ?>
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
                        </thead>
                        <tbody>
                            <?php if(empty($usuarios)): ?>
                                <tr><td colspan="6" style="text-align:center;">No hay usuarios disponibles.</td></tr>
                            <?php else: 
                                foreach($usuarios as $u): 
                                    if($u["id"] != $_SESSION["usuario_id"]):
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($u['idUsuario']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($u['nombre']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($u['apellido']); ?></td>
                                    <td><?php echo htmlspecialchars($u['grado'] == 0 ? "Adm" : "Emp"); ?></td>
                                    <td><?php echo htmlspecialchars($u['fecha_registro']); ?></td>
                                    <td>
                                        <a href="dashboard.php?action=delete&tipo=usuario&id=<?php echo $u['id']; ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar usuario?')">Eliminar</a>
                                    </td>
                                </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div style="margin-top: 15px;">
                        <a href="usuario.php" class="btn btn-primary">Añadir Usuario</a>
                    </div>
                </div>
                <?php endif;?>

            </div>
        </div>
    </div>
</div>

</body>
</html>
