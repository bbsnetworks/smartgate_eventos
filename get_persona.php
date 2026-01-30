<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

require_once __DIR__ . '/../smartgate/php/conexion.php'; // <-- AJUSTA si tu conexión tiene otro nombre/ruta

$personCode = trim($_GET['personCode'] ?? '');
if ($personCode === '') {
  echo json_encode(["ok"=>false, "error"=>"personCode requerido"]);
  exit;
}

$stmt = $conexion->prepare("
  SELECT personCode, nombre, apellido, tipo, department, `Inicio`, `Fin`
  FROM clientes
  WHERE personCode = ?
  LIMIT 1
");
$stmt->bind_param("s", $personCode);
$stmt->execute();
$res = $stmt->get_result();

if (!$row = $res->fetch_assoc()) {
  echo json_encode([
    "ok"=>true,
    "found"=>false,
    "status"=>"noreg",
    "title"=>"NO REGISTRADO",
    "message"=>"Pasa a mostrador a inscribirte.",
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

$tipo = $row["tipo"] ?? "clientes";
$nombreCompleto = trim(($row["nombre"] ?? '') . ' ' . ($row["apellido"] ?? ''));

$finStr = $row["Fin"] ?? null;
$inicioStr = $row["Inicio"] ?? null;

$now = new DateTime("now");
$fin = $finStr ? new DateTime($finStr) : null;
$inicio = $inicioStr ? new DateTime($inicioStr) : null;

// Empleados/gerencia: no mostramos fecha
if ($tipo === "empleados" || $tipo === "gerencia") {
  echo json_encode([
    "ok"=>true,
    "found"=>true,
    "status"=>"staff",
    "tipo"=>$tipo,
    "nombre"=>$row["nombre"],
    "apellido"=>$row["apellido"],
    "nombreCompleto"=>$nombreCompleto ?: $personCode,
    "title"=>($tipo==="gerencia" ? "BIENVENIDO(A) GERENCIA" : "BIENVENIDO(A)"),
    "message"=>"Que tengas un excelente día.",
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

// Clientes: validar membresía por Fin
if ($fin && $now <= $fin) {
  // días restantes
  $diff = $now->diff($fin);
  $daysLeft = (int)$diff->format('%a');

  // progreso opcional (si hay Inicio)
  $progress = null;
  if ($inicio && $inicio < $fin) {
    $total = $inicio->diff($fin)->days ?: 1;
    $used  = $inicio->diff($now)->days;
    $used = max(0, min($total, $used));
    $progress = (int) round(($used / $total) * 100);
  }
    $meses = ["ENE","FEB","MAR","ABR","MAY","JUN","JUL","AGO","SEP","OCT","NOV","DIC"];
    $finHuman = $fin->format('d') . " " . $meses[((int)$fin->format('n'))-1] . " " . $fin->format('Y');
  echo json_encode([
    "ok"=>true,
    "found"=>true,
    "status"=>"active",
    "tipo"=>$tipo,
    "nombre"=>$row["nombre"],
    "apellido"=>$row["apellido"],
    "nombreCompleto"=>$nombreCompleto ?: $personCode,
    "fin"=>$fin->format('Y-m-d H:i:s'),
    "finHuman"=>$finHuman,
    "daysLeft"=>$daysLeft,
    "progress"=>$progress, // puede ser null
    "title"=>"ACCESO PERMITIDO",
    "message"=>"Membresía activa",
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

// vencida (o sin fecha fin)
$finHuman = $fin ? $fin->format('d/m/Y') : null;

echo json_encode([
  "ok"=>true,
  "found"=>true,
  "status"=>"expired",
  "tipo"=>$tipo,
  "nombre"=>$row["nombre"],
  "apellido"=>$row["apellido"],
  "nombreCompleto"=>$nombreCompleto ?: $personCode,
  "fin"=>$finStr,
  "finHuman"=>$finHuman,
  "title"=>"SUSCRIPCIÓN VENCIDA",
  "message"=>"Pasa a mostrador a pagar.",
], JSON_UNESCAPED_UNICODE);
