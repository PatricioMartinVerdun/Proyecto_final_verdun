<?php
$titulo = 'Inicio';
require __DIR__ . '/includes/header.php';
?>
<section class="portada">
  <h1>Club Atlético River Plate</h1>
  <p>Fundado el 25 de mayo de 1901 en La Boca y con casa en Núñez desde 1938. Una camiseta blanca con una banda roja que cruza el pecho.</p>
  <ul class="datos-rapidos">
    <li><strong>1901</strong>fundación</li>
    <li><strong>Núñez</strong>barrio del estadio</li>
    <li><strong>Millonarios</strong>apodo más conocido</li>
    <li><strong>4</strong>Copas Libertadores</li>
  </ul>
</section>

<?php mostrar_flash(); ?>

<section class="seccion">
  <h2>Conocé al club</h2>
  <div class="rejilla">
  

    <article class="tarjeta">
      <h3>Estadio Monumental</h3>
      <p>El estadio más grande de la Argentina, sede de la final del Mundial 1978.</p>
      <a href="estadio.php">Conocer el estadio</a>
    </article>
  
  </div>
</section>

<section class="seccion">
  <h2>Sumate a la comunidad del sitio</h2>
  <p>Creá una cuenta para tener tu panel personal con tus datos. El equipo administrador gestiona las cuentas desde un panel propio.</p>
  <?php if (empty($_SESSION['usuario_id'])): ?>
    <a class="boton" href="registro.php">Crear cuenta</a>
    <a class="boton boton-linea" href="login.php">Ya tengo cuenta</a>
  <?php else: ?>
    <a class="boton" href="panel.php">Ir a mi panel</a>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
?>