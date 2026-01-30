<?php
// smartgate_eventos/weather.php
header('Content-Type: application/json; charset=utf-8');

$lat = $_GET['lat'] ?? null;
$lng = $_GET['lng'] ?? null;

if (!$lat || !$lng) {
  echo json_encode(["ok"=>false, "error"=>"Faltan lat/lng"]);
  exit;
}

$url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lng}&current=temperature_2m,weather_code&timezone=auto";

$resp = @file_get_contents($url);
if (!$resp) {
  echo json_encode(["ok"=>false, "error"=>"No se pudo obtener clima"]);
  exit;
}

$data = json_decode($resp, true);
$curr = $data["current"] ?? null;

echo json_encode([
  "ok" => true,
  "temp" => $curr["temperature_2m"] ?? null,
  "code" => $curr["weather_code"] ?? null,
  "time" => $curr["time"] ?? null
]);
