<?php
// smartgate_eventos/get_event_pic.php
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

$uri = trim($_GET['uri'] ?? '');
if ($uri === '') { http_response_code(400); echo "Falta uri"; exit; }

// ✅ Reutiliza tu script ya hecho (ajusta ruta si está en otro lado)
require_once __DIR__ . '/../smartgate/php/ver_foto_evento.php';
