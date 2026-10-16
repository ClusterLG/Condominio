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

if ($action === 'get_admin_profile') {
    requireAdminAuth();
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if ($userId <= 0) {
        jsonResponse(['success' => false, 'message' => 'Sesión no válida o expirada.'], 401);
    }

    $stmt = $pdo->prepare("SELECT id, condominio_id, username, rol, nombre, email, cedula, telefono, telefono_secundario, cargo, created_at FROM usuarios WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
    }

    $condoNombre = 'Super Administrador Global';
    if (!empty($user['condominio_id'])) {
        $cStmt = $pdo->prepare("SELECT nombre FROM condominios WHERE id = ? LIMIT 1");
        $cStmt->execute([(int)$user['condominio_id']]);
        $condoNombre = $cStmt->fetchColumn() ?: 'Condominio Local';
    }
    $user['condominio_nombre'] = $condoNombre;

    jsonResponse(['success' => true, 'user' => $user]);
}

if ($action === 'update_admin_profile') {
    requireAdminAuth();
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if ($userId <= 0) {
        jsonResponse(['success' => false, 'message' => 'Sesión no válida o expirada.'], 401);
    }

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $telefonoSecundario = trim($_POST['telefono_secundario'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($nombre)) {
        jsonResponse(['success' => false, 'message' => 'El nombre completo es obligatorio.'], 400);
    }

    if (empty($username)) {
        jsonResponse(['success' => false, 'message' => 'El nombre de usuario es obligatorio.'], 400);
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'El correo electrónico no tiene un formato válido.'], 400);
    }

    // Verificar si el username ya está en uso por otro usuario
    $stmtUserCheck = $pdo->prepare("SELECT id FROM usuarios WHERE username = ? AND id != ? LIMIT 1");
    $stmtUserCheck->execute([$username, $userId]);
    if ($stmtUserCheck->fetch()) {
        jsonResponse(['success' => false, 'message' => 'El nombre de usuario ya está en uso por otra persona. Elija uno diferente.'], 400);
    }

    // Si intenta cambiar la contraseña
    $updatePassword = false;
    $newPasswordHash = null;

    if (!empty($newPassword) || !empty($confirmPassword) || !empty($currentPassword)) {
        if (empty($currentPassword)) {
            jsonResponse(['success' => false, 'message' => 'Debe ingresar su contraseña actual para poder cambiarla.'], 400);
        }

        if (!password_verify($currentPassword, $user['password_hash'])) {
            jsonResponse(['success' => false, 'message' => 'La contraseña actual ingresada es incorrecta.'], 400);
        }

        if (strlen($newPassword) < 6) {
            jsonResponse(['success' => false, 'message' => 'La nueva contraseña debe tener un mínimo de 6 caracteres.'], 400);
        }

        if ($newPassword !== $confirmPassword) {
            jsonResponse(['success' => false, 'message' => 'La nueva contraseña y su confirmación no coinciden.'], 400);
        }

        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updatePassword = true;
    }

    if ($updatePassword) {
        $stmtUp = $pdo->prepare("UPDATE usuarios SET 
            nombre = ?, 
            username = ?, 
            email = ?, 
            cedula = ?, 
            telefono = ?, 
            telefono_secundario = ?, 
            cargo = ?, 
            password_hash = ? 
            WHERE id = ?");
        $stmtUp->execute([$nombre, $username, $email, $cedula, $telefono, $telefonoSecundario, $cargo, $newPasswordHash, $userId]);
    } else {
        $stmtUp = $pdo->prepare("UPDATE usuarios SET 
            nombre = ?, 
            username = ?, 
            email = ?, 
            cedula = ?, 
            telefono = ?, 
            telefono_secundario = ?, 
            cargo = ? 
            WHERE id = ?");
        $stmtUp->execute([$nombre, $username, $email, $cedula, $telefono, $telefonoSecundario, $cargo, $userId]);
    }

    $_SESSION['user_name'] = $nombre;

    jsonResponse([
        'success' => true, 
        'message' => $updatePassword 
            ? '¡Perfil y contraseña de ingreso actualizados con éxito!' 
            : '¡Datos del perfil de administrador actualizados con éxito!',
        'user' => [
            'id' => $userId,
            'nombre' => $nombre,
            'username' => $username,
            'email' => $email,
            'cedula' => $cedula,
            'telefono' => $telefono,
            'telefono_secundario' => $telefonoSecundario,
            'cargo' => $cargo,
            'password_changed' => $updatePassword
        ]
    ]);
}

jsonResponse(['success' => false, 'message' => 'Acción no válida.'], 400);
