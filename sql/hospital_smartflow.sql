<?php
require_once __DIR__ . '/conexion.php';

function json_out($data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    exit;
}

function json_in(): array
{
    $d = json_decode(file_get_contents('php://input'), true);
    return is_array($d) ? $d : [];
}
