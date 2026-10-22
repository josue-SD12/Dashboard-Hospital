<?php
/** KPIs actuales: ocupación por sala, totales, espera promedio y matriz de confusión. */
require_once __DIR__ . '/../includes/helpers.php';
$pdo = Conexion::get();

$salas = $pdo->query('SELECT * FROM v_ocupacion_actual ORDER BY id_sala')->fetchAll();

$tot = $pdo->query("SELECT COUNT(*) total,
        SUM(estado IN ('Ocupada','Crítica')) ocupadas,
        SUM(estado='Crítica') criticas,
        SUM(estado='Libre') libres,
        ROUND(100*SUM(estado IN ('Ocupada','Crítica'))/COUNT(*),1) tasa_ocupacion_pct,
        ROUND(100*SUM(estado='Crítica')/COUNT(*),1) tasa_criticas_pct,
        ROUND(AVG(espera_asignacion_min),1) espera_prom_min
        FROM camas")->fetch();

$mc  = $pdo->query('SELECT etiqueta, casos FROM matriz_confusion')->fetchAll(PDO::FETCH_KEY_PAIR);
$bsc = $pdo->query('SELECT dimension, kpi, meta FROM bsc')->fetchAll();
$kpi = $pdo->query('SELECT kpi, orden FROM kpi_meta ORDER BY orden')->fetchAll();

json_out(['totales' => $tot, 'por_sala' => $salas, 'matriz_confusion' => $mc, 'bsc' => $bsc, 'kpis' => $kpi]);
