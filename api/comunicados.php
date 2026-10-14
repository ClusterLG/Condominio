<?php
// api/comunicados.php
require_once __DIR__ . '/../config/helpers.php';

$pdo = getDBConnection();
$condominioId = getActiveCondominioId();
$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

if ($action === 'list') {
    $cId = (int)($_GET['condominio_id'] ?? $condominioId);
    $stmt = $pdo->prepare("SELECT * FROM comunicados WHERE condominio_id = ? ORDER BY id DESC");
    $stmt->execute([$cId]);
    $comunicados = $stmt->fetchAll();
    jsonResponse(['success' => true, 'comunicados' => $comunicados]);
}

if ($action === 'create') {
    requireAdminAuth();
    $cId = (int)($_POST['condominio_id'] ?? $condominioId);
    $titulo = trim($_POST['titulo'] ?? '');
    $contenido = trim($_POST['contenido'] ?? '');
    $prioridad = in_array($_POST['prioridad'] ?? '', ['informativo', 'importante', 'urgente']) ? $_POST['prioridad'] : 'informativo';
    $publicado_por = trim($_POST['publicado_por'] ?? 'Junta Directiva');

    if (empty($titulo) || empty($contenido)) {
        jsonResponse(['success' => false, 'message' => 'Complete título y contenido del comunicado.'], 400);
    }

    $stmt = $pdo->prepare("INSERT INTO comunicados (condominio_id, titulo, contenido, prioridad, publicado_por) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$cId, $titulo, $contenido, $prioridad, $publicado_por]);

    jsonResponse(['success' => true, 'message' => 'Comunicado publicado en la cartelera digital.']);
}

if ($action === 'delete') {
    requireAdminAuth();
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM comunicados WHERE id = ?");
    $stmt->execute([$id]);
    jsonResponse(['success' => true, 'message' => 'Comunicado eliminado.']);
}

jsonResponse(['success' => false, 'message' => 'Acción no válida.'], 400);
