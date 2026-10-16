<?php
// config/migrate_admin_profile.php
require_once __DIR__ . '/db.php';
$pdo = getDBConnection();

$cols = [
    'cedula' => "VARCHAR(30) NULL AFTER email",
    'telefono' => "VARCHAR(50) NULL AFTER cedula",
    'telefono_secundario' => "VARCHAR(50) NULL AFTER telefono",
    'cargo' => "VARCHAR(100) NULL DEFAULT 'Administrador de Condominio' AFTER telefono_secundario"
];

foreach ($cols as $col => $def) {
    try {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN {$col} {$def}");
        echo "Agregada columna {$col}\n";
    } catch (Exception $e) {
        echo "Columna {$col} ya existe o no se pudo agregar: " . $e->getMessage() . "\n";
    }
}

// Datos de demostración iniciales para el administrador local si están vacíos
try {
    $pdo->exec("UPDATE usuarios SET 
        cedula = 'V-15.678.901', 
        telefono = '0414-9876543', 
        telefono_secundario = '0212-9876543',
        cargo = 'Administrador Principal de la Junta'
        WHERE rol = 'admin' AND (cedula IS NULL OR cedula = '')");
    echo "Datos de demostración actualizados para admin.\n";
} catch (Exception $e) {
    echo "Error actualizando admin: " . $e->getMessage() . "\n";
}

$colsNow = $pdo->query("DESCRIBE usuarios")->fetchAll(PDO::FETCH_COLUMN);
echo "Columnas en tabla usuarios: " . implode(', ', $colsNow) . "\n";
