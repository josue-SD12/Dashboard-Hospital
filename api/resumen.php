<?php
/**
 * CRUD de camas.
 *   GET    api/camas.php                      -> lista (filtros: estado, tipo_cama, piso, sala, page, limit)
 *   GET    api/camas.php?id=C-100             -> una cama
 *   POST   api/camas.php        (JSON)        -> crear
 *   PUT    api/camas.php?id=C-100 (JSON)      -> actualizar
 *   DELETE api/camas.php?id=C-100             -> eliminar
 */
require_once __DIR__ . '/../includes/helpers.php';

$pdo    = Conexion::get();
$method = $_SERVER['REQUEST_METHOD'];
$id     = $_GET['id'] ?? null;

const ESTADOS = ['Libre', 'Ocupada', 'Crítica', 'En limpieza', 'Reservada'];

try {
    switch ($method) {
        case 'GET':
            if ($id) {
                $st = $pdo->prepare('SELECT * FROM v_camas_detalle WHERE id_cama = ?');
                $st->execute([$id]);
                $row = $st->fetch();
                $row ? json_out($row) : json_out(['error' => 'Cama no encontrada'], 404);
            }
            $where = []; $params = [];
            foreach (['estado', 'tipo_cama', 'piso', 'sala'] as $f) {
                if (isset($_GET[$f]) && $_GET[$f] !== '') {
                    $where[]  = "$f = ?";          // $f viene de lista blanca
                    $params[] = $_GET[$f];
                }
            }
            $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $limit  = max(1, min(500, (int)($_GET['limit'] ?? 50)));
            $page   = max(1, (int)($_GET['page'] ?? 1));
            $offset = ($page - 1) * $limit;

            $st = $pdo->prepare("SELECT COUNT(*) FROM v_camas_detalle $sqlWhere");
            $st->execute($params);
            $total = (int)$st->fetchColumn();

            $st = $pdo->prepare("SELECT * FROM v_camas_detalle $sqlWhere ORDER BY id_cama LIMIT $limit OFFSET $offset");
            $st->execute($params);
            json_out(['total' => $total, 'page' => $page, 'limit' => $limit, 'data' => $st->fetchAll()]);

        case 'POST':
            $d = json_in();
            foreach (['id_cama', 'id_sala', 'estado', 'edad_paciente', 'espera_asignacion_min'] as $k) {
                if (!isset($d[$k])) json_out(['error' => "Falta el campo $k"], 422);
            }
            if (!in_array($d['estado'], ESTADOS, true)) json_out(['error' => 'Estado inválido'], 422);
            $st = $pdo->prepare('INSERT INTO camas (id_cama,id_sala,estado,horas_ocupada,edad_paciente,espera_asignacion_min)
                                 VALUES (?,?,?,?,?,?)');
            $st->execute([$d['id_cama'], $d['id_sala'], $d['estado'], $d['horas_ocupada'] ?? 0,
                          $d['edad_paciente'], $d['espera_asignacion_min']]);
            json_out(['ok' => true, 'id_cama' => $d['id_cama']], 201);

        case 'PUT':
            if (!$id) json_out(['error' => 'Falta id'], 400);
            $d = json_in();
            $permitidos = ['id_sala', 'estado', 'horas_ocupada', 'edad_paciente', 'espera_asignacion_min'];
            $sets = []; $params = [];
            foreach ($permitidos as $k) {
                if (array_key_exists($k, $d)) { $sets[] = "$k = ?"; $params[] = $d[$k]; }
            }
            if (!$sets) json_out(['error' => 'Nada que actualizar'], 422);
            if (isset($d['estado']) && !in_array($d['estado'], ESTADOS, true)) json_out(['error' => 'Estado inválido'], 422);
            $params[] = $id;
            $st = $pdo->prepare('UPDATE camas SET ' . implode(', ', $sets) . ' WHERE id_cama = ?');
            $st->execute($params);
            json_out(['ok' => true, 'filas' => $st->rowCount()]);

        case 'DELETE':
            if (!$id) json_out(['error' => 'Falta id'], 400);
            $st = $pdo->prepare('DELETE FROM camas WHERE id_cama = ?');
            $st->execute([$id]);
            $st->rowCount() ? json_out(['ok' => true]) : json_out(['error' => 'Cama no encontrada'], 404);

        default:
            json_out(['error' => 'Método no permitido'], 405);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    json_out(['error' => 'Error en la base de datos', 'detalle' => $e->getCode()], 500);
}
