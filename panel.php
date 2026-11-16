<?php
require_once __DIR__ . '/includes/funciones.php';
$u = requerir_login();  

$titulo = 'Mi panel';
require __DIR__ . '/includes/header.php';
?>
<h1>Hola, <?= escapar($u['nombre']) ?></h1>
<?php mostrar_flash(); ?>
<h2>Tus datos</h2>
<dl class="ficha">
  <dt>Nombre y apellido</dt><dd><?= escapar($u['nombre'] . ' ' . $u['apellido']) ?></dd>
  <dt>DNI</dt><dd><?= escapar($u['dni']) ?></dd>
  <dt>Correo electrónico</dt><dd><?= escapar($u['email']) ?></dd>
  <dt>Rol</dt><dd><span class="etiqueta <?= $u['rol'] === 'admin' ? 'etiqueta-admin' : '' ?>"><?= escapar($u['rol']) ?></span></dd>
  <dt>Miembro desde</dt><dd><?= escapar($u['creado_en']) ?></dd>
</dl>
<?php if ($u['rol'] === 'admin'): ?>
  <a class="boton" href="admin_usuarios.php">Ir a la administración de usuarios</a>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
