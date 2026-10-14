<?php
// api/auth.php
require_once __DIR__ . '/../config/helpers.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$pdo = getDBConnection();

if ($action === 'login_admin') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        jsonResponse(['success' => false, 'message' => 'Complete todos los campos.'], 400);
    }

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_role'] = $user['rol'];
        
        if ($user['rol'] === 'super_admin') {
            // Super Admin puede conmutar a cualquier condominio, asignamos el primero por defecto
            if (!isset($_SESSION['condominio_id'])) {
                $_SESSION['condominio_id'] = 1;
            }
            jsonResponse([
                'success' => true, 
                'redirect' => 'superadmin_dashboard.php', 
                'message' => 'Bienvenido, Super Administrador Global',
                'role' => 'super_admin'
            ]);
        } else {
            // Admin local de un condominio
            $_SESSION['condominio_id'] = (int)($user['condominio_id'] ?: 1);
            jsonResponse([
                'success' => true, 
                'redirect' => 'admin_dashboard.php', 
                'message' => 'Inicio de sesión exitoso',
                'role' => 'admin'
            ]);
        }
    } else {
        jsonResponse(['success' => false, 'message' => 'Usuario o contraseña incorrectos.'], 401);
    }
}

if ($action === 'login_resident') {
    $condominioId = (int)($_POST['condominio_id'] ?? 1);
    $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if ($condominioId <= 0 || empty($codigo) || empty($password)) {
        jsonResponse(['success' => false, 'message' => 'Seleccione el condominio, su apartamento e ingrese la contraseña.'], 400);
    }

    $stmt = $pdo->prepare("SELECT * FROM apartamentos WHERE condominio_id = ? AND codigo = ? LIMIT 1");
    $stmt->execute([$condominioId, $codigo]);
    $apto = $stmt->fetch();

    if ($apto) {
        if (password_verify($password, $apto['password_hash'])) {
            $_SESSION['apto_id'] = $apto['id'];
            $_SESSION['apto_condominio_id'] = (int)$apto['condominio_id'];
            $_SESSION['condominio_id'] = (int)$apto['condominio_id'];
            $_SESSION['apto_codigo'] = $apto['codigo'];
            $_SESSION['apto_propietario'] = $apto['propietario_nombre'];
            $_SESSION['apto_piso'] = $apto['piso'];
            $_SESSION['apto_tipo'] = $apto['tipo'];
            
            jsonResponse([
                'success' => true, 
                'redirect' => 'resident_dashboard.php', 
                'message' => 'Bienvenido, apartamento ' . $apto['codigo']
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Contraseña incorrecta para el apartamento ' . $codigo . '.'], 401);
        }
    } else {
        jsonResponse(['success' => false, 'message' => 'El apartamento no fue encontrado en el condominio seleccionado.'], 404);
    }
}

if ($action === 'logout') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: ../index.php");
    exit;
}

jsonResponse(['success' => false, 'message' => 'Acción no válida.'], 400);
