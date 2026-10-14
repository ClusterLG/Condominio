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
