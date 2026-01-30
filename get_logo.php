<?php
// smartgate_eventos/get_logo.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../smartgate/php/conexion.php'; // ✅ ajusta ruta

try {
  $sql = "SELECT 
            app_name,
            dashboard_title,
            dashboard_sub,
            logo_blob,
            logo_mime,
            logo_etag
          FROM config_branding
          ORDER BY id DESC
          LIMIT 1";

  $res = $conexion->query($sql);
  if (!$res || $res->num_rows === 0) {
    echo json_encode(["ok"=>false, "error"=>"No hay config_branding"]);
    exit;
  }

  $row = $res->fetch_assoc();

  $mime = $row['logo_mime'] ?: 'image/png';
  $etag = $row['logo_etag'] ?: null;

  $dataUrl = null;
  if (!empty($row['logo_blob'])) {
    $b64 = base64_encode($row['logo_blob']);
    $dataUrl = "data:$mime;base64,$b64";
  }

  echo json_encode([
    "ok" => true,
    "app_name" => $row["app_name"] ?? null,
    "dashboard_title" => $row["dashboard_title"] ?? null,
    "dashboard_sub" => $row["dashboard_sub"] ?? null,
    "mime" => $mime,
    "etag" => $etag,
    "dataUrl" => $dataUrl
  ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["ok"=>false, "error"=>$e->getMessage()]);
}
