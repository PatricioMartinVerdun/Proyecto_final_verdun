<?php
require_once __DIR__ . '/includes/funciones.php';
$admin = requerir_admin();

// Solo POST: archivar nunca se hace con un enlace GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}
if (!csrf_valido()) {
    http_response_code(403);
    exit('Solicitud no válida (token CSRF).');
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    http_response_code(400);
    exit('Identificador inválido.');
}
if ($id === (int) $admin['id']) {
    flash('error', 'No podés archivar tu propia cuenta.');
    redirigir('admin_usuarios.php');
}

try {
    $stmt = $conexion->prepare(
        'UPDATE usuarios SET archivado_en = CURRENT_TIMESTAMP WHERE id = :id AND archivado_en IS NULL'
    );
    $stmt->execute(['id' => $id]);
    if ($stmt->rowCount() === 1) {
        flash('ok', 'Usuario archivado. Sus datos se conservan y podés reactivarlo.');
    } else {
        flash('error', 'El usuario no existe o ya estaba archivado.');
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    flash('error', 'No se pudo archivar el usuario.');
}
redirigir('admin_usuarios.php');
