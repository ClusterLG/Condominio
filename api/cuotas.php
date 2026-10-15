<?php
// api/cuotas.php
require_once __DIR__ . '/../config/helpers.php';

$pdo = getDBConnection();
$condominioId = getActiveCondominioId();
$tasa = getTasaDolar($condominioId);
$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

if ($action === 'list') {
    $cId = (int)($_GET['condominio_id'] ?? $condominioId);

    $sql = "SELECT c.*, 
            COUNT(ca.id) AS total_asignados,
            SUM(CASE WHEN ca.estado = 'pagado' THEN 1 ELSE 0 END) AS total_pagados,
            SUM(CASE WHEN ca.estado = 'en_revision' THEN 1 ELSE 0 END) AS total_en_revision,
            SUM(CASE WHEN ca.estado = 'pendiente' THEN 1 ELSE 0 END) AS total_pendientes,
            COALESCE(SUM(ca.monto_usd), 0) AS total_esperado_usd,
            COALESCE(SUM(CASE WHEN ca.estado = 'pagado' THEN ca.monto_usd ELSE 0 END), 0) AS total_recaudado_usd
            FROM cuotas c
            LEFT JOIN cuotas_apartamento ca ON c.id = ca.cuota_id
            WHERE c.condominio_id = ?
            GROUP BY c.id
            ORDER BY c.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cId]);
    $cuotas = $stmt->fetchAll();

    foreach ($cuotas as &$c) {
        $esperado = (float)$c['total_esperado_usd'];
        $recaudado = (float)$c['total_recaudado_usd'];
        $c['porcentaje_recaudado'] = $esperado > 0 ? round(($recaudado / $esperado) * 100, 1) : 0;
        $c['monto_usd_formatted'] = formatUSD($c['monto_usd']);
        $c['monto_bs_formatted'] = formatBS($c['monto_usd'] * $tasa);
        $c['total_esperado_usd_formatted'] = formatUSD($esperado);
        $c['total_recaudado_usd_formatted'] = formatUSD($recaudado);
    }

    jsonResponse(['success' => true, 'cuotas' => $cuotas, 'tasa_dolar' => $tasa]);
}

if ($action === 'create') {
    requireAdminAuth();
    $cId = (int)($_POST['condominio_id'] ?? $condominioId);

    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $tipo = in_array($_POST['tipo'] ?? '', ['ordinaria', 'extraordinaria', 'individual']) ? $_POST['tipo'] : 'ordinaria';
    $meses_duracion = max(1, (int)($_POST['meses_duracion'] ?? 1));
    $monto_usd = (float)($_POST['monto_usd'] ?? 0);
    $fecha_emision = $_POST['fecha_emision'] ?? date('Y-m-d');
    $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? date('Y-m-d', strtotime('+15 days'));
    $destino = $_POST['destino'] ?? 'todos'; // 'todos', 'personalizado' o 'individual'

    if (empty($titulo) || $monto_usd <= 0) {
        jsonResponse(['success' => false, 'message' => 'Por favor ingrese el título y un monto válido en USD.'], 400);
    }

    if ($tipo === 'extraordinaria') {
        $descripcion = trim($descripcion . " (Duración: {$meses_duracion} meses)");
    } else if ($tipo === 'ordinaria' && empty($descripcion)) {
        $descripcion = "Cuota ordinaria mensual recurrente";
    }

    $pdo->beginTransaction();
    try {
        $stmtCuota = $pdo->prepare("INSERT INTO cuotas (condominio_id, titulo, descripcion, tipo, monto_usd, fecha_emision, fecha_vencimiento, estado) VALUES (?, ?, ?, ?, ?, ?, ?, 'activa')");
        $stmtCuota->execute([$cId, $titulo, $descripcion, $tipo, $monto_usd, $fecha_emision, $fecha_vencimiento]);
        $cuotaId = $pdo->lastInsertId();

        // Obtener apartamentos residenciales o seleccionados para este condominio
        if ($tipo === 'individual') {
            $aptoId = (int)($_POST['apartamento_individual_id'] ?? 0);
            if ($aptoId <= 0) {
                throw new Exception("Debe seleccionar un apartamento válido para la cuota individual.");
            }
            $aptos = [$aptoId];
        } else if ($destino === 'todos') {
            $stmtAptos = $pdo->prepare("SELECT id FROM apartamentos WHERE condominio_id = ? AND tipo = 'apartamento'");
            $stmtAptos->execute([$cId]);
            $aptos = $stmtAptos->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $aptosRaw = $_POST['apartamentos_ids'] ?? [];
            $aptos = is_array($aptosRaw) ? array_map('intval', $aptosRaw) : [];
        }

        if (empty($aptos)) {
            throw new Exception("No hay apartamentos registrados en este condominio para asignar la cuota.");
        }

        $stmtAsignar = $pdo->prepare("INSERT INTO cuotas_apartamento (cuota_id, apartamento_id, monto_usd, estado) VALUES (?, ?, ?, 'pendiente')");
        foreach ($aptos as $aptoId) {
            $stmtAsignar->execute([$cuotaId, $aptoId, $monto_usd]);
        }

        $pdo->commit();
        jsonResponse(['success' => true, 'message' => "Cuota '{$titulo}' creada exitosamente y asignada a " . count($aptos) . " apartamentos."]);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => 'Error al crear la cuota: ' . $e->getMessage()], 500);
    }
}

if ($action === 'delete') {
    requireAdminAuth();
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(['success' => false, 'message' => 'ID inválido.'], 400);
    }

    $stmt = $pdo->prepare("DELETE FROM cuotas WHERE id = ?");
    $stmt->execute([$id]);

    jsonResponse(['success' => true, 'message' => 'Cuota eliminada correctamente.']);
}

jsonResponse(['success' => false, 'message' => 'Acción no válida.'], 400);
