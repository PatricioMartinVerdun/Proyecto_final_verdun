<?php

require_once __DIR__ . '/funciones.php';
$titulo = $titulo ?? 'Club Atlético River Plate';
$logueado = !empty($_SESSION['usuario_id']);
$esAdmin  = $logueado && (($_SESSION['usuario_rol'] ?? '') === 'admin');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= escapar($titulo) ?> | River Plate</title>
  <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<header class="cabecera">
  <div class="contenedor barra">
    <a class="marca" href="index.php">
      <span class="marca-banda" aria-hidden="true"></span>
      <span>River Plate</span>
    </a>
    <nav class="menu" aria-label="Principal">
      <a href="index.php">Inicio</a>
  

      <a href="estadio.php">Estadio</a>

    </nav>
    <nav class="menu menu-cuenta" aria-label="Cuenta">
      <?php if ($logueado): ?>
        <a href="panel.php">Mi panel</a>
        <?php if ($esAdmin): ?><a href="admin_usuarios.php">Administración</a><?php endif; ?>
        <a class="boton-chico" href="cerrar_sesion.php">Cerrar sesión</a>
      <?php else: ?>
        <a href="login.php">Ingresar</a>
        <a class="boton-chico" href="registro.php">Crear cuenta</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="contenedor">
