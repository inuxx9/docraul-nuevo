<?php
require_once "../config/db.php";
header("Content-Type: application/json");

if (!$pdo) {
    echo json_encode(["success" => false, "message" => "Error de conexión con la base de datos."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$nombre = $data['nombre'] ?? '';
$email = $data['email'] ?? '';
$telefono = $data['telefono'] ?? '';
$fecha = $data['fecha_solicitud'] ?? '';
$hora = $data['hora_solicitud'] ?? '';
$motivo = $data['motivo'] ?? '';
$metodo_pago = $data['metodo_pago'] ?? 'efectivo';

// Datos de tarjeta
$nombre_titular = $data['nombre_titular'] ?? null;
$numero_tarjeta = $data['numero_tarjeta'] ?? null;
$vencimiento = $data['vencimiento'] ?? null;
$cvv = $data['cvv'] ?? null;

try {
    // Buscar o crear cliente
    $stmt = $pdo->prepare("SELECT cliente_id FROM clientes WHERE email = ?");
    $stmt->execute([$email]);
    $cliente = $stmt->fetch();

    if ($cliente) {
        $cliente_id = $cliente['cliente_id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO clientes (nombre, email, telefono, fecha_registro) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$nombre, $email, $telefono]);
        $cliente_id = $pdo->lastInsertId();
    }

    // Insertar cita
    $stmt = $pdo->prepare("INSERT INTO citas (cliente_id, fecha_solicitud, hora_solicitud, motivo, estado, fecha_creacion, metodo_pago)
                           VALUES (?, ?, ?, ?, 'Pendiente', NOW(), ?)");
    $stmt->execute([$cliente_id, $fecha, $hora, $motivo, $metodo_pago]);
    $cita_id = $pdo->lastInsertId();

    // Insertar datos de tarjeta si aplica
    if ($metodo_pago === 'tarjeta') {
        $stmt = $pdo->prepare("INSERT INTO tarjeta (cita_id, nombre_titular, numero_tarjeta, vencimiento, cvv)
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$cita_id, $nombre_titular, $numero_tarjeta, $vencimiento, $cvv]);
    }

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
