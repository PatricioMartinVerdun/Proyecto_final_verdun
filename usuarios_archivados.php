<?php
require_once __DIR__ . '/includes/funciones.php';
requerir_admin();

try {
    $usuarios = $conexion->query(
        'SELECT id, nombre, apellido, dni, email, rol, archivado_en
         FROM usuarios WHERE archivado_en IS NOT NULL ORDER BY archivado_en DESC'
    )->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('No se pudieron consultar los usuarios archivados.');
}

$titulo = 'Usuarios archivados';
require __DIR__ . '/includes/header.php';
?>
<h1>Usuarios archivados</h1>
<?php mostrar_flash(); ?>
<a class="boton boton-linea" href="admin_usuarios.php">Volver a usuarios activos</a>
<p><strong>Total:</strong> <?= count($usuarios) ?> archivado(s)</p>
<?php if (!$usuarios): ?>
  <p>No hay usuarios archivados.</p>
<?php else: ?>
<div class="tabla-wrap">
<table>
  <thead><tr><th>ID</th><th>Nombre</th><th>DNI</th><th>Correo</th><th>Rol</th><th>Archivado el</th><th>Acción</th></tr></thead>
  <tbody>
  <?php foreach ($usuarios as $u): ?>
    <tr>
      <td><?= escapar($u['id']) ?></td>
      <td><?= escapar($u['apellido'] . ', ' . $u['nombre']) ?></td>
      <td><?= escapar($u['dni']) ?></td>
      <td><?= escapar($u['email']) ?></td>
      <td><?= escapar($u['rol']) ?></td>
      <td><?= escapar($u['archivado_en']) ?></td>
      <td>
        <form class="en-linea" method="POST" action="reactivar_usuario.php"
              onsubmit="return confirm('¿Reactivar esta cuenta?');">
          <?= csrf_campo() ?>
          <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
          <button class="boton pequeno" type="submit">Reactivar</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
