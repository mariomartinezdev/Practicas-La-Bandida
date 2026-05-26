<?php
session_start();
require_once 'conexion.php';

if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit;
}

$errores = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $contra = trim($_POST['contra']);

    if (empty($usuario)) {
        $errores[] = "El campo de usuario es obligatorio.";
    }
    if (empty($contra)) {
        $errores[] = "La contraseña es obligatoria.";
    }

    if (empty($errores)) {
        try {
			
            $stmt = $pdo->prepare("SELECT id,idUsuario,password FROM usuarios WHERE idUsuario = :input LIMIT 1");
            $stmt->execute(['input' => $usuario]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
			
			
			//echo password_hash($password, PASSWORD_DEFAULT); Para generar contrasena
			
            if ($usuario && password_verify($contra, $usuario['password'])) {
                $_SESSION['usuario_id'] = $usuario['id'];

                header("Location: dashboard.php");
                exit;
                
            } else {
                $errores[] = "Usuario o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            $errores[] = "Error en el sistema. Inténtalo más tarde.";
        }
    }
}

?>

<!DOCTYPE html>

<html>

    <head>
        <title>Administracion</title>
        <link href="login.css" rel="stylesheet">
    </head>

    <body>

        <main>

            <form method="post" action="login.php">

                <img src="../archivos/LOGOTIPO PNG/LOGO COMPLETO LA BANDIDA_Mesa de trabajo 1 copia.png">
            
                <label>Administracion</label>

				<?php if (!empty($errores)): ?>
					<div class="error">
						<?php foreach ($errores as $error): ?>
							<p style="margin: 0;"><?php echo htmlspecialchars($error); ?></p>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

                <div>

                    <input class="campos" name="usuario" type="text" placeholder="Usuario">
                    <input class="campos" name="contra" type="password" placeholder="Contrasena">

                    <div>
                        <input class="boton" value="Acceder" type="submit">
                    </div>

                </div>

            </form>

        </main>

    </body>

</html>
