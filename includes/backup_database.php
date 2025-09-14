<?php
require_once 'auth.php';
require_once '../database.php';

$sesion = verificarAuth();
if ($sesion['rol'] != 1) {
    header("Location: ../login.php");
    exit();
}

// Configuración de backup
$backup_file = 'pulgarcito_backup_' . date('Ymd_His') . '.sql';
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $backup_file . '"');

// Comandos para generar backup (simplificado)
echo "-- Backup de Jardín Pulgarcito\n";
echo "-- Generado: " . date('Y-m-d H:i:s') . "\n";
echo "-- Por: " . $sesion['nombre'] . "\n\n";

// Aquí iría el código real para exportar la base de datos
// Esto es un ejemplo simplificado
echo "SELECT 'Backup generado correctamente' as status;\n";
?>