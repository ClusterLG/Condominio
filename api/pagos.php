<?php
// api/pagos.php
require_once __DIR__ . '/../config/helpers.php';

$pdo = getDBConnection();
$tasa = getTasaDolar();
$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

if ($action === 'list') {
    requireAdminAuth();
    $filtroEstado = $_GET['estado'] ?? 'todos';

    $cId = (int)($_GET['condominio_id'] ?? getActiveCondominioId());
    $where = ["a.condominio_id = ?"];
    $params = [$cId];
    if (in_array($filtroEstado, ['pendiente', 'aprobado', 'rechazado'])) {
        $where[] = "p.estado = ?";
        $params[] = $filtroEstado;
    }

    $whereSql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

    $sql = "SELECT p.*, a.codigo AS apto_codigo, a.piso AS apto_piso, a.propietario_nombre,
                   c.titulo AS cuota_titulo, c.tipo AS cuota_tipo, ca.monto_usd AS cuota_monto_usd
            FROM pagos p
            JOIN apartamentos a ON p.apartamento_id = a.id
            JOIN cuotas_apartamento ca ON p.cuota_apartamento_id = ca.id
            JOIN cuotas c ON ca.cuota_id = c.id
            $whereSql
            ORDER BY p.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pagos = $stmt->fetchAll();

    foreach ($pagos as &$p) {
        $p['monto_pagado_formatted'] = $p['moneda'] === 'BS' ? formatBS($p['monto_pagado']) : formatUSD($p['monto_pagado']);
        $p['cuota_monto_usd_formatted'] = formatUSD($p['cuota_monto_usd']);
    }

    jsonResponse(['success' => true, 'pagos' => $pagos]);
}

if ($action === 'resident_pending_cuotas') {
    requireResidentAuth();
    $aptoId = (int)$_SESSION['apto_id'];

    $sql = "SELECT ca.*, c.titulo, c.descripcion, c.tipo, c.fecha_emision, c.fecha_vencimiento
            FROM cuotas_apartamento ca
            JOIN cuotas c ON ca.cuota_id = c.id
            WHERE ca.apartamento_id = ? AND ca.estado IN ('pendiente', 'en_revision')
            ORDER BY c.fecha_vencimiento ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$aptoId]);
    $cuotas = $stmt->fetchAll();

    foreach ($cuotas as &$c) {
        $c['monto_usd_formatted'] = formatUSD($c['monto_usd']);
        $c['monto_bs_formatted'] = formatBS($c['monto_usd'] * $tasa);
    }

    jsonResponse([
        'success' => true,
        'tasa_dolar' => $tasa,
        'cuotas' => $cuotas
    ]);
}

if ($action === 'resident_history') {
    requireResidentAuth();
    $aptoId = (int)$_SESSION['apto_id'];

    $sql = "SELECT p.*, c.titulo AS cuota_titulo, c.tipo AS cuota_tipo, ca.monto_usd AS cuota_monto_usd
            FROM pagos p
            JOIN cuotas_apartamento ca ON p.cuota_apartamento_id = ca.id
            JOIN cuotas c ON ca.cuota_id = c.id
            WHERE p.apartamento_id = ?
            ORDER BY p.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$aptoId]);
    $pagos = $stmt->fetchAll();

    foreach ($pagos as &$p) {
        $p['monto_pagado_formatted'] = $p['moneda'] === 'BS' ? formatBS($p['monto_pagado']) : formatUSD($p['monto_pagado']);
    }

    jsonResponse(['success' => true, 'pagos' => $pagos]);
}

if ($action === 'report') {
    requireResidentAuth();
    $aptoId = (int)$_SESSION['apto_id'];

    $cuotaAptoId = (int)($_POST['cuota_apartamento_id'] ?? 0);
    $metodoPago = $_POST['metodo_pago'] ?? '';
    $referencia = trim($_POST['referencia'] ?? '');
    $montoPagado = (float)($_POST['monto_pagado'] ?? 0);
    $moneda = in_array($_POST['moneda'] ?? '', ['USD', 'BS']) ? $_POST['moneda'] : 'USD';
    $fechaPago = $_POST['fecha_pago'] ?? date('Y-m-d');
    $notas = trim($_POST['notas'] ?? '');

    if ($cuotaAptoId <= 0 || empty($referencia) || $montoPagado <= 0) {
        jsonResponse(['success' => false, 'message' => 'Seleccione la cuota, ingrese el número de referencia y el monto pagado.'], 400);
    }

    // Verificar que la cuota pertenezca al apartamento
    $stmtCheck = $pdo->prepare("SELECT * FROM cuotas_apartamento WHERE id = ? AND apartamento_id = ?");
    $stmtCheck->execute([$cuotaAptoId, $aptoId]);
    $cuotaApto = $stmtCheck->fetch();

    if (!$cuotaApto) {
        jsonResponse(['success' => false, 'message' => 'Cuota no encontrada para este apartamento.'], 404);
    }

    // Manejo de archivo comprobante
    $comprobantePath = null;
    if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['comprobante']['tmp_name'];
        $fileName = $_FILES['comprobante']['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExts = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];
        if (in_array($ext, $allowedExts)) {
            $nuevoNombre = 'pago_' . $aptoId . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $destino = __DIR__ . '/../uploads/comprobantes/' . $nuevoNombre;
            if (move_uploaded_file($fileTmp, $destino)) {
                $comprobantePath = 'uploads/comprobantes/' . $nuevoNombre;
            }
        }
    }

    $pdo->beginTransaction();
    try {
        $stmtPago = $pdo->prepare("INSERT INTO pagos (apartamento_id, cuota_apartamento_id, metodo_pago, referencia, monto_pagado, moneda, tasa_cambio, fecha_pago, comprobante_path, notas, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')");
        $stmtPago->execute([$aptoId, $cuotaAptoId, $metodoPago, $referencia, $montoPagado, $moneda, $tasa, $fechaPago, $comprobantePath, $notas]);

        // Cambiar estado de cuota_apartamento a 'en_revision'
        $stmtUpd = $pdo->prepare("UPDATE cuotas_apartamento SET estado = 'en_revision' WHERE id = ?");
        $stmtUpd->execute([$cuotaAptoId]);

        $pdo->commit();
        jsonResponse(['success' => true, 'message' => 'Pago reportado con éxito. El administrador revisará la información a la brevedad.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => 'Error al registrar el pago: ' . $e->getMessage()], 500);
    }
}

if ($action === 'approve') {
    requireAdminAuth();
    $pagoId = (int)($_POST['pago_id'] ?? 0);

    if ($pagoId <= 0) {
        jsonResponse(['success' => false, 'message' => 'ID de pago inválido.'], 400);
    }

    $stmtPago = $pdo->prepare("SELECT * FROM pagos WHERE id = ?");
    $stmtPago->execute([$pagoId]);
    $pago = $stmtPago->fetch();

    if (!$pago) {
        jsonResponse(['success' => false, 'message' => 'Pago no encontrado.'], 404);
    }

    $pdo->beginTransaction();
    try {
        $stmtApprove = $pdo->prepare("UPDATE pagos SET estado = 'aprobado', motivo_rechazo = NULL, fecha_verificacion = NOW() WHERE id = ?");
        $stmtApprove->execute([$pagoId]);

        $stmtCuota = $pdo->prepare("UPDATE cuotas_apartamento SET estado = 'pagado' WHERE id = ?");
        $stmtCuota->execute([$pago['cuota_apartamento_id']]);

        $pdo->commit();
        jsonResponse(['success' => true, 'message' => 'Pago verificado y aprobado. Cuota marcada como PAGADA.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
}

if ($action === 'reject') {
    requireAdminAuth();
    $pagoId = (int)($_POST['pago_id'] ?? 0);
    $motivo = trim($_POST['motivo_rechazo'] ?? 'Comprobante o referencia no coincide con la cuenta.');

    if ($pagoId <= 0) {
        jsonResponse(['success' => false, 'message' => 'ID de pago inválido.'], 400);
    }

    $stmtPago = $pdo->prepare("SELECT * FROM pagos WHERE id = ?");
    $stmtPago->execute([$pagoId]);
    $pago = $stmtPago->fetch();

    if (!$pago) {
        jsonResponse(['success' => false, 'message' => 'Pago no encontrado.'], 404);
    }

    $pdo->beginTransaction();
    try {
        $stmtReject = $pdo->prepare("UPDATE pagos SET estado = 'rechazado', motivo_rechazo = ?, fecha_verificacion = NOW() WHERE id = ?");
        $stmtReject->execute([$motivo, $pagoId]);

        // Volver la cuota a pendiente
        $stmtCuota = $pdo->prepare("UPDATE cuotas_apartamento SET estado = 'pendiente' WHERE id = ?");
        $stmtCuota->execute([$pago['cuota_apartamento_id']]);

        $pdo->commit();
        jsonResponse(['success' => true, 'message' => 'Pago rechazado. El residente podrá volver a reportar su cuota.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
}

jsonResponse(['success' => false, 'message' => 'Acción no válida.'], 400);
