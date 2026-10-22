<?php
// Prueba de conexión + resumen rápido. Abre http://localhost/hospital_smartflow/
require_once __DIR__ . '/includes/conexion.php';
$pdo   = Conexion::get();
$salas = $pdo->query('SELECT * FROM v_ocupacion_actual ORDER BY id_sala')->fetchAll();
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8"><title>Hospital SmartFlow</title>
<style>
 body{font-family:system-ui,sans-serif;margin:2rem;color:#1a1a1a}
 table{border-collapse:collapse;width:100%;max-width:900px}
 th,td{border:1px solid #ccc;padding:.5rem .75rem;text-align:right}
 th{background:#f1f5f9} td:first-child,th:first-child{text-align:left}
 .ok{color:#0a7a3d;font-weight:600}
</style></head><body>
<h1>Hospital SmartFlow</h1>
<p class="ok">✔ Conexión a la base de datos exitosa</p>
<table>
<tr><th>Sala</th><th>Tipo</th><th>Piso</th><th>Total</th><th>Ocupadas</th><th>Críticas</th><th>Libres</th><th>% Ocupación</th><th>Espera prom. (min)</th></tr>
<?php foreach ($salas as $s): ?>
<tr><td><?= $h($s['sala']) ?></td><td><?= $h($s['tipo_cama']) ?></td><td><?= $h($s['piso']) ?></td>
<td><?= $h($s['total_camas']) ?></td><td><?= $h($s['ocupadas']) ?></td><td><?= $h($s['criticas']) ?></td>
<td><?= $h($s['libres']) ?></td><td><?= $h($s['tasa_ocupacion_pct']) ?>%</td><td><?= $h($s['espera_prom_min']) ?></td></tr>
<?php endforeach; ?>
</table>
<p>Endpoints: <a href="api/camas.php?limit=10">camas</a> · <a href="api/resumen.php">resumen</a> · <a href="api/historico.php">historico</a></p>
</body></html>
