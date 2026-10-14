<?php
// api/configuracion.php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/bcv_scraper.php';

$pdo = getDBConnection();
$condominioId = getActiveCondominioId();
$action = $_GET['action'] ?? ($_POST['action'] ?? 'get');

if ($action === 'get') {
    $cId = (int)($_GET['condominio_id'] ?? $condominioId);
    $condo = getCondominio($cId);

    $stmt = $pdo->query("SELECT clave, valor FROM configuracion");
    $configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    if ($condo) {
        $configs['nombre_edificio'] = $condo['nombre'];
        $configs['rif'] = $condo['rif'] ?? '';
        $configs['direccion'] = $condo['direccion'] ?? '';
        $configs['tasa_dolar'] = $condo['tasa_dolar'] ?? $configs['tasa_dolar'] ?? '72.40';
        $configs['banco_pago_movil'] = $condo['banco_pago_movil'] ?? '';
        $configs['banco_transferencia'] = $condo['banco_transferencia'] ?? '';
        $configs['cuenta_zelle'] = $condo['cuenta_zelle'] ?? '';
        $configs['monto_cuota_ordinaria_default'] = $condo['monto_cuota_ordinaria_default'] ?? '35.00';
    }

    jsonResponse(['success' => true, 'config' => $configs, 'condominio' => $condo]);
}

if ($action === 'sync_bcv') {
    requireAdminAuth();
    $result = fetchAndSyncTasaBCV(true);
    if ($result['success']) {
        $nuevaTasa = (float)$result['tasa'];
        // Actualizar también en el condominio activo
        $stmtUp = $pdo->prepare("UPDATE condominios SET tasa_dolar = ? WHERE id = ?");
        $stmtUp->execute([$nuevaTasa, $condominioId]);

        jsonResponse([
            'success' => true,
            'message' => 'Tasa BCV sincronizada con éxito: Bs. ' . number_format($nuevaTasa, 2, ',', '.') . ' por USD (' . ($result['fecha_valor'] ?: 'Oficial') . ')',
            'data' => $result
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'message' => $result['message'],
            'data' => $result
        ], 500);
    }
}

if ($action === 'update') {
    requireAdminAuth();
    $cId = (int)($_POST['condominio_id'] ?? $condominioId);

    $campos = [
        'nombre_edificio',
        'rif',
        'direccion',
        'tasa_dolar',
        'banco_pago_movil',
        'banco_transferencia',
        'cuenta_zelle',
        'monto_cuota_ordinaria_default'
    ];

    foreach ($campos as $campo) {
        if (isset($_POST[$campo])) {
            $val = trim($_POST[$campo]);
            if ($campo === 'tasa_dolar') {
                $val = number_format((float)$val, 2, '.', '');
            }
            setConfig($campo, $val);
        }
    }

    // Actualizar también el registro en tabla condominios
    $stmtCondo = $pdo->prepare("
        UPDATE condominios SET 
            nombre = COALESCE(?, nombre),
            rif = COALESCE(?, rif),
            direccion = COALESCE(?, direccion),
            tasa_dolar = COALESCE(?, tasa_dolar),
            banco_pago_movil = COALESCE(?, banco_pago_movil),
            banco_transferencia = COALESCE(?, banco_transferencia),
            cuenta_zelle = COALESCE(?, cuenta_zelle),
            monto_cuota_ordinaria_default = COALESCE(?, monto_cuota_ordinaria_default)
        WHERE id = ?
    ");

    $stmtCondo->execute([
        $_POST['nombre_edificio'] ?? null,
        $_POST['rif'] ?? null,
        $_POST['direccion'] ?? null,
        isset($_POST['tasa_dolar']) ? (float)$_POST['tasa_dolar'] : null,
        $_POST['banco_pago_movil'] ?? null,
        $_POST['banco_transferencia'] ?? null,
        $_POST['cuenta_zelle'] ?? null,
        isset($_POST['monto_cuota_ordinaria_default']) ? (float)$_POST['monto_cuota_ordinaria_default'] : null,
        $cId
    ]);

    jsonResponse(['success' => true, 'message' => 'Configuraciones actualizadas correctamente.']);
}

jsonResponse(['success' => false, 'message' => 'Acción no válida.'], 400);
