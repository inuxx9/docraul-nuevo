<?php
// login/login.php
session_start();

// Obtener el mensaje de error de la sesión (si existe)
$error_message = null;
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    // Limpiar la variable de sesión después de mostrar el mensaje
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Docraul - Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Estilos base para centrar y usar tu color rojo corporativo */
        .bg-docraul {
            background-color: #C62828;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-sm">
        
        <h1 class="text-3xl font-bold text-center text-docraul mb-2">Acceso Docraul</h1>
        <p class="text-center text-gray-600 mb-6">Panel de Recepción</p>
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-sm" role="alert">
                <?= htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        <form action="../api/login_proceso.php" method="POST" class="space-y-6">
            
            <div>
                <label for="usuario" class="block text-sm font-medium text-gray-700">Usuario</label>
                <input type="email" name="usuario" id="usuario" required 
                       class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-docraul focus:border-docraul"
                       placeholder="ejemplo@docraul.com">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input type="password" name="password" id="password" required 
                       class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-3 focus:ring-docraul focus:border-docraul">
            </div>

            <button type="submit" class="w-full bg-docraul text-white font-semibold py-3 rounded-lg hover:bg-red-700 transition shadow-md">
                Iniciar Sesión
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            &larr; <a href="../index.html" class="hover:underline">Volver a la página principal</a>
        </p>

    </div>

</body>
</html>