<?php
require_once __DIR__ . '/includes/funciones.php';
requerir_admin();

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

try {
    $stmt = $conexion->prepare(
        'UPDATE usuarios SET archivado_en = NULL WHERE id = :id AND archivado_en IS NOT NULL'
    );
    $stmt->execute(['id' => $id]);
    if ($stmt->rowCount() === 1) {
        flash('ok', 'Cuenta reactivada correctamente.');
    } else {
        flash('error', 'El usuario no existe o ya estaba activo.');
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    flash('error', 'No se pudo reactivar la cuenta.');
}
redirigir('usuarios_archivados.php');
