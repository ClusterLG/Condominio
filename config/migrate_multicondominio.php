<?php
// config/migrate_multicondominio.php
require_once __DIR__ . '/db.php';

echo "=== INICIANDO MIGRACIÓN MULTI-CONDOMINIO ===\n";

$pdo = getDBConnection();

try {
    // 1. Crear tabla condominios
    $pdo->exec("CREATE TABLE IF NOT EXISTS `condominios` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `slug` VARCHAR(60) NOT NULL UNIQUE,
        `nombre` VARCHAR(150) NOT NULL,
        `rif` VARCHAR(30) NULL,
        `direccion` TEXT NULL,
        `pisos_cant` INT DEFAULT 12,
        `tiene_pb` TINYINT(1) DEFAULT 1,
        `aptos_pb_cant` INT DEFAULT 2,
        `aptos_por_piso` INT DEFAULT 4,
        `nomenclatura_tipo` VARCHAR(20) DEFAULT 'letras' COMMENT 'letras, numeros, personalizado',
        `tasa_dolar` DECIMAL(10,2) DEFAULT 72.40,
        `banco_pago_movil` TEXT NULL,
        `banco_transferencia` TEXT NULL,
        `cuenta_zelle` TEXT NULL,
        `monto_cuota_ordinaria_default` DECIMAL(10,2) DEFAULT 35.00,
        `activo` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "✔ Tabla 'condominios' verificada/creada.\n";

    // 2. Verificar si existe el primer condominio por defecto (Residencias Los Samanes)
    $stmtCheck = $pdo->query("SELECT COUNT(*) FROM `condominios` WHERE `id` = 1");
    if ($stmtCheck->fetchColumn() == 0) {
        $stmtInsert = $pdo->prepare("INSERT INTO `condominios` 
            (`id`, `slug`, `nombre`, `rif`, `direccion`, `pisos_cant`, `tiene_pb`, `aptos_pb_cant`, `aptos_por_piso`, `nomenclatura_tipo`, `tasa_dolar`, `banco_pago_movil`, `banco_transferencia`, `cuenta_zelle`, `monto_cuota_ordinaria_default`) 
            VALUES (1, 'los-samanes', 'Residencias Los Samanes', 'J-12345678-0', 'Av. Principal Los Samanes, Caracas', 12, 1, 2, 4, 'letras', 72.40, 
            'Banco Banesco | 0134 | V-12345678 | 0414-1234567', 
            'Banesco Cta Corriente: 0134-0000-00-0000000000 | RIF: J-12345678-0', 
            'condominio.samanes@gmail.com (Titular: Condominio)', 35.00)");
        $stmtInsert->execute();
        echo "✔ Condominio inicial 'Residencias Los Samanes' registrado con ID = 1.\n";
    }

    // 3. Añadir columna condominio_id a tablas existentes si no existen
    $tablas = [
        'apartamentos' => "INT NOT NULL DEFAULT 1 AFTER `id`",
        'usuarios' => "INT NULL DEFAULT 1 AFTER `id`",
        'cuotas' => "INT NOT NULL DEFAULT 1 AFTER `id`",
        'comunicados' => "INT NOT NULL DEFAULT 1 AFTER `id`"
    ];

    foreach ($tablas as $tabla => $colDef) {
        $checkCol = $pdo->query("SHOW COLUMNS FROM `{$tabla}` LIKE 'condominio_id'")->fetch();
        if (!$checkCol) {
            $pdo->exec("ALTER TABLE `{$tabla}` ADD COLUMN `condominio_id` {$colDef}");
            $pdo->exec("ALTER TABLE `{$tabla}` ADD CONSTRAINT `fk_{$tabla}_condominio` FOREIGN KEY (`condominio_id`) REFERENCES `condominios`(`id`) ON DELETE CASCADE");
            echo "✔ Columna 'condominio_id' agregada y vinculada en '{$tabla}'.\n";
        } else {
            echo "ℹ Columna 'condominio_id' ya existe en '{$tabla}'.\n";
        }
    }

    // 4. Modificar rol en usuarios para permitir super_admin
    $pdo->exec("ALTER TABLE `usuarios` MODIFY COLUMN `rol` ENUM('super_admin', 'admin') NOT NULL DEFAULT 'admin'");
    echo "✔ Rol de usuarios actualizado para admitir 'super_admin'.\n";

    // 5. Asignar usuario admin principal como super_admin
    $pdo->exec("UPDATE `usuarios` SET `rol` = 'super_admin', `condominio_id` = NULL WHERE `username` = 'admin'");
    echo "✔ Usuario 'admin' promovido a Super-Administrador global.\n";

    // 6. Eliminar restricción UNIQUE en codigo de apartamentos para permitir mismo código en distintos condominios
    // En MySQL, verificar índices de apartamentos
    $indexes = $pdo->query("SHOW INDEX FROM `apartamentos` WHERE Key_name = 'codigo'")->fetchAll();
    if (!empty($indexes)) {
        try {
            $pdo->exec("ALTER TABLE `apartamentos` DROP INDEX `codigo`");
            $pdo->exec("ALTER TABLE `apartamentos` ADD UNIQUE KEY `uk_condominio_codigo` (`condominio_id`, `codigo`)");
            echo "✔ Índice de código de apartamento actualizado a (condominio_id, codigo) único.\n";
        } catch (Exception $e) {
            echo "ℹ Índice uk_condominio_codigo ya ajustado o nota: " . $e->getMessage() . "\n";
        }
    }

    echo "=== MIGRACIÓN MULTI-CONDOMINIO COMPLETADA CON ÉXITO ===\n";

} catch (Exception $e) {
    echo "❌ Error durante la migración: " . $e->getMessage() . "\n";
    exit(1);
}
