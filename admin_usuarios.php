<?php
require_once __DIR__ . '/includes/funciones.php';
$admin = requerir_admin();   // solo rol admin

$buscar = trim($_GET['buscar'] ?? '');
if (mb_strlen($buscar) > 100) {
    $buscar = mb_substr($buscar, 0, 100);
}

$sql = 'SELECT id, nombre, apellido, dni, email, rol, creado_en
        FROM usuarios WHERE archivado_en IS NULL';
$parametros = [];
if ($buscar !== '') {
    $sql .= ' AND (nombre LIKE :nombre OR apellido LIKE :apellido OR dni LIKE :dni OR email LIKE :email)';
    $termino = '%' . addcslashes($buscar, '%_\\') . '%';
    $parametros = ['nombre' => $termino, 'apellido' => $termino, 'dni' => $termino, 'email' => $termino];
}
$sql .= ' ORDER BY apellido, nombre';

try {
    $stmt = $conexion->prepare($sql);
    $stmt->execute($parametros);
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('No se pudieron consultar los usuarios.');
}

$titulo = 'Administración de usuarios';
require __DIR__ . '/includes/header.php';
?>
<h1>Administración de usuarios</h1>
<?php mostrar_flash(); ?>
<div class="acciones">
  <a class="boton" href="crear_usuario.php">Registrar usuario</a>
  <a class="boton boton-negro" href="usuarios_archivados.php">Ver archivados</a>
</div>

<form class="buscador" method="GET" action="admin_usuarios.php" role="search">
  <input type="search" name="buscar" maxlength="100" value="<?= escapar($buscar) ?>"
         placeholder="Buscar por nombre, apellido, DNI o correo" aria-label="Buscar usuarios">
  <button class="boton" type="submit">Buscar</button>
  <?php if ($buscar !== ''): ?><a class="boton boton-linea" href="admin_usuarios.php">Limpiar</a><?php endif; ?>
</form>

<p><strong>Total:</strong> <?= count($usuarios) ?> usuario(s) activo(s)</p>

<?php if (!$usuarios): ?>
  <p>No se encontraron usuarios.</p>
<?php else: ?>
<div class="tabla-wrap">
<table>
  <thead>
    <tr><th>ID</th><th>Nombre</th><th>DNI</th><th>Correo</th><th>Rol</th><th>Alta</th><th>Acciones</th></tr>
  </thead>
  <tbody>
  <?php foreach ($usuarios as $u): ?>
    <tr>
      <td><?= escapar($u['id']) ?></td>
      <td><?= escapar($u['apellido'] . ', ' . $u['nombre']) ?></td>
      <td><?= escapar($u['dni']) ?></td>
      <td><?= escapar($u['email']) ?></td>
      <td><span class="etiqueta <?= $u['rol'] === 'admin' ? 'etiqueta-admin' : '' ?>"><?= escapar($u['rol']) ?></span></td>
      <td><?= escapar($u['creado_en']) ?></td>
      <td>
        <div class="acciones">
          <a class="boton pequeno boton-linea" href="editar_usuario.php?id=<?= (int) $u['id'] ?>">Editar</a>
          <?php if ((int) $u['id'] !== (int) $admin['id']): ?>
          <form class="en-linea" method="POST" action="archivar_usuario.php"
                onsubmit="return confirm('¿Archivar a este usuario? Podrás reactivarlo después.');">
            <?= csrf_campo() ?>
            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
            <button class="boton pequeno" type="submit">Archivar</button>
          </form>
          <?php endif; ?>
        </div>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
