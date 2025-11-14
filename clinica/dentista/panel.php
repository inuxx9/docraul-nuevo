<?php
// dentista/panel.php
session_start(); // Asegúrate de que la sesión se inicie si no está en auth_dentista.php
require_once 'auth_dentista.php';
require_once '../config/db.php';

$citas_asignadas = [];
$error_bd = null;

// $dentista_usuario_id debe estar disponible desde 'auth_dentista.php'
if (!isset($dentista_usuario_id)) {
    header('Location: ../login/login.php');
    exit;
}

try {
    // 1. Obtener dentista_id
    $sql_dentista = "SELECT dentista_id, nombre FROM dentistas WHERE usuario_id = ?";
    $stmt_dentista = $pdo->prepare($sql_dentista);
    $stmt_dentista->execute([$dentista_usuario_id]);
    $dentista_data = $stmt_dentista->fetch(PDO::FETCH_ASSOC);

    if (!$dentista_data) {
        header('Location: ../api/logout.php'); 
        exit;
    }
    
    $dentista_id = $dentista_data['dentista_id'];
    $dentista_nombre = $dentista_data['nombre'];

    // 2. Obtener citas confirmadas (Las citas 'Finalizada' o 'Rechazada' se excluyen)
    $sql_citas = "SELECT 
        c.cita_id, c.fecha_solicitud, c.hora_solicitud, c.motivo, c.estado,
        cl.nombre AS cliente_nombre, cl.email AS cliente_email, cl.telefono AS cliente_telefono
        FROM citas c
        JOIN clientes cl ON c.cliente_id = cl.cliente_id
        WHERE c.dentista_id = :dentista_id AND c.estado = 'Confirmada'
        ORDER BY c.fecha_solicitud ASC, c.hora_solicitud ASC";
    
    $stmt_citas = $pdo->prepare($sql_citas);
    $stmt_citas->execute([':dentista_id' => $dentista_id]);
    $citas_asignadas = $stmt_citas->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error al cargar panel de dentista: " . $e->getMessage());
    $error_bd = "No se pudieron cargar las citas. Por favor, verifica la BD.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Dentista - Docraul</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

<header class="bg-blue-800 shadow-md p-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-white">Panel de Dentista 🩺</h1>
    <div class="flex items-center space-x-4">
        <span class="text-white text-sm hidden sm:inline">Hola, Dr(a). <?= htmlspecialchars($dentista_nombre) ?></span>
        <a href="../api/logout.php" class="bg-white text-blue-800 font-semibold py-1 px-3 rounded-md hover:bg-gray-200 transition">
            Cerrar Sesión
        </a>
    </div>
</header>

<main class="container mx-auto mt-8 p-4">
    
    <h2 class="text-3xl font-semibold text-gray-800 mb-6 border-b pb-2">
        Mis Citas Confirmadas (<?= count($citas_asignadas) ?>)
    </h2>

    <?php if (isset($error_bd)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <strong class="font-bold">Error de Servidor:</strong> <?= $error_bd ?>
        </div>

    <?php elseif (empty($citas_asignadas)): ?>
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative">
            🧘 No tienes citas asignadas por ahora.
        </div>

    <?php else: ?>

    <div class="bg-white shadow-lg rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3">ID Cita</th>
                    <th class="px-6 py-3">Fecha / Hora</th>
                    <th class="px-6 py-3">Paciente</th>
                    <th class="px-6 py-3">Motivo</th>
                    <th class="px-6 py-3">Contacto</th>
                    <th class="px-6 py-3 text-center">Estado</th>
                    <th class="px-6 py-3 text-center">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">

            <?php foreach ($citas_asignadas as $cita): ?>
                <tr class="hover:bg-blue-50">
                    <td class="px-6 py-4"><?= $cita['cita_id'] ?></td>
                    <td class="px-6 py-4"><?= date('d/m/Y', strtotime($cita['fecha_solicitud'])) ?><br><?= date('H:i', strtotime($cita['hora_solicitud'])) ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($cita['cliente_nombre']) ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($cita['motivo']) ?></td>
                    <td class="px-6 py-4">Email: <?= htmlspecialchars($cita['cliente_email']) ?><br>Tel: <?= htmlspecialchars($cita['cliente_telefono']) ?></td>
                    <td class="px-6 py-4 text-center"><span class="bg-green-200 text-green-900 px-2 py-1 rounded"><?= $cita['estado'] ?></span></td>

                    <td class="px-6 py-4 text-center">
                        <button onclick="eliminarCita(<?= $cita['cita_id'] ?>)" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Finalizar</button>
                    </td>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>
    </div>
    <?php endif; ?>
</main>

<script>
// FUNCIÓN MEJORADA PARA MANEJO DE ERRORES DE FETCH
function eliminarCita(cita_id) {
    // El mensaje de confirmación ha sido ajustado para reflejar que es una FINALIZACIÓN
    if (!confirm("¿Finalizar consulta? La cita se marcará como FINALIZADA.")) return;

    // Ejecutamos el fetch a tu script de acción
    fetch('eliminar_cita.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cita_id: cita_id }) 
    })
    .then(r => {
        // Manejo de errores HTTP (ej. 404, 500)
        if (!r.ok) {
            console.error('Error HTTP:', r.status, r.statusText);
            alert(`❌ Error de conexión al servidor: ${r.status}. Revisa el archivo eliminar_cita.php.`);
            return r.text().then(text => { throw new Error(text || r.statusText); });
        }
        return r.json(); // Intentamos parsear la respuesta JSON
    })
    .then(d => {
        // Manejo de respuesta JSON del servidor
        if (d.success) {
            alert("✅ Consulta finalizada correctamente.");
            location.reload(); // Recarga la página, ocultando la cita finalizada
        } else {
            // Muestra el mensaje de error devuelto por PHP
            console.error('Error de servidor (PHP):', d.message);
            alert("❌ Error: " + (d.message || "Error desconocido en el servidor."));
        }
    })
    .catch(error => {
        // Manejo de errores de red o JSON inválido
        console.error('Error en fetch o JSON inválido:', error);
        alert('❌ Error fatal al procesar la respuesta. Revisa la Consola del navegador (F12) para detalles.');
    });
}
</script>

</body>
</html>