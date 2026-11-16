<?php
require_once __DIR__ . '/includes/funciones.php';
$admin = requerir_admin();

// El id llega por GET (al abrir) o por POST (al guardar); siempre se valida como entero
$id = filter_var($_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['id'] ?? null) : ($_GET['id'] ?? null),
                 FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    http_response_code(400);
    exit('Identificador inválido.');
}

$stmt = $conexion->prepare(
    'SELECT id, nombre, apellido, dni, email, rol FROM usuarios WHERE id = :id AND archivado_en IS NULL LIMIT 1'
);
$stmt->execute(['id' => $id]);
$usuario = $stmt->fetch();
if ($usuario === false) {
    flash('error', 'El usuario no existe o está archivado.');
    redirigir('admin_usuarios.php');
}

$errores = [];
$nombre = $usuario['nombre'];
$apellido = $usuario['apellido'];
$dni = $usuario['dni'];
$email = $usuario['email'];
$rol = $usuario['rol'];
$esUnoMismo = ((int) $usuario['id'] === (int) $admin['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $errores[] = 'La solicitud no es válida. Recargá la página e intentá de nuevo.';
    } else {
        $nombre   = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $dni      = trim($_POST['dni'] ?? '');
        $email    = mb_strtolower(trim($_POST['email'] ?? ''));
        $rol      = $esUnoMismo ? $usuario['rol'] : ($_POST['rol'] ?? $usuario['rol']);  // no podés cambiar tu propio rol
        $password = $_POST['password'] ?? '';
        $confirmar = $_POST['confirmar'] ?? '';

        $errores = validar_datos_usuario($nombre, $apellido, $dni, $email);
        if (!in_array($rol, ['usuario', 'admin'], true)) {
            $errores[] = 'El rol seleccionado no es válido.';
        }
        if ($password !== '' || $confirmar !== '') {
            $errores = array_merge($errores, validar_password($password, $confirmar));
        }
        if (!$errores) {
            $errores = errores_duplicados($conexion, $dni, $email, $id);
        }
        if (!$errores) {
            try {
                $sql = 'UPDATE usuarios SET nombre = :nombre, apellido = :apellido, dni = :dni,
                               email = :email, rol = :rol';
                $params = ['nombre' => $nombre, 'apellido' => $apellido, 'dni' => $dni,
                           'email' => $email, 'rol' => $rol, 'id' => $id];
                if ($password !== '') {
                    $sql .= ', password_hash = :hash';
                    $params['hash'] = password_hash($password, PASSWORD_DEFAULT);
                }
                $sql .= ' WHERE id = :id AND archivado_en IS NULL';
                $conexion->prepare($sql)->execute($params);
                flash('ok', 'Datos actualizados correctamente.');
                redirigir('admin_usuarios.php');
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $errores[] = 'El DNI o el correo ya están registrados.';
                } else {
                    error_log($e->getMessage());
                    $errores[] = 'Ocurrió un error al guardar los cambios.';
                }
            }
        }
    }
}

$titulo = 'Editar usuario';
require __DIR__ . '/includes/header.php';
?>
<h1>Editar usuario</h1>
<?php if ($errores): ?>
  <div class="aviso aviso-error"><ul><?php foreach ($errores as $e): ?><li><?= escapar($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<form class="formulario" action="editar_usuario.php" method="POST" novalidate>
  <?= csrf_campo() ?>
  <input type="hidden" name="id" value="<?= (int) $id ?>">
  <label for="nombre">Nombre</label>
  <input type="text" id="nombre" name="nombre" maxlength="80" required value="<?= escapar($nombre) ?>">
  <label for="apellido">Apellido</label>
  <input type="text" id="apellido" name="apellido" maxlength="80" required value="<?= escapar($apellido) ?>">
  <label for="dni">DNI</label>
  <input type="text" id="dni" name="dni" inputmode="numeric" pattern="[0-9]{7,8}" maxlength="8" required value="<?= escapar($dni) ?>">
  <label for="email">Correo electrónico</label>
  <input type="email" id="email" name="email" maxlength="254" required value="<?= escapar($email) ?>">
  <label for="rol">Rol</label>
  <select id="rol" name="rol" <?= $esUnoMismo ? 'disabled' : '' ?>>
    <?php foreach (['usuario', 'admin'] as $r): ?>
      <option value="<?= $r ?>" <?= $rol === $r ? 'selected' : '' ?>><?= $r ?></option>
    <?php endforeach; ?>
  </select>
  <?php if ($esUnoMismo): ?><p class="ayuda">No podés cambiar tu propio rol.</p><?php endif; ?>
  <label for="password">Nueva contraseña (opcional)</label>
  <input type="password" id="password" name="password" autocomplete="new-password">
  <p class="ayuda">Dejala vacía para no cambiarla. Mínimo 8 caracteres.</p>
  <label for="confirmar">Repetir nueva contraseña</label>
  <input type="password" id="confirmar" name="confirmar" autocomplete="new-password">
  <button class="boton" type="submit">Guardar cambios</button>
  <a class="boton boton-linea" href="admin_usuarios.php">Cancelar</a>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
