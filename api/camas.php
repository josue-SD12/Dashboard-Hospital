<?php
/** Histórico diario. Filtros opcionales: desde, hasta (YYYY-MM-DD), sala. */
require_once __DIR__ . '/../includes/helpers.php';
$pdo = Conexion::get();

$where = []; $p = [];
if (!empty($_GET['desde'])) { $where[] = 'h.fecha >= ?'; $p[] = $_GET['desde']; }
if (!empty($_GET['hasta'])) { $where[] = 'h.fecha <= ?'; $p[] = $_GET['hasta']; }
if (!empty($_GET['sala']))  { $where[] = 's.sala = ?';   $p[] = $_GET['sala']; }

$sql = 'SELECT h.fecha, s.tipo_cama, s.piso, s.sala, h.camas_ocupadas, h.camas_criticas
        FROM historico h JOIN salas s USING (id_sala)'
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . ' ORDER BY h.fecha, s.id_sala';
$st = $pdo->prepare($sql);
$st->execute($p);
json_out($st->fetchAll());
