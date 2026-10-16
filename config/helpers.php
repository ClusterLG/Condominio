<?php
// config/helpers.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/bcv_scraper.php';

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function sanitizeInput($data) {
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Obtiene el ID del condominio activo en el contexto actual
 */
function getActiveCondominioId(): int {
    $pdo = getDBConnection();

    // 1. Si se envía explícitamente y el usuario tiene permisos de Super-Admin
    if (isset($_REQUEST['condominio_id']) && isSuperAdmin()) {
        $cid = (int)$_REQUEST['condominio_id'];
        if ($cid > 0) {
            $_SESSION['condominio_id'] = $cid;
            return $cid;
        }
    }

    // 2. Si hay sesión de residente activa
    if (isset($_SESSION['apto_condominio_id']) && (int)$_SESSION['apto_condominio_id'] > 0) {
        return (int)$_SESSION['apto_condominio_id'];
    }

    // 3. Si hay sesión de administrador activa
    if (isset($_SESSION['condominio_id']) && (int)$_SESSION['condominio_id'] > 0) {
        return (int)$_SESSION['condominio_id'];
    }

    // 4. Fallback al primer condominio activo en la BD
    try {
        $firstId = $pdo->query("SELECT id FROM condominios WHERE activo = 1 ORDER BY id ASC LIMIT 1")->fetchColumn();
        if ($firstId) {
            $_SESSION['condominio_id'] = (int)$firstId;
            return (int)$firstId;
        }
    } catch (Exception $e) {}

    return 1;
}

/**
 * Obtiene los datos del condominio activo o por ID
 */
function getCondominio($id = null) {
    $pdo = getDBConnection();
    $id = $id ? (int)$id : getActiveCondominioId();
    $stmt = $pdo->prepare("SELECT * FROM condominios WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $condo = $stmt->fetch();
    return $condo ?: null;
}

function isSuperAdmin(): bool {
    return isset($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'super_admin';
}

function isAdmin(): bool {
    return isset($_SESSION['user_id']) && in_array($_SESSION['user_role'] ?? '', ['admin', 'super_admin']);
}

function isResident(): bool {
    return isset($_SESSION['apto_id']) && (int)$_SESSION['apto_id'] > 0;
}

function getConfig($key, $default = '') {
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("SELECT valor FROM configuracion WHERE clave = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    } catch (Exception $e) {
        return $default;
    }
}

function setConfig($key, $value) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO configuracion (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
    return $stmt->execute([$key, $value]);
}

/**
 * Obtiene un ajuste del condominio actual (nombre, cuentas, cuotas), con fallback a configuracion global
 */
function getCondoSetting($key, $default = '', $condominioId = null) {
    $condo = getCondominio($condominioId);
    if ($condo && isset($condo[$key]) && $condo[$key] !== null && $condo[$key] !== '') {
        return $condo[$key];
    }
    return getConfig($key, $default);
}

function getTasaDolar($condominioId = null) {
    $condo = getCondominio($condominioId);
    if ($condo && !empty($condo['tasa_dolar']) && (float)$condo['tasa_dolar'] > 0) {
        return (float)$condo['tasa_dolar'];
    }
    
    $tasa = (float)getConfig('tasa_dolar', 0);
    if ($tasa <= 0) {
        $sync = fetchAndSyncTasaBCV(true);
        $tasa = (float)($sync['tasa'] ?? 876.80);
    }
    return $tasa > 0 ? $tasa : 876.80;
}

function formatUSD($amount) {
    return '$' . number_format((float)$amount, 2, '.', ',');
}

function formatBS($amount) {
    return 'Bs. ' . number_format((float)$amount, 2, ',', '.');
}

function formatUSDToBS($amountUSD, $tasa = null) {
    if ($tasa === null) {
        $tasa = getTasaDolar();
    }
    return (float)$amountUSD * $tasa;
}

function requireAdminAuth() {
    if (!isAdmin()) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            jsonResponse(['success' => false, 'message' => 'Acceso no autorizado. Debe iniciar sesión como Administrador.'], 401);
        }
        header("Location: index.php?error=no_auth");
        exit;
    }
}

function requireSuperAdminAuth() {
    if (!isSuperAdmin()) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            jsonResponse(['success' => false, 'message' => 'Acceso denegado. Se requieren permisos de Super-Administrador.'], 403);
        }
        header("Location: index.php?error=forbidden");
        exit;
    }
}

function requireResidentAuth() {
    if (!isResident()) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            jsonResponse(['success' => false, 'message' => 'Acceso no autorizado. Inicie sesión con su apartamento.'], 401);
        }
        header("Location: index.php?error=no_auth");
        exit;
    }
}

/**
 * Sincroniza un nuevo condominio, sus apartamentos y exactamente N aptos + 1 admin con claves por defecto en Supabase
 */
function syncNewCondominioAndUsersToSupabase(array $condo, array $units, array $adminInfo = []): array {
    $supabaseUrl = 'https://xlyfbwsfojzmcgjgcsrw.supabase.co/rest/v1/';
    $supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InhseWZid3Nmb2p6bWNnamdjc3J3Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3OTIwMTAzNjYsImV4cCI6MjEwNzU4NjM2Nn0.reJ5Iv8rjpKXfbsGChqX4-roeEGXhr2zOCOTt5RwTUY';

    $request = function($endpoint, $data = null) use ($supabaseUrl, $supabaseKey) {
        $ch = curl_init($supabaseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $headers = [
            'apikey: ' . $supabaseKey,
            'Authorization: Bearer ' . $supabaseKey,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return ['code' => $code, 'body' => json_decode($res, true)];
    };

    try {
        $condoId = (int)$condo['id'];
        $slugClean = preg_replace('/[^a-z0-9]/', '', strtolower($condo['slug']));
        $domain = ($condoId === 1) ? 'resdlossamanes.com' : (($condoId === 2) ? 'resdchichisuggar.com' : "resd{$slugClean}.com");

        // 1. Insertar Condominio en Supabase
        $condoPayload = [
            'id' => $condoId,
            'slug' => $condo['slug'],
            'nombre' => $condo['nombre'],
            'rif' => $condo['rif'] ?? null,
            'direccion' => $condo['direccion'] ?? null,
            'pisos_cant' => (int)($condo['pisos_cant'] ?? 10),
            'tiene_pb' => !empty($condo['tiene_pb']) ? 1 : 0,
            'aptos_pb_cant' => (int)($condo['aptos_pb_cant'] ?? 0),
            'aptos_por_piso' => (int)($condo['aptos_por_piso'] ?? 4),
            'tasa_dolar' => (float)($condo['tasa_dolar'] ?? 877.90),
            'banco_pago_movil' => $condo['banco_pago_movil'] ?? null,
            'banco_transferencia' => $condo['banco_transferencia'] ?? null,
            'cuenta_zelle' => $condo['cuenta_zelle'] ?? null
        ];
        $request('condominios', [$condoPayload]);

        // 2. Insertar Apartamentos en Supabase
        $aptoRows = [];
        foreach ($units as $u) {
            $aptoRows[] = [
                'condominio_id' => $condoId,
                'piso' => (int)$u['piso'],
                'codigo' => $u['codigo'],
                'alicuota' => (float)($u['alicuota'] ?? 2.0),
                'propietario' => $u['propietario_nombre'] ?? null,
                'cedula' => $u['cedula_identidad'] ?? null,
                'telefono' => $u['telefono'] ?? null,
                'email' => $u['email'] ?? null,
                'vehiculo' => null
            ];
        }
        if (!empty($aptoRows)) {
            foreach (array_chunk($aptoRows, 100) as $chunk) {
                $request('apartamentos', $chunk);
            }
        }

        // 3. Generar Usuarios: N apartamentos + 1 admin con claves por defecto
        $userRows = [];

        // 3.1 Usuario Admin (Clave por defecto: admin123)
        $userRows[] = [
            'condominio_id' => $condoId,
            'username' => "admin@{$domain}",
            'email' => "admin@{$domain}",
            'password' => 'admin123',
            'nombre_completo' => $adminInfo['nombre'] ?? "Administrador {$condo['nombre']}",
            'cedula' => $condo['rif'] ?? null,
            'telefono' => '0414-1234567',
            'rol' => 'admin',
            'apto_codigo' => null,
            'activo' => true
        ];

        // 3.2 Usuarios Residentes (Uno por cada apartamento generado, Clave por defecto: 123456)
        foreach ($units as $u) {
            $codeClean = preg_replace('/[^a-z0-9]/', '', strtolower($u['codigo']));
            $resEmail = "apto{$codeClean}@{$domain}";
            $userRows[] = [
                'condominio_id' => $condoId,
                'username' => $resEmail,
                'email' => $resEmail,
                'password' => '123456',
                'nombre_completo' => !empty($u['propietario_nombre']) ? $u['propietario_nombre'] : "Copropietario Apto {$u['codigo']}",
                'cedula' => $u['cedula_identidad'] ?? null,
                'telefono' => $u['telefono'] ?? null,
                'rol' => 'residente',
                'apto_codigo' => $u['codigo'],
                'activo' => true
            ];
        }

        if (!empty($userRows)) {
            $lastCode = 201;
            foreach (array_chunk($userRows, 100) as $chunk) {
                $uRes = $request('usuarios', $chunk);
                $lastCode = $uRes['code'];
            }
            return [
                'success' => true, 
                'total_usuarios' => count($userRows), 
                'total_apartamentos' => count($aptoRows),
                'supabase_code' => $lastCode
            ];
        }

        return ['success' => true, 'total_usuarios' => 0];
    } catch (Exception $e) {
        error_log('Error sincronizando con Supabase: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

