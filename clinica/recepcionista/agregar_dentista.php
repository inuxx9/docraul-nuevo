<?php
// recepcionista/agregar_dentista.php
session_start();
// CRÍTICO: Incluir la conexión a la BD
require_once '../config/db.php'; 

// --- 1. VERIFICACIÓN DE SESIÓN ---
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'recepcionista') {
    header('Location: ../login/login.php'); 
    exit;
}

$error = null;
$success = null;

// --- 2. PROCESAMIENTO DEL FORMULARIO ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Extracción de datos (el nombre del input 'usuario' debe ser el email)
    $nombre = trim($_POST['nombre'] ?? '');
    $especialidad = trim($_POST['especialidad'] ?? '');
    $email_usuario = trim($_POST['email_usuario'] ?? ''); 
    $password = $_POST['password'] ?? '';

    // Validación básica
    if (empty($nombre) || empty($especialidad) || empty($email_usuario) || empty($password)) {
        $error = "❌ Error: Todos los campos obligatorios deben ser llenados.";
    } elseif (!filter_var($email_usuario, FILTER_VALIDATE_EMAIL)) {
        $error = "❌ Error: El email introducido no es válido.";
    } else {
        // Encriptar la contraseña para la tabla 'usuarios'
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            // Iniciar Transacción
            $pdo->beginTransaction();

            // A) Insertar en la tabla 'usuarios'
            $sql_user = "INSERT INTO usuarios (usuario, password, rol) VALUES (?, ?, 'dentista')";
            $stmt_user = $pdo->prepare($sql_user);
            $stmt_user->execute([$email_usuario, $hashed_password]);
            
            // Obtener el ID del nuevo usuario insertado
            $usuario_id = $pdo->lastInsertId();

            // B) Insertar en la tabla 'dentistas'
            // NOTA: Asegúrate que los nombres de las columnas coincidan con tu base de datos
            $sql_dentista = "INSERT INTO dentistas (nombre, especialidad, usuario_id) VALUES (?, ?, ?)";
            $stmt_dentista = $pdo->prepare($sql_dentista);
            $stmt_dentista->execute([$nombre, $especialidad, $usuario_id]);

            // Commit de la transacción
            $pdo->commit();

            $success = "✅ ¡El Dr(a). **{$nombre}** ha sido agregado con éxito! Su usuario es **{$email_usuario}**.";
            
            // Limpiar variables POST para que el formulario se vea vacío
            unset($nombre, $especialidad, $email_usuario, $password);

        } catch (PDOException $e) {
            $pdo->rollBack();
            // Error 23000 (Integridad) suele ser por duplicación (ej. email único)
            if ($e->getCode() == '23000') { 
                $error = "❌ Error: El correo electrónico **{$email_usuario}** ya está registrado como usuario.";
            } else {
                error_log("Error al agregar dentista: " . $e->getMessage());
                $error = "❌ Error en la base de datos. Intenta nuevamente o revisa logs.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Dentista - Docraul</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>.bg-docraul { background-color: #C62828; }</style>
</head>
<body class="bg-gray-100 min-h-screen">

    <header class="bg-docraul shadow-md p-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-white">Administración de Profesionales 🧑‍⚕️</h1>
        <div class="flex items-center space-x-4">
            <a href="dashboard.php" class="bg-blue-600 text-white font-semibold py-1 px-3 rounded-md hover:bg-blue-700 transition shadow-md">
                ⬅️ Volver al Panel
            </a>
            <a href="../api/logout.php" class="bg-white text-docraul font-semibold py-1 px-3 rounded-md hover:bg-gray-200 transition">
                Cerrar Sesión
            </a>
        </div>
    </header>

    <main class="container mx-auto mt-8 p-4">
        <div class="max-w-lg mx-auto bg-white p-8 rounded-lg shadow-xl border-t-4 border-docraul">
            <h2 class="text-3xl font-semibold text-gray-800 mb-6 text-center">Registrar Nuevo Dentista</h2>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    <?= $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <?= $error; ?>
                </div>
            <?php endif; ?>

            <form action="agregar_dentista.php" method="POST" class="space-y-6">
                <h3 class="text-xl font-medium text-gray-700 border-b pb-2">Datos del Profesional</h3>

                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre ?? '') ?>" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-docraul focus:border-docraul">
                </div>

                <div>
                    <label for="especialidad" class="block text-sm font-medium text-gray-700">Especialidad</label>
                    <select id="especialidad" name="especialidad" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-docraul focus:border-docraul">
                        <option value="" disabled selected>-- Selecciona --</option>
                        <option value="Odontología General" <?= (($especialidad ?? '') == 'Odontología General') ? 'selected' : '' ?>>Odontología General</option>
                        <option value="Ortodoncia" <?= (($especialidad ?? '') == 'Ortodoncia') ? 'selected' : '' ?>>Ortodoncia</option>
                        <option value="Endodoncia" <?= (($especialidad ?? '') == 'Endodoncia') ? 'selected' : '' ?>>Endodoncia</option>
                        <option value="Periodoncia" <?= (($especialidad ?? '') == 'Periodoncia') ? 'selected' : '' ?>>Periodoncia</option>
                        <option value="Cirugía Oral" <?= (($especialidad ?? '') == 'Cirugía Oral') ? 'selected' : '' ?>>Cirugía Oral</option>
                    </select>
                </div>

                <h3 class="text-xl font-medium text-gray-700 border-b pb-2 pt-4">Credenciales de Acceso</h3>

                <div>
                    <label for="email_usuario" class="block text-sm font-medium text-gray-700">Email (Usuario de Login)</label>
                    <input type="email" id="email_usuario" name="email_usuario" value="<?= htmlspecialchars($email_usuario ?? '') ?>" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-docraul focus:border-docraul">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Contraseña Temporal</label>
                    <input type="password" id="password" name="password" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-docraul focus:border-docraul">
                </div>

                <div>
                    <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md font-semibold hover:bg-green-700 transition">
                        Registrar Dentista y Usuario
                    </button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>