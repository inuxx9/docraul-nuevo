<?php
// recepcionista/dashboard.php (Unificado para Listar y Asignar Citas)
session_start();
require_once '../config/db.php'; 

// --- 1. VERIFICACIÓN DE SESIÓN ---
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'recepcionista') {
    header('Location: ../login/login.php'); 
    exit;
}

$error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
$success = isset($_SESSION['success']) ? $_SESSION['success'] : null;
unset($_SESSION['error'], $_SESSION['success']); // Limpiar mensajes de sesión

// --- 2. PROCESAMIENTO DEL FORMULARIO DE ASIGNACIÓN ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cita_id']) && isset($_POST['dentista_id'])) {
    
    $cita_id = (int)($_POST['cita_id'] ?? 0);
    $dentista_id = (int)($_POST['dentista_id'] ?? 0); 

    if ($cita_id === 0 || $dentista_id === 0) {
        $_SESSION['error'] = "❌ Error: Debes seleccionar un dentista válido para asignar la cita #{$cita_id}.";
    } else {
        try {
            // Actualizar la cita: asignar dentista y cambiar estado a 'Confirmada'
            $sql = "UPDATE citas SET dentista_id = ?, estado = 'Confirmada' WHERE cita_id = ? AND estado = 'Pendiente'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$dentista_id, $cita_id]);
            
            if ($stmt->rowCount() > 0) {
                $stmt_dentista = $pdo->prepare("SELECT nombre FROM dentistas WHERE dentista_id = ?");
                $stmt_dentista->execute([$dentista_id]);
                $dentista = $stmt_dentista->fetchColumn();

                $_SESSION['success'] = "✅ Cita ID #{$cita_id} asignada y **Confirmada** con el Dr(a). {$dentista}.";
            } else {
                $_SESSION['error'] = "❌ No se pudo asignar la cita. Podría no estar pendiente o no existir.";
            }
        
        } catch (PDOException $e) {
            error_log("Error al asignar dentista: " . $e->getMessage());
            $_SESSION['error'] = "❌ Error en la base de datos al intentar asignar la cita.";
        }
    }
    // Redireccionar para evitar reenvío del formulario (PRG pattern)
    header('Location: dashboard.php');
    exit;
}

// --- 3. OBTENER DATOS PARA MOSTRAR ---
$citas_pendientes = [];
$dentistas = [];
$error_bd = null;

try {
    // La consulta sigue obteniendo los datos por si los necesitas en otro lado,
    // pero en el HTML solo usaremos c.metodo_pago
    $sql_citas = "SELECT 
        c.cita_id, c.fecha_solicitud, c.hora_solicitud, c.motivo, c.metodo_pago, 
        cl.nombre AS cliente_nombre, cl.telefono AS cliente_telefono,
        t.nombre_titular
        FROM citas c
        JOIN clientes cl ON c.cliente_id = cl.cliente_id
        LEFT JOIN tarjeta t ON c.cita_id = t.cita_id
        WHERE c.estado = 'Pendiente'
        ORDER BY c.fecha_solicitud ASC, c.hora_solicitud ASC";
    
    $citas_pendientes = $pdo->query($sql_citas)->fetchAll(PDO::FETCH_ASSOC);

    $sql_dentistas = "SELECT dentista_id, nombre, especialidad FROM dentistas ORDER BY nombre ASC";
    $dentistas = $pdo->query($sql_dentistas)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error al cargar dashboard: " . $e->getMessage());
    $error_bd = "No se pudieron cargar los datos de citas. Por favor, verifica tu conexión a la BD.";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Recepcionista - Docraul</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

    <header class="bg-[#C62828] shadow-md p-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-white">Panel de Recepcionista 🦷</h1>
        <div class="flex items-center space-x-4">
            <span class="text-white text-sm hidden sm:inline">Hola, Recepcionista</span>
            <a href="../api/logout.php" class="bg-white text-[#C62828] font-semibold py-1 px-3 rounded-md hover:bg-gray-200 transition">
                Cerrar Sesión
            </a>
        </div>
    </header>

    <main class="container mx-auto mt-8 p-4">
        
        <div class="flex justify-between items-center mb-6 border-b pb-2">
            <h2 class="text-3xl font-semibold text-gray-800">Citas Pendientes (<?= count($citas_pendientes) ?>)</h2>
            
            <a href="agregar_dentista.php" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:bg-blue-700 transition">
                ➕ Agregar Nuevo Dentista
            </a>
        </div>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <?= htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>


        <?php if (isset($error_bd)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Error de Servidor:</strong>
                <span class="block sm:inline"><?= $error_bd ?></span>
            </div>
        <?php elseif (empty($citas_pendientes)): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">🎉 ¡No hay citas pendientes en este momento!</span>
            </div>
        <?php else: ?>
            
            <div class="bg-white shadow-lg rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Cita</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente (Contacto)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método Pago</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Asignar Dentista</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($citas_pendientes as $cita): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900"><?= htmlspecialchars($cita['cita_id']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <strong><?= date('d/m/Y', strtotime($cita['fecha_solicitud'])) ?></strong><br>
                                <?= date('H:i', strtotime($cita['hora_solicitud'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="font-medium"><?= htmlspecialchars($cita['cliente_nombre']) ?></span><br>
                                Tel: <?= htmlspecialchars($cita['cliente_telefono']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <span class="font-bold">
                                    <?= htmlspecialchars($cita['metodo_pago'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 max-w-xs overflow-hidden text-sm text-gray-700"><?= htmlspecialchars($cita['motivo']) ?></td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                <form action="asignar_dentista.php" method="POST" class="flex flex-col space-y-2">
                                    <input type="hidden" name="cita_id" value="<?= $cita['cita_id'] ?>">
                                    <select name="dentista_id" required class="border rounded-md p-2 text-sm focus:ring-[#C62828] focus:border-[#C62828]">
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($dentistas as $dentista): ?>
                                            <option value="<?= $dentista['dentista_id'] ?>">
                                                <?= htmlspecialchars($dentista['nombre']) ?> (<?= htmlspecialchars($dentista['especialidad']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="accion" value="asignar" 
                                            class="bg-green-600 text-white py-1.5 rounded-md text-xs font-semibold hover:bg-green-700 transition">
                                        Asignar y Confirmar
                                    </button>
                                </form>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <form action="../api/rechazar_citas.php" method="POST">
                                    <input type="hidden" name="cita_id" value="<?= $cita['cita_id'] ?>">
                                    <button type="submit" name="accion" value="rechazar" 
                                            onclick="return confirm('¿Estás segura de rechazar esta cita?');"
                                            class="bg-red-500 text-white py-1 px-3 rounded-md text-xs font-semibold hover:bg-red-600 transition">
                                        Rechazar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
        <?php endif; ?>

    </main>

</body>
</html>