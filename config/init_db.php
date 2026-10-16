<?php
// config/init_db.php
// Inicializador de base de datos Multi-Condominio para despliegue en cualquier servidor

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/generator.php';

function initializeDatabase() {
    try {
        // Conexión inicial sin DB para crearla si no existe
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `" . DB_NAME . "`");

        // 1. Tabla de Condominios / Proyectos Inmobiliarios
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
            `nomenclatura_tipo` VARCHAR(20) DEFAULT 'letras',
            `tasa_dolar` DECIMAL(10,2) DEFAULT 72.40,
            `banco_pago_movil` TEXT NULL,
            `banco_transferencia` TEXT NULL,
            `cuenta_zelle` TEXT NULL,
            `monto_cuota_ordinaria_default` DECIMAL(10,2) DEFAULT 35.00,
            `activo` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 2. Tabla de Apartamentos / Unidades
        $pdo->exec("CREATE TABLE IF NOT EXISTS `apartamentos` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `condominio_id` INT NOT NULL DEFAULT 1,
            `piso` INT NOT NULL COMMENT '0 para Planta Baja, 1-N para pisos',
            `codigo` VARCHAR(20) NOT NULL,
            `tipo` ENUM('apartamento') NOT NULL DEFAULT 'apartamento',
            `propietario_nombre` VARCHAR(120) NULL,
            `cedula_identidad` VARCHAR(30) NULL,
            `telefono` VARCHAR(50) NULL,
            `telefono_secundario` VARCHAR(50) NULL,
            `email` VARCHAR(120) NULL,
            `condicion_ocupacion` ENUM('propietario', 'arrendatario', 'residente') DEFAULT 'propietario',
            `habitantes_cant` INT DEFAULT 1,
            `vehiculos_info` VARCHAR(255) NULL,
            `mascotas_info` VARCHAR(255) NULL,
            `contacto_emergencia` VARCHAR(150) NULL,
            `observaciones` TEXT NULL,
            `password_hash` VARCHAR(255) NOT NULL,
            `alicuota` DECIMAL(5,2) DEFAULT 2.00,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`condominio_id`) REFERENCES `condominios`(`id`) ON DELETE CASCADE,
            UNIQUE KEY `uk_condo_codigo` (`condominio_id`, `codigo`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 3. Tabla de Usuarios (Super Admin y Administradores de Condominio)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `usuarios` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `condominio_id` INT NULL,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `rol` ENUM('super_admin', 'admin') NOT NULL DEFAULT 'admin',
            `nombre` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NULL,
            `cedula` VARCHAR(30) NULL,
            `telefono` VARCHAR(50) NULL,
            `telefono_secundario` VARCHAR(50) NULL,
            `cargo` VARCHAR(100) NULL DEFAULT 'Administrador de Condominio',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`condominio_id`) REFERENCES `condominios`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 4. Tabla de Cuotas
        $pdo->exec("CREATE TABLE IF NOT EXISTS `cuotas` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `condominio_id` INT NOT NULL DEFAULT 1,
            `titulo` VARCHAR(150) NOT NULL,
            `descripcion` TEXT NULL,
            `tipo` ENUM('ordinaria', 'extraordinaria') NOT NULL DEFAULT 'ordinaria',
            `monto_usd` DECIMAL(10,2) NOT NULL,
            `fecha_emision` DATE NOT NULL,
            `fecha_vencimiento` DATE NOT NULL,
            `estado` ENUM('activa', 'cerrada') DEFAULT 'activa',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`condominio_id`) REFERENCES `condominios`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 5. Detalle de Cuota por Apartamento
        $pdo->exec("CREATE TABLE IF NOT EXISTS `cuotas_apartamento` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `cuota_id` INT NOT NULL,
            `apartamento_id` INT NOT NULL,
            `monto_usd` DECIMAL(10,2) NOT NULL,
            `estado` ENUM('pendiente', 'en_revision', 'pagado') DEFAULT 'pendiente',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`cuota_id`) REFERENCES `cuotas`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`apartamento_id`) REFERENCES `apartamentos`(`id`) ON DELETE CASCADE,
            UNIQUE KEY `uk_cuota_apto` (`cuota_id`, `apartamento_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 6. Tabla de Pagos Reportados
        $pdo->exec("CREATE TABLE IF NOT EXISTS `pagos` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `apartamento_id` INT NOT NULL,
            `cuota_apartamento_id` INT NOT NULL,
            `metodo_pago` ENUM('pago_movil', 'transferencia_bs', 'zelle', 'efectivo_usd', 'transferencia_usd') NOT NULL,
            `referencia` VARCHAR(80) NOT NULL,
            `monto_pagado` DECIMAL(12,2) NOT NULL,
            `moneda` ENUM('USD', 'BS') NOT NULL DEFAULT 'USD',
            `tasa_cambio` DECIMAL(10,2) DEFAULT 1.00,
            `fecha_pago` DATE NOT NULL,
            `comprobante_path` VARCHAR(255) NULL,
            `notas` TEXT NULL,
            `estado` ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
            `motivo_rechazo` TEXT NULL,
            `fecha_verificacion` DATETIME NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`apartamento_id`) REFERENCES `apartamentos`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`cuota_apartamento_id`) REFERENCES `cuotas_apartamento`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 7. Tabla de Tickets / Quejas y Solicitudes
        $pdo->exec("CREATE TABLE IF NOT EXISTS `tickets` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `apartamento_id` INT NOT NULL,
            `tipo` ENUM('queja', 'solicitud', 'novedad_averia') NOT NULL,
            `titulo` VARCHAR(150) NOT NULL,
            `descripcion` TEXT NOT NULL,
            `foto_path` VARCHAR(255) NULL,
            `prioridad` ENUM('baja', 'media', 'alta', 'urgente') DEFAULT 'media',
            `estado` ENUM('pendiente', 'en_revision', 'resuelto', 'rechazado') DEFAULT 'pendiente',
            `respuesta_admin` TEXT NULL,
            `fecha_respuesta` DATETIME NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`apartamento_id`) REFERENCES `apartamentos`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 8. Cartelera Digital / Comunicados
        $pdo->exec("CREATE TABLE IF NOT EXISTS `comunicados` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `condominio_id` INT NOT NULL DEFAULT 1,
            `titulo` VARCHAR(150) NOT NULL,
            `contenido` TEXT NOT NULL,
            `prioridad` ENUM('informativo', 'importante', 'urgente') DEFAULT 'informativo',
            `publicado_por` VARCHAR(100) DEFAULT 'Junta Directiva',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`condominio_id`) REFERENCES `condominios`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // 9. Configuración General del Sistema
        $pdo->exec("CREATE TABLE IF NOT EXISTS `configuracion` (
            `clave` VARCHAR(50) PRIMARY KEY,
            `valor` TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // INSERTAR DATOS SEMILLA SI ESTÁ VACÍO
        // 1. Condominio Base
        $checkCondos = $pdo->query("SELECT COUNT(*) FROM `condominios`")->fetchColumn();
        if ($checkCondos == 0) {
            $pdo->exec("INSERT INTO `condominios` 
                (`id`, `slug`, `nombre`, `rif`, `direccion`, `pisos_cant`, `tiene_pb`, `aptos_pb_cant`, `aptos_por_piso`, `nomenclatura_tipo`, `tasa_dolar`, `banco_pago_movil`, `banco_transferencia`, `cuenta_zelle`, `monto_cuota_ordinaria_default`) 
                VALUES (1, 'los-samanes', 'Residencias Los Samanes', 'J-12345678-0', 'Av. Principal Los Samanes, Caracas', 12, 1, 2, 4, 'letras', 72.40, 
                'Banco Banesco | 0134 | V-12345678 | 0414-1234567', 
                'Banesco Cta Corriente: 0134-0000-00-0000000000 | RIF: J-12345678-0', 
                'condominio.samanes@gmail.com (Titular: Condominio)', 35.00)");
        }

        // 2. Superusuario Global (clusterlg / glretsulc)
        $checkSuper = $pdo->query("SELECT COUNT(*) FROM `usuarios` WHERE `username` = 'clusterlg'")->fetchColumn();
        if ($checkSuper == 0) {
            $superPass = password_hash('glretsulc', PASSWORD_DEFAULT);
            $stmtSuper = $pdo->prepare("INSERT INTO `usuarios` (`username`, `password_hash`, `rol`, `condominio_id`, `nombre`, `email`) VALUES ('clusterlg', ?, 'super_admin', NULL, 'Superusuario Global', 'superuser@clusterlg.com')");
            $stmtSuper->execute([$superPass]);
        }

        // 2.1 Administrador Local por defecto para Condominio 1
        $checkAdmin = $pdo->query("SELECT COUNT(*) FROM `usuarios` WHERE `username` = 'admin'")->fetchColumn();
        if ($checkAdmin == 0) {
            $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
            $stmtAdmin = $pdo->prepare("INSERT INTO `usuarios` (`username`, `password_hash`, `rol`, `condominio_id`, `nombre`, `email`) VALUES ('admin', ?, 'admin', 1, 'Administrador Los Samanes', 'admin@samanes.com')");
            $stmtAdmin->execute([$adminPass]);
        }

        // 3. Apartamentos del condominio base
        $checkAptos = $pdo->query("SELECT COUNT(*) FROM `apartamentos` WHERE `condominio_id` = 1")->fetchColumn();
        if ($checkAptos == 0) {
            generarEstructuraCondominio($pdo, 1, [
                'pisos_cant' => 12,
                'aptos_por_piso' => 4,
                'tiene_pb' => true,
                'aptos_pb_cant' => 2,
                'nomenclatura_tipo' => 'letras',
                'clave_default' => '123456'
            ]);
        }

        return true;
    } catch (Exception $e) {
        die("Error en inicialización de Base de Datos: " . $e->getMessage());
    }
}
