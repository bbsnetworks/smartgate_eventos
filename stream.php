<?php
header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache');
header('Pragma: no-cache');

@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', 0);
@ini_set('max_execution_time', 0);
@set_time_limit(0);

while (ob_get_level() > 0) { @ob_end_flush(); }
@ob_implicit_flush(true);

$logFile = __DIR__ . '/events.log';
if (!file_exists($logFile)) touch($logFile);

function sse_send($event, $id, $dataLine) {
  if ($id !== null && $id !== '') echo "id: {$id}\n";
  echo "event: {$event}\n";
  echo "data: {$dataLine}\n\n";
  flush();
}

// hello inicial
sse_send('hello', 'hello', '{}');

// si el browser manda Last-Event-ID (reconexiones)
$lastEventId = $_SERVER['HTTP_LAST_EVENT_ID'] ?? '';

$fp = fopen($logFile, 'r');
if (!$fp) exit;

// arrancamos al final (solo en vivo)
fseek($fp, 0, SEEK_END);
$lastPos  = ftell($fp);
$lastPing = time();

while (true) {
  // siempre refresca statcache (Windows lo necesita)
  clearstatcache(true, $logFile);

  $size = filesize($logFile);
  if ($size === false) $size = 0;

  // si el archivo se truncó/rotó
  if ($size < $lastPos) {
    fseek($fp, 0, SEEK_SET);
    $lastPos = 0;
  }

  // ✅ si creció y estamos en EOF, “reactiva” el stream
  if ($size > $lastPos) {
    fseek($fp, $lastPos, SEEK_SET); // reset EOF + retoma desde donde ibas
  }

  // lee todas las líneas nuevas disponibles
  while (($line = fgets($fp)) !== false) {
    $line = trim($line);
    if ($line === '') continue;

    // id estable desde JSON
    $id = null;
    $j = json_decode($line, true);
    if (is_array($j)) {
      $id = $j['eventId'] ?? $j['id'] ?? $j['receivedAt'] ?? null;
    }

    // evita repetir exactamente el mismo id al reconectar
    if ($lastEventId && $id && $id === $lastEventId) {
      continue;
    }

    sse_send('hik', $id, $line);
    $lastPos = ftell($fp);
  }

  // keepalive
  if (time() - $lastPing >= 15) {
    echo ": ping\n\n";
    flush();
    $lastPing = time();
  }

  usleep(200000);
}
