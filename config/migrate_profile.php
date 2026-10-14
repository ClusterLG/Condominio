<?php
// config/migrate_profile.php
require_once __DIR__ . '/db.php';
$pdo = getDBConnection();

$cols = [
    'cedula_identidad' => "VARCHAR(30) NULL AFTER propietario_nombre",
    'telefono_secundario' => "VARCHAR(50) NULL AFTER telefono",
    'condicion_ocupacion' => "ENUM('propietario', 'arrendatario', 'residente') DEFAULT 'propietario' AFTER email",
    'habitantes_cant' => "INT DEFAULT 1 AFTER condicion_ocupacion",
    'vehiculos_info' => "VARCHAR(255) NULL AFTER habitantes_cant",
    'mascotas_info' => "VARCHAR(255) NULL AFTER vehiculos_info",
    'contacto_emergencia' => "VARCHAR(150) NULL AFTER mascotas_info",
    'observaciones' => "TEXT NULL AFTER contacto_emergencia"
];

foreach ($cols as $col => $def) {
    try {
        $pdo->exec("ALTER TABLE apartamentos ADD COLUMN {$col} {$def}");
        echo "Agregada columna {$col}\n";
    } catch (Exception $e) {
        // Columna ya existe
    }
}

// Actualizar datos de prueba en algunos apartamentos para que se vean de inmediato
$pdo->exec("UPDATE apartamentos SET 
    cedula_identidad = 'V-14.852.963',
    telefono_secundario = '0414-9988776',
    condicion_ocupacion = 'propietario',
    habitantes_cant = 3,
    vehiculos_info = 'Toyota Corolla Blanco (Placa AB123CD) - Puesto #02',
    mascotas_info = '1 Caniche (Milo)',
    contacto_emergencia = 'Carlos Mendoza (Hermano) - 0424-1122334'
    WHERE codigo = 'PB-A'");

$pdo->exec("UPDATE apartamentos SET 
    cedula_identidad = 'V-18.420.111',
    telefono_secundario = '0424-5551234',
    condicion_ocupacion = 'arrendatario',
    habitantes_cant = 2,
    vehiculos_info = 'Chevrolet Spark Gris (Placa XY987ZT) - Puesto #10',
    mascotas_info = 'Ninguna',
    contacto_emergencia = 'Elena Silva (Madre) - 0416-9998877'
    WHERE codigo = '1A'");

echo "Migración completada con éxito.\n";
