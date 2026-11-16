<?php
require_once __DIR__ . '/includes/funciones.php';

if (!empty($_SESSION['usuario_id'])) {
    redirigir('panel.php');
}

$errores = [];
$nombre = $apellido = $dni = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $errores[] = 'La solicitud no es válida. Recargá la página e intentá de nuevo.';
    } else {
        $nombre    = trim($_POST['nombre'] ?? '');
        $apellido  = trim($_POST['apellido'] ?? '');
        $dni       = trim($_POST['dni'] ?? '');
        $email     = mb_strtolower(trim($_POST['email'] ?? ''));
        $password  = $_POST['password'] ?? '';
        $confirmar = $_POST['confirmar'] ?? '';

        $errores = array_merge(
            validar_datos_usuario($nombre, $apellido, $dni, $email),
            validar_password($password, $confirmar)
        );
        if (!$errores) {
            $errores = errores_duplicados($conexion, $dni, $email);
        }
        if (!$errores) {
            try {
                $stmt = $conexion->prepare(
                    'INSERT INTO usuarios (nombre, apellido, dni, email, password_hash, rol)
                     VALUES (:nombre, :apellido, :dni, :email, :hash, :rol)'
                );
                $stmt->execute([
                    'nombre' => $nombre, 'apellido' => $apellido, 'dni' => $dni, 'email' => $email,
                    'hash' => password_hash($password, PASSWORD_DEFAULT),
                    'rol' => 'usuario',   // el registro público NUNCA crea administradores
                ]);
                flash('ok', 'Cuenta creada correctamente. Ya podés iniciar sesión.');
                redirigir('login.php');
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $errores[] = 'El DNI o el correo ya están registrados.';
                } else {
                    error_log($e->getMessage());
                    $errores[] = 'Ocurrió un error al guardar los datos. Intentá más tarde.';
                }
            }
        }
    }
}

$titulo = 'Crear cuenta';
require __DIR__ . '/includes/header.php';
?>
<h1>Crear cuenta</h1>
<?php if ($errores): ?>
  <div class="aviso aviso-error"><ul><?php foreach ($errores as $e): ?><li><?= escapar($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<form class="formulario" action="registro.php" method="POST" novalidate>
  <?= csrf_campo() ?>
  <label for="nombre">Nombre</label>
  <input type="text" id="nombre" name="nombre" maxlength="80" required value="<?= escapar($nombre) ?>">
  <label for="apellido">Apellido</label>
  <input type="text" id="apellido" name="apellido" maxlength="80" required value="<?= escapar($apellido) ?>">
  <label for="dni">DNI</label>
  <input type="text" id="dni" name="dni" inputmode="numeric" pattern="[0-9]{7,8}" maxlength="8" required value="<?= escapar($dni) ?>">
  <p class="ayuda">7 u 8 números, sin puntos.</p>
  <label for="email">Correo electrónico</label>
  <input type="email" id="email" name="email" maxlength="254" required value="<?= escapar($email) ?>">
  <label for="password">Contraseña</label>
  <input type="password" id="password" name="password" minlength="8" required autocomplete="new-password">
  <p class="ayuda">Mínimo 8 caracteres.</p>
  <label for="confirmar">Repetir contraseña</label>
  <input type="password" id="confirmar" name="confirmar" minlength="8" required autocomplete="new-password">
  <button class="boton" type="submit">Crear cuenta</button>
</form>
<p>¿Ya tenés cuenta? <a href="login.php">Iniciá sesión</a>.</p>
<?php require __DIR__ . '/includes/footer.php'; ?>