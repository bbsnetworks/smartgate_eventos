<?php
// smartgate_eventos/eventRcv.php
header('Content-Type: application/json; charset=utf-8');

date_default_timezone_set('America/Mexico_City');

$rawBody = file_get_contents("php://input");
if (!$rawBody) {
  http_response_code(400);
  echo json_encode(["ok"=>false, "error"=>"Body vacío"]);
  exit;
}

$payload = json_decode($rawBody, true);
if (!is_array($payload)) {
  http_response_code(400);
  echo json_encode(["ok"=>false, "error"=>"JSON inválido"]);
  exit;
}

$logFile = __DIR__ . "/events.log";
if (!file_exists($logFile)) touch($logFile);

function append_event($logFile, $record) {
  $line = json_encode($record, JSON_UNESCAPED_UNICODE) . PHP_EOL;
  file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

function looks_like_on_event_notify($p) {
  return isset($p["method"]) && $p["method"] === "OnEventNotify" && isset($p["params"]["events"]) && is_array($p["params"]["events"]);
}

function normalize_one_event($evt, $fullPayload) {
  $data = $evt["data"] ?? [];

  return [
    "id"         => $evt["eventId"] ?? bin2hex(random_bytes(8)),  // mejor usar eventId si viene
    "eventId"    => $evt["eventId"] ?? null,
    "receivedAt" => date("c"),
    "sendTime"   => $fullPayload["params"]["sendTime"] ?? null,

    "eventType"  => $evt["eventType"] ?? null,
    "status"     => $evt["status"] ?? null,
    "happenTime" => $evt["happenTime"] ?? null,

    "srcType"    => $evt["srcType"] ?? null,
    "srcName"    => $evt["srcName"] ?? null,

    "personId"   => $data["personId"] ?? null,
    "personCode" => $data["personCode"] ?? null,
    "cardNo"     => $data["cardNo"] ?? null,

    "readerName" => $data["readerName"] ?? null,
    "picUri"     => $data["picUri"] ?? null,

    // Guardamos el raw completo por si luego ocupamos más campos
    "raw"        => [
      "method" => $fullPayload["method"] ?? null,
      "params" => $fullPayload["params"] ?? null,
      "event"  => $evt
    ]
  ];
}
function trim_log_keep_last_lines($logFile, $maxLines = 500) {
  // Si el archivo es pequeño, no hagas nada (ahorra I/O)
  $size = @filesize($logFile);
  if ($size !== false && $size < 1024 * 200) return; // <200KB

  $lines = @file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  if (!$lines) return;

  $count = count($lines);
  if ($count <= $maxLines) return;

  $lines = array_slice($lines, -$maxLines);
  file_put_contents($logFile, implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX);
}

// 1) Caso real: OnEventNotify (puede traer varios eventos)
if (looks_like_on_event_notify($payload)) {
  $events = $payload["params"]["events"];
  $saved = 0;

  foreach ($events as $evt) {
    $record = normalize_one_event($evt, $payload);

    // deduplicación básica por eventId (evita repeticiones tras restart/cache)
    if (!empty($record["eventId"])) {
      // muy simple: si el archivo es grande, esto no es ideal; pero para inicio funciona.
      $content = @file_get_contents($logFile);
      if ($content && str_contains($content, $record["eventId"])) {
        continue;
      }
    }

    append_event($logFile, $record);
    trim_log_keep_last_lines($logFile, 500);
    $saved++;
  }

  echo json_encode(["ok"=>true, "saved"=>$saved]);
  exit;
}

// 2) Caso de pruebas (tu curl “plano”)
$record = [
  "id"         => bin2hex(random_bytes(8)),
  "receivedAt" => date("c"),
  "eventType"  => $payload["eventType"] ?? null,
  "happenTime" => $payload["happenTime"] ?? null,
  "personName" => $payload["personName"] ?? null,
  "personCode" => $payload["personCode"] ?? null,
  "raw"        => $payload
];

append_event($logFile, $record);
echo json_encode(["ok"=>true, "saved"=>1]);
