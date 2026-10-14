<?php
// api/tickets.php
require_once __DIR__ . '/../config/helpers.php';

$pdo = getDBConnection();
$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

if ($action === 'list') {
    requireAdminAuth();
    $filtroTipo = $_GET['tipo'] ?? 'todos';
    $filtroEstado = $_GET['estado'] ?? 'todos';

    $cId = (int)($_GET['condominio_id'] ?? getActiveCondominioId());
    $where = ["a.condominio_id = ?"];
    $params = [$cId];
    if (in_array($filtroTipo, ['queja', 'solicitud', 'novedad_averia'])) {
        $where[] = "t.tipo = ?";
        $params[] = $filtroTipo;
    }
    if (in_array($filtroEstado, ['pendiente', 'en_revision', 'resuelto', 'rechazado'])) {
        $where[] = "t.estado = ?";
        $params[] = $filtroEstado;
    }

    $whereSql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

    $sql = "SELECT t.*, a.codigo AS apto_codigo, a.piso AS apto_piso, a.propietario_nombre, a.telefono AS apto_telefono
            FROM tickets t
            JOIN apartamentos a ON t.apartamento_id = a.id
            $whereSql
            ORDER BY t.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tickets = $stmt->fetchAll();

    jsonResponse(['success' => true, 'tickets' => $tickets]);
}

if ($action === 'resident_list') {
    requireResidentAuth();
    $aptoId = (int)$_SESSION['apto_id'];

    $sql = "SELECT * FROM tickets WHERE apartamento_id = ? ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$aptoId]);
    $tickets = $stmt->fetchAll();

    jsonResponse(['success' => true, 'tickets' => $tickets]);
}

if ($action === 'create') {
    requireResidentAuth();
    $aptoId = (int)$_SESSION['apto_id'];

    $tipo = in_array($_POST['tipo'] ?? '', ['queja', 'solicitud', 'novedad_averia']) ? $_POST['tipo'] : 'solicitud';
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $prioridad = in_array($_POST['prioridad'] ?? '', ['baja', 'media', 'alta', 'urgente']) ? $_POST['prioridad'] : 'media';

    if (empty($titulo) || empty($descripcion)) {
        jsonResponse(['success' => false, 'message' => 'Por favor complete el título y la descripción del requerimiento.'], 400);
    }

    $fotoPath = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['foto']['tmp_name'];
        $fileName = $_FILES['foto']['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        if (in_array($ext, $allowedExts)) {
            $nuevoNombre = 'ticket_' . $aptoId . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $destino = __DIR__ . '/../uploads/evidencias/' . $nuevoNombre;
            if (move_uploaded_file($fileTmp, $destino)) {
                $fotoPath = 'uploads/evidencias/' . $nuevoNombre;
            }
        }
    }

    $stmt = $pdo->prepare("INSERT INTO tickets (apartamento_id, tipo, titulo, descripcion, foto_path, prioridad, estado) VALUES (?, ?, ?, ?, ?, ?, 'pendiente')");
    $stmt->execute([$aptoId, $tipo, $titulo, $descripcion, $fotoPath, $prioridad]);

    jsonResponse(['success' => true, 'message' => 'Requerimiento registrado exitosamente. La administración ha sido notificada.']);
}

if ($action === 'respond') {
    requireAdminAuth();
    $ticketId = (int)($_POST['ticket_id'] ?? 0);
    $nuevoEstado = in_array($_POST['estado'] ?? '', ['pendiente', 'en_revision', 'resuelto', 'rechazado']) ? $_POST['estado'] : 'en_revision';
    $respuesta = trim($_POST['respuesta_admin'] ?? '');

    if ($ticketId <= 0) {
        jsonResponse(['success' => false, 'message' => 'ID de ticket inválido.'], 400);
    }

    $stmt = $pdo->prepare("UPDATE tickets SET estado = ?, respuesta_admin = ?, fecha_respuesta = NOW() WHERE id = ?");
    $stmt->execute([$nuevoEstado, $respuesta, $ticketId]);

    jsonResponse(['success' => true, 'message' => 'Respuesta enviada y estado actualizado correctamente.']);
}

jsonResponse(['success' => false, 'message' => 'Acción no válida.'], 400);
