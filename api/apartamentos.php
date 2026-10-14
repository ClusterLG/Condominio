<?php
// api/apartamentos.php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/generator.php';

$pdo = getDBConnection();
$condominioId = getActiveCondominioId();
$tasa = getTasaDolar($condominioId);
$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

// 1. Listado público para el selector del login filtrado por condominio
if ($action === 'list_public') {
    $cId = (int)($_GET['condominio_id'] ?? $condominioId);
    $stmt = $pdo->prepare("SELECT id, piso, codigo, propietario_nombre FROM apartamentos WHERE condominio_id = ? ORDER BY piso ASC, codigo ASC");
    $stmt->execute([$cId]);
    $aptos = $stmt->fetchAll();

    // Agrupar por pisos dinámicamente
    $pisos = [];
    foreach ($aptos as $a) {
        $pisoNum = (int)$a['piso'];
        $pisoNombre = $pisoNum === 0 ? 'Planta Baja (PB)' : 'Piso ' . $pisoNum;
        if (!isset($pisos[$pisoNum])) {
            $pisos[$pisoNum] = [
                'piso' => $pisoNum,
                'nombre' => $pisoNombre,
                'unidades' => []
            ];
        }
        $pisos[$pisoNum]['unidades'][] = $a;
    }

    jsonResponse(['success' => true, 'pisos' => array_values($pisos), 'total_unidades' => count($aptos)]);
}

// 2. Listado principal para la matriz de administración
if ($action === 'list') {
    $cId = (int)($_GET['condominio_id'] ?? $condominioId);

    $sql = "SELECT a.*, 
            COALESCE(SUM(CASE WHEN ca.estado = 'pendiente' THEN ca.monto_usd ELSE 0 END), 0) AS deuda_usd,
            COALESCE(SUM(CASE WHEN ca.estado = 'en_revision' THEN 1 ELSE 0 END), 0) AS pagos_en_revision,
            COALESCE(COUNT(ca.id), 0) AS total_cuotas,
            COALESCE(SUM(CASE WHEN ca.estado = 'pagado' THEN 1 ELSE 0 END), 0) AS cuotas_pagadas,
            (SELECT COUNT(*) FROM tickets t WHERE t.apartamento_id = a.id) AS total_tickets,
            (SELECT COUNT(*) FROM tickets t WHERE t.apartamento_id = a.id AND t.estado IN ('pendiente', 'en_revision')) AS tickets_abiertos
            FROM apartamentos a
            LEFT JOIN cuotas_apartamento ca ON a.id = ca.apartamento_id
            WHERE a.condominio_id = ?
            GROUP BY a.id
            ORDER BY a.piso ASC, a.codigo ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cId]);
    $aptos = $stmt->fetchAll();

    $pisos = [];
    foreach ($aptos as &$apto) {
        $deuda = (float)$apto['deuda_usd'];
        $revision = (int)$apto['pagos_en_revision'];

        if ($revision > 0) {
            $apto['estado_solvencia'] = 'en_revision';
            $apto['estado_label'] = 'En Revisión';
        } elseif ($deuda > 0) {
            $apto['estado_solvencia'] = 'deuda';
            $apto['estado_label'] = 'Deudor (' . formatUSD($deuda) . ')';
        } else {
            $apto['estado_solvencia'] = 'solvente';
            $apto['estado_label'] = 'Solvente';
        }

        $apto['deuda_bs'] = $deuda * $tasa;
        $apto['deuda_bs_formatted'] = formatBS($apto['deuda_bs']);
        $apto['deuda_usd_formatted'] = formatUSD($deuda);

        $pisoNum = (int)$apto['piso'];
        $pisoNombre = $pisoNum === 0 ? 'Planta Baja (PB)' : 'Piso ' . $pisoNum;
        
        if (!isset($pisos[$pisoNum])) {
            $pisos[$pisoNum] = [
                'piso' => $pisoNum,
                'nombre' => $pisoNombre,
                'unidades' => []
            ];
        }
        $pisos[$pisoNum]['unidades'][] = $apto;
    }

    $condoInfo = getCondominio($cId);

    jsonResponse([
        'success' => true,
        'condominio' => $condoInfo,
        'tasa_dolar' => $tasa,
        'fecha_valor_bcv' => getConfig('tasa_bcv_fecha_valor', 'Oficial'),
        'pisos' => array_values($pisos),
        'total_unidades' => count($aptos)
    ]);
}

// 3. Detalle completo de un apartamento
if ($action === 'detail') {
    $aptoId = (int)($_GET['id'] ?? 0);
    if ($aptoId <= 0) {
        jsonResponse(['success' => false, 'message' => 'ID inválido.'], 400);
    }

    $stmtApto = $pdo->prepare("SELECT * FROM apartamentos WHERE id = ?");
    $stmtApto->execute([$aptoId]);
    $apto = $stmtApto->fetch();

    if (!$apto) {
        jsonResponse(['success' => false, 'message' => 'Apartamento no encontrado.'], 404);
    }

    // Cuotas asignadas
    $stmtCuotas = $pdo->prepare("
        SELECT ca.*, c.titulo, c.descripcion, c.tipo, c.fecha_emision, c.fecha_vencimiento 
        FROM cuotas_apartamento ca
        JOIN cuotas c ON ca.cuota_id = c.id
        WHERE ca.apartamento_id = ?
        ORDER BY ca.id DESC
    ");
    $stmtCuotas->execute([$aptoId]);
    $cuotas = $stmtCuotas->fetchAll();

    // Pagos registrados
    $stmtPagos = $pdo->prepare("
        SELECT p.*, c.titulo AS cuota_titulo 
        FROM pagos p
        JOIN cuotas_apartamento ca ON p.cuota_apartamento_id = ca.id
        JOIN cuotas c ON ca.cuota_id = c.id
        WHERE p.apartamento_id = ?
        ORDER BY p.id DESC
    ");
    $stmtPagos->execute([$aptoId]);
    $pagos = $stmtPagos->fetchAll();

    foreach ($pagos as &$p) {
        $p['monto_pagado_formatted'] = $p['moneda'] === 'BS' ? formatBS($p['monto_pagado']) : formatUSD($p['monto_pagado']);
    }

    $ultimoPago = !empty($pagos) ? $pagos[0] : null;

    // Tickets
    $stmtTickets = $pdo->prepare("SELECT * FROM tickets WHERE apartamento_id = ? ORDER BY id DESC");
    $stmtTickets->execute([$aptoId]);
    $tickets = $stmtTickets->fetchAll();

    // Calcular deuda
    $deudaTotal = 0;
    foreach ($cuotas as &$c) {
        $c['monto_usd_formatted'] = formatUSD($c['monto_usd']);
        $c['monto_bs_formatted'] = formatBS($c['monto_usd'] * $tasa);
        if ($c['estado'] === 'pendiente') {
            $deudaTotal += (float)$c['monto_usd'];
        }
    }

    $apto['deuda_total_usd'] = $deudaTotal;
    $apto['deuda_total_usd_formatted'] = formatUSD($deudaTotal);
    $apto['deuda_total_bs_formatted'] = formatBS($deudaTotal * $tasa);

    jsonResponse([
        'success' => true,
        'apartamento' => $apto,
        'ultimo_pago' => $ultimoPago,
        'cuotas' => $cuotas,
        'pagos' => $pagos,
        'tickets' => $tickets
    ]);
}

// 4. Actualizar ficha del apartamento (Admin)
if ($action === 'update') {
    requireAdminAuth();
    $id = (int)($_POST['id'] ?? 0);
    $nombre = trim($_POST['propietario_nombre'] ?? '');
    $cedula = trim($_POST['cedula_identidad'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $telefonoSecundario = trim($_POST['telefono_secundario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $condicion = in_array($_POST['condicion_ocupacion'] ?? '', ['propietario', 'arrendatario', 'residente']) ? $_POST['condicion_ocupacion'] : 'propietario';
    $habitantes = (int)($_POST['habitantes_cant'] ?? 1);
    $vehiculos = trim($_POST['vehiculos_info'] ?? '');
    $mascotas = trim($_POST['mascotas_info'] ?? '');
    $contactoEmergencia = trim($_POST['contacto_emergencia'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');
    $alicuota = (float)($_POST['alicuota'] ?? 2.0);
    $newPassword = trim($_POST['new_password'] ?? '');

    if ($id <= 0) {
        jsonResponse(['success' => false, 'message' => 'ID inválido.'], 400);
    }

    if (!empty($newPassword)) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE apartamentos SET 
            propietario_nombre = ?, cedula_identidad = ?, telefono = ?, telefono_secundario = ?, email = ?, 
            condicion_ocupacion = ?, habitantes_cant = ?, vehiculos_info = ?, mascotas_info = ?, 
            contacto_emergencia = ?, observaciones = ?, alicuota = ?, password_hash = ? 
            WHERE id = ?");
        $stmt->execute([$nombre, $cedula, $telefono, $telefonoSecundario, $email, $condicion, $habitantes, $vehiculos, $mascotas, $contactoEmergencia, $observaciones, $alicuota, $hash, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE apartamentos SET 
            propietario_nombre = ?, cedula_identidad = ?, telefono = ?, telefono_secundario = ?, email = ?, 
            condicion_ocupacion = ?, habitantes_cant = ?, vehiculos_info = ?, mascotas_info = ?, 
            contacto_emergencia = ?, observaciones = ?, alicuota = ? 
            WHERE id = ?");
        $stmt->execute([$nombre, $cedula, $telefono, $telefonoSecundario, $email, $condicion, $habitantes, $vehiculos, $mascotas, $contactoEmergencia, $observaciones, $alicuota, $id]);
    }

    jsonResponse(['success' => true, 'message' => 'Ficha del apartamento y titular actualizada con éxito.']);
}

// 5. Agregar apartamento individual (Admin / Super-Admin)
if ($action === 'create_unit') {
    requireAdminAuth();
    $cId = (int)($_POST['condominio_id'] ?? $condominioId);
    $piso = (int)($_POST['piso'] ?? 0);
    $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
    $nombre = trim($_POST['propietario_nombre'] ?? 'Propietario ' . $codigo);
    $alicuota = (float)($_POST['alicuota'] ?? 2.0);
    $password = trim($_POST['password'] ?? '123456');

    if (empty($codigo)) {
        jsonResponse(['success' => false, 'message' => 'El código del apartamento es obligatorio (ej. 13A, PH-1, Local-1).'], 400);
    }

    // Verificar si ya existe en este condominio
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM apartamentos WHERE condominio_id = ? AND codigo = ?");
    $stmtCheck->execute([$cId, $codigo]);
    if ($stmtCheck->fetchColumn() > 0) {
        jsonResponse(['success' => false, 'message' => "El código de unidad '{$codigo}' ya existe en este condominio."], 400);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO apartamentos (condominio_id, piso, codigo, tipo, propietario_nombre, alicuota, password_hash) VALUES (?, ?, ?, 'apartamento', ?, ?, ?)");
    $stmt->execute([$cId, $piso, $codigo, $nombre, $alicuota, $hash]);

    jsonResponse(['success' => true, 'message' => "Apartamento '{$codigo}' agregado exitosamente al condominio."]);
}

// 6. Eliminar apartamento individual (Admin / Super-Admin)
if ($action === 'delete_unit') {
    requireAdminAuth();
    $id = (int)($_POST['id'] ?? 0);

    $stmtCheck = $pdo->prepare("SELECT * FROM apartamentos WHERE id = ?");
    $stmtCheck->execute([$id]);
    $apto = $stmtCheck->fetch();

    if (!$apto) {
        jsonResponse(['success' => false, 'message' => 'Apartamento no encontrado.'], 404);
    }

    $stmtDel = $pdo->prepare("DELETE FROM apartamentos WHERE id = ?");
    $stmtDel->execute([$id]);

    jsonResponse(['success' => true, 'message' => "Apartamento '{$apto['codigo']}' eliminado correctamente."]);
}

// 7. Regenerar estructura completa de apartamentos (Reset & Rebuild)
if ($action === 'regenerate_structure') {
    requireAdminAuth();
    $cId = (int)($_POST['condominio_id'] ?? $condominioId);

    $pisosCant = max(1, (int)($_POST['pisos_cant'] ?? 10));
    $aptosPorPiso = max(1, (int)($_POST['aptos_por_piso'] ?? 4));
    $tienePB = !empty($_POST['tiene_pb']);
    $aptosPBCant = $tienePB ? max(1, (int)($_POST['aptos_pb_cant'] ?? 2)) : 0;
    $nomenclatura = in_array($_POST['nomenclatura_tipo'] ?? '', ['letras', 'numeros']) ? $_POST['nomenclatura_tipo'] : 'letras';
    $tienePH = !empty($_POST['tiene_ph']);
    $aptosPHCant = $tienePH ? max(1, (int)($_POST['aptos_ph_cant'] ?? 2)) : 0;

    // Actualizar datos en condominios
    $stmtUpdateCondo = $pdo->prepare("UPDATE condominios SET pisos_cant = ?, tiene_pb = ?, aptos_pb_cant = ?, aptos_por_piso = ?, nomenclatura_tipo = ? WHERE id = ?");
    $stmtUpdateCondo->execute([$pisosCant, $tienePB ? 1 : 0, $aptosPBCant, $aptosPorPiso, $nomenclatura, $cId]);

    // Ejecutar generador
    $res = generarEstructuraCondominio($pdo, $cId, [
        'pisos_cant' => $pisosCant,
        'aptos_por_piso' => $aptosPorPiso,
        'tiene_pb' => $tienePB,
        'aptos_pb_cant' => $aptosPBCant,
        'nomenclatura_tipo' => $nomenclatura,
        'tiene_ph' => $tienePH,
        'aptos_ph_cant' => $aptosPHCant,
        'limpiar_existentes' => true
    ]);

    jsonResponse([
        'success' => true,
        'message' => "Estructura del edificio regenerada. Se crearon {$res['total_generados']} unidades residenciales.",
        'total' => $res['total_generados']
    ]);
}

// 8. Residente actualiza su propio perfil
if ($action === 'resident_update_profile') {
    requireResidentAuth();
    $id = (int)$_SESSION['apto_id'];

    $nombre = trim($_POST['propietario_nombre'] ?? '');
    $cedula = trim($_POST['cedula_identidad'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $telefonoSecundario = trim($_POST['telefono_secundario'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $condicion = in_array($_POST['condicion_ocupacion'] ?? '', ['propietario', 'arrendatario', 'residente']) ? $_POST['condicion_ocupacion'] : 'propietario';
    $habitantes = (int)($_POST['habitantes_cant'] ?? 1);
    $vehiculos = trim($_POST['vehiculos_info'] ?? '');
    $mascotas = trim($_POST['mascotas_info'] ?? '');
    $contactoEmergencia = trim($_POST['contacto_emergencia'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');

    if (empty($nombre)) {
        jsonResponse(['success' => false, 'message' => 'El nombre del adjudicatario/responsable es obligatorio.'], 400);
    }

    if (!empty($newPassword)) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE apartamentos SET 
            propietario_nombre = ?, cedula_identidad = ?, telefono = ?, telefono_secundario = ?, email = ?, 
            condicion_ocupacion = ?, habitantes_cant = ?, vehiculos_info = ?, mascotas_info = ?, 
            contacto_emergencia = ?, password_hash = ? 
            WHERE id = ?");
        $stmt->execute([$nombre, $cedula, $telefono, $telefonoSecundario, $email, $condicion, $habitantes, $vehiculos, $mascotas, $contactoEmergencia, $hash, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE apartamentos SET 
            propietario_nombre = ?, cedula_identidad = ?, telefono = ?, telefono_secundario = ?, email = ?, 
            condicion_ocupacion = ?, habitantes_cant = ?, vehiculos_info = ?, mascotas_info = ?, 
            contacto_emergencia = ? 
            WHERE id = ?");
        $stmt->execute([$nombre, $cedula, $telefono, $telefonoSecundario, $email, $condicion, $habitantes, $vehiculos, $mascotas, $contactoEmergencia, $id]);
    }

    $_SESSION['apto_propietario'] = $nombre;

    jsonResponse(['success' => true, 'message' => 'Su perfil de adjudicatario ha sido actualizado correctamente.']);
}

jsonResponse(['success' => false, 'message' => 'Acción no reconocida.'], 400);
