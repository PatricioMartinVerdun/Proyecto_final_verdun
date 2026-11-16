<?php
require_once __DIR__ . '/includes/funciones.php';

if (!empty($_SESSION['usuario_id'])) {
    redirigir('panel.php');
}

$errores = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $errores[] = 'La solicitud no es válida. Recargá la página e intentá de nuevo.';
    } else {
        $email = mb_strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $errores[] = 'Completá el correo y la contraseña.';
        } else {
            $stmt = $conexion->prepare(
                'SELECT id, password_hash, rol, archivado_en FROM usuarios WHERE email = :email LIMIT 1'
            );
            $stmt->execute(['email' => $email]);
            $u = $stmt->fetch();

            if ($u !== false && password_verify($password, $u['password_hash'])) {
                if ($u['archivado_en'] !== null) {
                    $errores[] = 'Tu cuenta está archivada. Pedile a un administrador que la reactive.';
                } else {
                    session_regenerate_id(true);   // evita fijación de sesión
                    $_SESSION['usuario_id'] = (int) $u['id'];
                    $_SESSION['usuario_rol'] = $u['rol'];
                    redirigir($u['rol'] === 'admin' ? 'admin_usuarios.php' : 'panel.php');
                }
            } else {
           
                $errores[] = 'Correo o contraseña incorrectos.';
            }
        }
    }
}

$titulo = 'Ingresar';
require __DIR__ . '/includes/header.php';
?>
<h1>Ingresar</h1>
<?php mostrar_flash(); ?>
<?php if ($errores): ?>
  <div class="aviso aviso-error"><ul><?php foreach ($errores as $e): ?><li><?= escapar($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<form class="formulario" action="login.php" method="POST">
  <?= csrf_campo() ?>
  <label for="email">Correo electrónico</label>
  <input type="email" id="email" name="email" maxlength="254" required value="<?= escapar($email) ?>" autocomplete="username">
  <label for="password">Contraseña</label>
  <input type="password" id="password" name="password" required autocomplete="current-password">
  <button class="boton" type="submit">Ingresar</button>
</form>
<p>¿No tenés cuenta? <a href="registro.php">Creá una</a>.</p>
<?php require __DIR__ . '/includes/footer.php'; ?>
