<?php
// api/condominios.php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/generator.php';

$pdo = getDBConnection();
$action = $_GET['action'] ?? ($_POST['action'] ?? 'list_public');

// 1. Listado público para el selector de login / bienvenida
if ($action === 'list_public') {
    $stmt = $pdo->query("
        SELECT c.id, c.slug, c.nombre, c.rif, c.direccion, c.pisos_cant, c.tiene_pb, c.aptos_por_piso,
               COUNT(a.id) AS total_unidades
        FROM condominios c
        LEFT JOIN apartamentos a ON c.id = a.condominio_id
        WHERE c.activo = 1
        GROUP BY c.id
        ORDER BY c.nombre ASC
    ");
    $condos = $stmt->fetchAll();
    jsonResponse(['success' => true, 'condominios' => $condos]);
}

// 2. Listado completo con métricas operativas (Super-Admin)
if ($action === 'list') {
    requireSuperAdminAuth();

    $stmt = $pdo->query("
        SELECT c.*,
               COUNT(DISTINCT a.id) AS total_apartamentos,
               COALESCE(SUM(CASE WHEN ca.estado = 'pendiente' THEN ca.monto_usd ELSE 0 END), 0) AS total_deuda_usd,
               COALESCE(SUM(CASE WHEN ca.estado = 'en_revision' THEN 1 ELSE 0 END), 0) AS pagos_en_revision,
               COALESCE(SUM(CASE WHEN ca.estado = 'pagado' THEN ca.monto_usd ELSE 0 END), 0) AS total_recaudado_usd,
               (SELECT u.nombre FROM usuarios u WHERE u.condominio_id = c.id LIMIT 1) AS admin_nombre,
               (SELECT u.username FROM usuarios u WHERE u.condominio_id = c.id LIMIT 1) AS admin_username
        FROM condominios c
        LEFT JOIN apartamentos a ON c.id = a.condominio_id
        LEFT JOIN cuotas_apartamento ca ON a.id = ca.apartamento_id
        GROUP BY c.id
        ORDER BY c.id ASC
    ");
    $condos = $stmt->fetchAll();

    foreach ($condos as &$c) {
        $c['total_deuda_usd_formatted'] = formatUSD($c['total_deuda_usd']);
        $c['total_recaudado_usd_formatted'] = formatUSD($c['total_recaudado_usd']);
        $c['es_activo_en_sesion'] = (getActiveCondominioId() === (int)$c['id']);
    }

    jsonResponse(['success' => true, 'condominios' => $condos, 'activo_id' => getActiveCondominioId()]);
}

// 3. Detalle de un condominio
if ($action === 'detail') {
    requireAdminAuth();
    $id = (int)($_GET['id'] ?? getActiveCondominioId());

    $stmt = $pdo->prepare("SELECT * FROM condominios WHERE id = ?");
    $stmt->execute([$id]);
    $condo = $stmt->fetch();

    if (!$condo) {
        jsonResponse(['success' => false, 'message' => 'Condominio no encontrado.'], 404);
    }

    // Contar unidades
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM apartamentos WHERE condominio_id = ?");
    $stmtCount->execute([$id]);
    $condo['total_unidades'] = (int)$stmtCount->fetchColumn();

    // Administrador local asignado
    $stmtAdmin = $pdo->prepare("SELECT id, username, nombre, email FROM usuarios WHERE condominio_id = ? AND rol = 'admin' LIMIT 1");
    $stmtAdmin->execute([$id]);
    $condo['admin'] = $stmtAdmin->fetch() ?: null;

    jsonResponse(['success' => true, 'condominio' => $condo]);
}

// 4. Crear nuevo condominio (Asistente de Creación)
if ($action === 'create') {
    requireSuperAdminAuth();

    $nombre = trim($_POST['nombre'] ?? '');
    if (empty($nombre)) {
        jsonResponse(['success' => false, 'message' => 'El nombre del condominio es obligatorio.'], 400);
    }

    // Generar slug
    $slug = trim($_POST['slug'] ?? '');
    if (empty($slug)) {
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($nombre)));
        $slug = trim($slug, '-');
    }

    // Comprobar unicidad de slug
    $stmtSlug = $pdo->prepare("SELECT COUNT(*) FROM condominios WHERE slug = ?");
    $stmtSlug->execute([$slug]);
    if ($stmtSlug->fetchColumn() > 0) {
        $slug .= '-' . mt_rand(100, 999);
    }

    $rif = trim($_POST['rif'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $pisosCant = max(1, (int)($_POST['pisos_cant'] ?? 10));
    $tienePB = isset($_POST['tiene_pb']) && ($_POST['tiene_pb'] === '1' || $_POST['tiene_pb'] === 'true' || $_POST['tiene_pb'] === 1);
    $aptosPBCant = $tienePB ? max(1, (int)($_POST['aptos_pb_cant'] ?? 2)) : 0;
    $aptosPorPiso = max(1, (int)($_POST['aptos_por_piso'] ?? 4));
    $nomenclatura = in_array($_POST['nomenclatura_tipo'] ?? '', ['letras', 'numeros']) ? $_POST['nomenclatura_tipo'] : 'letras';
    $tienePH = isset($_POST['tiene_ph']) && ($_POST['tiene_ph'] === '1' || $_POST['tiene_ph'] === 'true' || $_POST['tiene_ph'] === 1);
    $aptosPHCant = $tienePH ? max(1, (int)($_POST['aptos_ph_cant'] ?? 2)) : 0;

    $tasaDolar = (float)($_POST['tasa_dolar'] ?? getTasaDolar());
    $bancoPagoMovil = trim($_POST['banco_pago_movil'] ?? '');
    $bancoTransferencia = trim($_POST['banco_transferencia'] ?? '');
    $cuentaZelle = trim($_POST['cuenta_zelle'] ?? '');
    $cuotaDefault = (float)($_POST['monto_cuota_ordinaria_default'] ?? 35.00);

    $pdo->beginTransaction();
    try {
        $stmtInsert = $pdo->prepare("
            INSERT INTO condominios (
                slug, nombre, rif, direccion, pisos_cant, tiene_pb, aptos_pb_cant, 
                aptos_por_piso, nomenclatura_tipo, tasa_dolar, banco_pago_movil, 
                banco_transferencia, cuenta_zelle, monto_cuota_ordinaria_default, activo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmtInsert->execute([
            $slug, $nombre, $rif, $direccion, $pisosCant, $tienePB ? 1 : 0, $aptosPBCant,
            $aptosPorPiso, $nomenclatura, $tasaDolar, $bancoPagoMovil,
            $bancoTransferencia, $cuentaZelle, $cuotaDefault
        ]);

        $condominioId = (int)$pdo->lastInsertId();

        // Generar apartamentos de la estructura
        $resultadoGen = generarEstructuraCondominio($pdo, $condominioId, [
            'pisos_cant' => $pisosCant,
            'aptos_por_piso' => $aptosPorPiso,
            'tiene_pb' => $tienePB,
            'aptos_pb_cant' => $aptosPBCant,
            'nomenclatura_tipo' => $nomenclatura,
            'tiene_ph' => $tienePH,
            'aptos_ph_cant' => $aptosPHCant,
            'clave_default' => '123456'
        ]);

        // Crear SIEMPRE el usuario administrador local para este condominio
        $adminUsername = trim($_POST['admin_username'] ?? '');
        $adminPassword = trim($_POST['admin_password'] ?? '');
        $adminNombre = trim($_POST['admin_nombre'] ?? 'Administrador ' . $nombre);

        if (empty($adminUsername)) {
            $adminUsername = 'admin_' . str_replace('-', '', substr($slug, 0, 10));
        }
        if (empty($adminPassword)) {
            $adminPassword = 'admin' . mt_rand(100, 999);
        }

        // Verificar si el username ya existe
        $stmtUserCheck = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ?");
        $stmtUserCheck->execute([$adminUsername]);
        if ($stmtUserCheck->fetchColumn() > 0) {
            $adminUsername .= '_' . mt_rand(10, 99);
        }

        $hash = password_hash($adminPassword, PASSWORD_DEFAULT);
        $stmtNewAdmin = $pdo->prepare("INSERT INTO usuarios (condominio_id, username, password_hash, rol, nombre) VALUES (?, ?, ?, 'admin', ?)");
        $stmtNewAdmin->execute([$condominioId, $adminUsername, $hash, $adminNombre]);

        // Crear una cuota ordinaria de bienvenida
        $hoy = date('Y-m-d');
        $vencimiento = date('Y-m-d', strtotime('+15 days'));
        $stmtCuota = $pdo->prepare("INSERT INTO cuotas (condominio_id, titulo, descripcion, tipo, monto_usd, fecha_emision, fecha_vencimiento, estado) VALUES (?, ?, ?, 'ordinaria', ?, ?, ?, 'activa')");
        $stmtCuota->execute([
            $condominioId,
            'Cuota Ordinaria Inicial',
            'Fondo de mantenimiento y reserva operativa del condominio.',
            $cuotaDefault,
            $hoy,
            $vencimiento
        ]);
        $cuotaId = $pdo->lastInsertId();

        // Asignar cuota a todas las unidades generadas
        $stmtAptos = $pdo->prepare("SELECT id FROM apartamentos WHERE condominio_id = ?");
        $stmtAptos->execute([$condominioId]);
        $aptosIds = $stmtAptos->fetchAll(PDO::FETCH_COLUMN);

        $stmtAsignar = $pdo->prepare("INSERT INTO cuotas_apartamento (cuota_id, apartamento_id, monto_usd, estado) VALUES (?, ?, ?, 'pendiente')");
        foreach ($aptosIds as $aId) {
            $stmtAsignar->execute([$cuotaId, $aId, $cuotaDefault]);
        }

        // Comunicado de bienvenida
        $stmtCom = $pdo->prepare("INSERT INTO comunicados (condominio_id, titulo, contenido, prioridad, publicado_por) VALUES (?, ?, ?, 'importante', 'Junta Directiva')");
        $stmtCom->execute([
            $condominioId,
            'Bienvenida a ' . $nombre,
            'Iniciamos formalmente la digitalización del condominio con nuestro nuevo sistema. Cada apartamento puede ingresar con su código residencial y la clave por defecto 123456.'
        ]);

        $pdo->commit();

        // Obtener las unidades generadas para sincronización
        $stmtUnits = $pdo->prepare("SELECT id, piso, codigo, alicuota, propietario_nombre, cedula_identidad, telefono, email FROM apartamentos WHERE condominio_id = ? ORDER BY piso ASC, codigo ASC");
        $stmtUnits->execute([$condominioId]);
        $unitsGeneradas = $stmtUnits->fetchAll();

        // Sincronizar hacia Supabase: Condominio, Apartamentos y N apartamentos + 1 admin con claves por defecto
        $condoData = [
            'id' => $condominioId,
            'slug' => $slug,
            'nombre' => $nombre,
            'rif' => $rif,
            'direccion' => $direccion,
            'pisos_cant' => $pisosCant,
            'tiene_pb' => $tienePB,
            'aptos_pb_cant' => $aptosPBCant,
            'aptos_por_piso' => $aptosPorPiso,
            'tasa_dolar' => $tasaDolar,
            'banco_pago_movil' => $bancoPagoMovil,
            'banco_transferencia' => $bancoTransferencia,
            'cuenta_zelle' => $cuentaZelle
        ];
        $adminInfo = [
            'username' => $adminUsername,
            'nombre' => $adminNombre
        ];
        $syncSupabase = syncNewCondominioAndUsersToSupabase($condoData, $unitsGeneradas, $adminInfo);

        $totalUsuarios = count($unitsGeneradas) + 1;

        jsonResponse([
            'success' => true,
            'message' => "Condominio '{$nombre}' creado con éxito. Se generaron {$resultadoGen['total_generados']} apartamentos residenciales y {$totalUsuarios} usuarios ({$resultadoGen['total_generados']} residentes con clave 123456 + 1 admin con clave admin123) listos en Supabase.",
            'condominio_id' => $condominioId,
            'total_unidades' => $resultadoGen['total_generados'],
            'total_usuarios' => $totalUsuarios,
            'supabase_sync' => $syncSupabase,
            'codigos' => $resultadoGen['codigos']
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'message' => 'Error al crear el condominio: ' . $e->getMessage()], 500);
    }
}

// 5. Conmutar condominio activo (Super-Admin)
if ($action === 'switch') {
    requireSuperAdminAuth();
    $id = (int)($_POST['id'] ?? ($_GET['id'] ?? 0));

    $stmt = $pdo->prepare("SELECT * FROM condominios WHERE id = ?");
    $stmt->execute([$id]);
    $condo = $stmt->fetch();

    if (!$condo) {
        jsonResponse(['success' => false, 'message' => 'Condominio no encontrado.'], 404);
    }

    $_SESSION['condominio_id'] = (int)$condo['id'];
    jsonResponse([
        'success' => true,
        'message' => "Ahora estás gestionando: {$condo['nombre']}",
        'condominio' => $condo
    ]);
}

// 6. Actualizar información de un condominio
if ($action === 'update') {
    requireAdminAuth();
    $id = (int)($_POST['id'] ?? getActiveCondominioId());

    // Si es admin local, verificar que pertenezca a su condominio
    if (!isSuperAdmin() && (int)$_SESSION['condominio_id'] !== $id) {
        jsonResponse(['success' => false, 'message' => 'No tienes permisos para modificar este condominio.'], 403);
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $rif = trim($_POST['rif'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $bancoPagoMovil = trim($_POST['banco_pago_movil'] ?? '');
    $bancoTransferencia = trim($_POST['banco_transferencia'] ?? '');
    $cuentaZelle = trim($_POST['cuenta_zelle'] ?? '');
    $cuotaDefault = (float)($_POST['monto_cuota_ordinaria_default'] ?? 35.00);
    $tasaDolar = (float)($_POST['tasa_dolar'] ?? 0);

    $sql = "UPDATE condominios SET 
                nombre = ?, rif = ?, direccion = ?, 
                banco_pago_movil = ?, banco_transferencia = ?, cuenta_zelle = ?, 
                monto_cuota_ordinaria_default = ?";
    $params = [$nombre, $rif, $direccion, $bancoPagoMovil, $bancoTransferencia, $cuentaZelle, $cuotaDefault];

    if ($tasaDolar > 0) {
        $sql .= ", tasa_dolar = ?";
        $params[] = $tasaDolar;
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Si el Super-Admin desea actualizar las credenciales del Administrador local
    if (isSuperAdmin()) {
        $adminUser = trim($_POST['admin_username'] ?? '');
        $adminPass = trim($_POST['admin_password'] ?? '');
        $adminNombre = trim($_POST['admin_nombre'] ?? '');

        if (!empty($adminUser)) {
            $stmtFindAdm = $pdo->prepare("SELECT id FROM usuarios WHERE condominio_id = ? AND rol = 'admin' LIMIT 1");
            $stmtFindAdm->execute([$id]);
            $existingAdminId = $stmtFindAdm->fetchColumn();

            if ($existingAdminId) {
                if (!empty($adminPass)) {
                    $hash = password_hash($adminPass, PASSWORD_DEFAULT);
                    $stmtUpAdm = $pdo->prepare("UPDATE usuarios SET username = ?, password_hash = ?, nombre = COALESCE(NULLIF(?, ''), nombre) WHERE id = ?");
                    $stmtUpAdm->execute([$adminUser, $hash, $adminNombre, $existingAdminId]);
                } else {
                    $stmtUpAdm = $pdo->prepare("UPDATE usuarios SET username = ?, nombre = COALESCE(NULLIF(?, ''), nombre) WHERE id = ?");
                    $stmtUpAdm->execute([$adminUser, $adminNombre, $existingAdminId]);
                }
            } else {
                $hash = password_hash(!empty($adminPass) ? $adminPass : 'admin123', PASSWORD_DEFAULT);
                $stmtInsAdm = $pdo->prepare("INSERT INTO usuarios (condominio_id, username, password_hash, rol, nombre) VALUES (?, ?, ?, 'admin', ?)");
                $stmtInsAdm->execute([$id, $adminUser, $hash, !empty($adminNombre) ? $adminNombre : 'Administrador ' . $nombre]);
            }
        }
    }

    jsonResponse(['success' => true, 'message' => 'Datos del condominio y administrador actualizados correctamente.']);
}

// 7. Eliminar condominio
if ($action === 'delete') {
    requireSuperAdminAuth();
    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 1) {
        jsonResponse(['success' => false, 'message' => 'El condominio principal base no puede ser eliminado.'], 400);
    }

    $stmt = $pdo->prepare("DELETE FROM condominios WHERE id = ?");
    $stmt->execute([$id]);

    if (getActiveCondominioId() === $id) {
        $_SESSION['condominio_id'] = 1;
    }

    jsonResponse(['success' => true, 'message' => 'Condominio y todos sus registros asociados han sido eliminados.']);
}

jsonResponse(['success' => false, 'message' => 'Acción no válida.'], 400);
