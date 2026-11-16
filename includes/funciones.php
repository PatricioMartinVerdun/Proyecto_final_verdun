<?php

require_once __DIR__ . '/../conexion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function escapar($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/** Token CSRF guardado en la sesión. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Campo oculto para incluir en formularios POST. */
function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . escapar(csrf_token()) . '">';
}

/** Valida el token recibido por POST. */
function csrf_valido(): bool
{
    $enviado = $_POST['csrf'] ?? '';
    return is_string($enviado) && isset($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $enviado);
}

/** Mensajes de una sola vez (se muestran y desaparecen). */
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function mostrar_flash(): void
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="aviso aviso-' . escapar($f['tipo']) . '">' . escapar($f['mensaje']) . '</div>';
    }
}

function redirigir(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Exige sesión iniciada. Vuelve a consultar la base para confirmar que la cuenta
 * existe, sigue activa y para tomar el rol real (no el de la sesión).
 */
function requerir_login(): array
{
    global $conexion;
    if (empty($_SESSION['usuario_id'])) {
        flash('error', 'Tenés que iniciar sesión para ver esa página.');
        redirigir('login.php');
    }
    $stmt = $conexion->prepare(
        'SELECT id, nombre, apellido, dni, email, rol, creado_en
         FROM usuarios WHERE id = :id AND archivado_en IS NULL LIMIT 1'
    );
    $stmt->execute(['id' => $_SESSION['usuario_id']]);
    $u = $stmt->fetch();
    if ($u === false) {
        $_SESSION = [];
        session_destroy();
        session_start();
        flash('error', 'Tu sesión ya no es válida. Iniciá sesión de nuevo.');
        redirigir('login.php');
    }
    $_SESSION['usuario_rol'] = $u['rol'];
    return $u;
}

/** Exige sesión + rol administrador. */
function requerir_admin(): array
{
    $u = requerir_login();
    if ($u['rol'] !== 'admin') {
        http_response_code(403);
        exit('Acceso denegado: esta sección es solo para administradores.');
    }
    return $u;
}
function validar_datos_usuario(string $nombre, string $apellido, string $dni, string $email): array
{
    $errores = [];
    if ($nombre === '' || mb_strlen($nombre) > 80) {
        $errores[] = 'El nombre es obligatorio (hasta 80 caracteres).';
    }
    if ($apellido === '' || mb_strlen($apellido) > 80) {
        $errores[] = 'El apellido es obligatorio (hasta 80 caracteres).';
    }
    if (!preg_match('/^[0-9]{7,8}$/', $dni)) {
        $errores[] = 'El DNI debe tener 7 u 8 números, sin puntos.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 254) {
        $errores[] = 'Ingresá un correo electrónico válido.';
    }
    return $errores;
}


function errores_duplicados(PDO $conexion, string $dni, string $email, int $excluirId = 0): array
{
    $errores = [];
    $stmt = $conexion->prepare('SELECT id FROM usuarios WHERE dni = :dni AND id <> :id LIMIT 1');
    $stmt->execute(['dni' => $dni, 'id' => $excluirId]);
    if ($stmt->fetch()) {
        $errores[] = 'Ese DNI ya está registrado.';
    }
    $stmt = $conexion->prepare('SELECT id FROM usuarios WHERE email = :email AND id <> :id LIMIT 1');
    $stmt->execute(['email' => $email, 'id' => $excluirId]);
    if ($stmt->fetch()) {
        $errores[] = 'Ese correo electrónico ya está registrado.';
    }
    return $errores;
}

function validar_password(string $password, string $confirmar): array
{
    $errores = [];
    if (mb_strlen($password) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }
    if ($password !== $confirmar) {
        $errores[] = 'Las contraseñas no coinciden.';
    }
    return $errores;
}
