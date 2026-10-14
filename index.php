<?php
// index.php
require_once __DIR__ . '/config/helpers.php';

// Si ya está autenticado, redirigir según su rol
if (isset($_SESSION['user_id'])) {
    if (($_SESSION['user_role'] ?? '') === 'super_admin') {
        header("Location: superadmin_dashboard.php");
        exit;
    }
    if (($_SESSION['user_role'] ?? '') === 'admin') {
        header("Location: admin_dashboard.php");
        exit;
    }
}
if (isset($_SESSION['apto_id'])) {
    header("Location: resident_dashboard.php");
    exit;
}

$pdo = getDBConnection();

// Cargar todos los condominios activos
$condominios = $pdo->query("
    SELECT c.*, COUNT(a.id) AS total_unidades 
    FROM condominios c 
    LEFT JOIN apartamentos a ON c.id = a.condominio_id 
    WHERE c.activo = 1 
    GROUP BY c.id 
    ORDER BY c.id ASC
")->fetchAll();

if (empty($condominios)) {
    // Si no hay ninguno, inicializar por defecto
    require_once __DIR__ . '/config/init_db.php';
    initializeDatabase();
    header("Location: index.php");
    exit;
}

// Determinar el condominio seleccionado inicialmente
$slugParam = trim($_GET['c'] ?? '');
$idParam = (int)($_GET['condominio_id'] ?? 0);

$activeCondo = $condominios[0];
foreach ($condominios as $c) {
    if ($idParam > 0 && (int)$c['id'] === $idParam) {
        $activeCondo = $c;
        break;
    }
    if (!empty($slugParam) && $c['slug'] === $slugParam) {
        $activeCondo = $c;
        break;
    }
}

$tasaDolar = getTasaDolar($activeCondo['id']);
$fechaValorBCV = getConfig('tasa_bcv_fecha_valor', 'Oficial');

// Cargar apartamentos para el condominio inicial seleccionado
$stmtAptos = $pdo->prepare("SELECT id, codigo, piso, tipo, propietario_nombre FROM apartamentos WHERE condominio_id = ? ORDER BY piso ASC, codigo ASC");
$stmtAptos->execute([$activeCondo['id']]);
$aptos = $stmtAptos->fetchAll();

// Agrupar por pisos dinámicamente
$pisosAgrupados = [];
foreach ($aptos as $a) {
    $pisoNum = (int)$a['piso'];
    $nombrePiso = $pisoNum === 0 ? 'Planta Baja (PB)' : 'Piso ' . $pisoNum;
    $pisosAgrupados[$pisoNum]['nombre'] = $nombrePiso;
    $pisosAgrupados[$pisoNum]['unidades'][] = $a;
}
ksort($pisosAgrupados);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema - <?= htmlspecialchars($activeCondo['nombre']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            background: radial-gradient(circle at 10% 20%, rgba(59, 130, 246, 0.12) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(139, 92, 246, 0.12) 0%, transparent 40%),
                        var(--bg-primary);
        }
        .login-box {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 500px;
            padding: 2.25rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
        }
        .condo-select-box {
            background: var(--bg-card);
            border: 1px solid var(--border-glow);
            border-radius: var(--radius-sm);
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
        }
        .tab-switch {
            display: flex;
            background-color: var(--bg-card);
            border-radius: var(--radius-sm);
            padding: 0.35rem;
            margin-bottom: 1.75rem;
            gap: 0.35rem;
        }
        .tab-btn {
            flex: 1;
            padding: 0.65rem 0.5rem;
            border: none;
            background: transparent;
            color: var(--text-secondary);
            font-size: 0.88rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transition);
        }
        .tab-btn.active {
            background-color: var(--primary);
            color: #fff;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
        }
        .demo-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 1px dashed var(--border-color);
        }
        .demo-btn {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transition);
        }
        .demo-btn:hover {
            background-color: var(--primary-light);
            color: #60a5fa;
            border-color: var(--primary);
        }
        .bcv-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            margin-top: 0.65rem;
            font-size: 0.8rem;
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }
        @media (max-width: 480px) {
            .login-box {
                padding: 1.5rem 1.15rem;
            }
            .tab-btn {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-box">
        <!-- Selector de Condominio (Si hay múltiples o para conmutar) -->
        <div class="condo-select-box">
            <label style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-bottom: 0.35rem; font-weight: 600;">
                <i class="bi bi-buildings"></i> SELECCIONE EL CONDOMINIO:
            </label>
            <select id="select-condominio" class="form-control" style="font-weight: 600; color: #fff; background: var(--bg-primary);" onchange="handleCondoChange(this.value)">
                <?php foreach ($condominios as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $activeCondo['id'] ? 'selected' : '' ?>>
                        🏢 <?= htmlspecialchars($c['nombre']) ?> (<?= $c['total_unidades'] ?> Aptos)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div class="brand-icon" style="margin: 0 auto 0.85rem; width: 52px; height: 52px; font-size: 1.6rem;">
                <i class="bi bi-buildings"></i>
            </div>
            <h2 id="condo-display-name" style="font-size: 1.4rem; font-weight: 800; color: #fff;"><?= htmlspecialchars($activeCondo['nombre']) ?></h2>
            <p id="condo-display-specs" style="font-size: 0.84rem; color: var(--text-secondary); margin-top: 0.25rem;">
                <?= ($activeCondo['tiene_pb'] ? 'PB + ' : '') . $activeCondo['pisos_cant'] ?> Pisos (<?= count($aptos) ?> Apartamentos Residenciales)
            </p>
            <div class="bcv-pill">
                <i class="bi bi-bank2"></i>
                <span>Tasa Oficial BCV: <strong>Bs. <?= number_format($tasaDolar, 2, ',', '.') ?> / USD</strong></span>
            </div>
            <?php if (!empty($fechaValorBCV)): ?>
                <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.2rem;">
                    Fecha Valor BCV: <?= htmlspecialchars($fechaValorBCV) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-switch">
            <button class="tab-btn active" id="tab-resident-btn" onclick="showLoginTab('resident')">
                <i class="bi bi-door-open"></i> Propietario / Apto
            </button>
            <button class="tab-btn" id="tab-admin-btn" onclick="showLoginTab('admin')">
                <i class="bi bi-shield-lock"></i> Administración
            </button>
        </div>

        <!-- Formulario Residente / Apartamento -->
        <form id="form-login-resident" onsubmit="handleResidentLogin(event)">
            <input type="hidden" name="condominio_id" id="hidden-condominio-id" value="<?= $activeCondo['id'] ?>">

            <div class="form-group">
                <label class="form-label"><i class="bi bi-house"></i> Apartamento Residencial:</label>
                <select name="codigo" id="res-codigo" class="form-control" required>
                    <option value="">-- Seleccione su apartamento --</option>
                    <?php foreach ($pisosAgrupados as $pNum => $pData): ?>
                        <optgroup label="<?= htmlspecialchars($pData['nombre']) ?>">
                            <?php foreach ($pData['unidades'] as $a): ?>
                                <option value="<?= $a['codigo'] ?>">Apto <?= $a['codigo'] ?> (<?= htmlspecialchars($a['propietario_nombre']) ?>)</option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label"><i class="bi bi-key"></i> Contraseña de Acceso:</label>
                <input type="password" name="password" id="res-password" class="form-control" placeholder="Contraseña (por defecto: 123456)" required>
                <small style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem; display: block;">
                    Clave inicial: <code style="color: var(--primary);">123456</code>
                </small>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;" id="btn-login-res">
                <i class="bi bi-box-arrow-in-right"></i> Entrar al Portal del Apartamento
            </button>
        </form>

        <!-- Formulario Administrador -->
        <form id="form-login-admin" style="display: none;" onsubmit="handleAdminLogin(event)">
            <div class="form-group">
                <label class="form-label"><i class="bi bi-person-badge"></i> Usuario Administrador:</label>
                <input type="text" name="username" id="admin-user" class="form-control" placeholder="Ej: admin o admin_torre" required>
            </div>

            <div class="form-group">
                <label class="form-label"><i class="bi bi-key"></i> Contraseña:</label>
                <input type="password" name="password" id="admin-pass" class="form-control" placeholder="Contraseña (admin123)" required>
                <small style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem; display: block; line-height: 1.4;">
                    Superusuario Global: <code style="color: #c084fc;">clusterlg</code> | Clave: <code style="color: #c084fc;">glretsulc</code><br>
                    Admin Local: <code style="color: var(--primary);">admin</code> (o usuario del edificio) | Clave: <code style="color: var(--primary);">admin123</code>
                </small>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;" id="btn-login-adm">
                <i class="bi bi-shield-check"></i> Ingresar al Dashboard
            </button>
        </form>
    </div>
</div>

<script src="assets/js/app.js"></script>
<script>
function showLoginTab(tab) {
    const resForm = document.getElementById('form-login-resident');
    const admForm = document.getElementById('form-login-admin');
    const tabResBtn = document.getElementById('tab-resident-btn');
    const tabAdmBtn = document.getElementById('tab-admin-btn');

    if (tab === 'resident') {
        resForm.style.display = 'block';
        admForm.style.display = 'none';
        tabResBtn.classList.add('active');
        tabAdmBtn.classList.remove('active');
    } else {
        resForm.style.display = 'none';
        admForm.style.display = 'block';
        tabResBtn.classList.remove('active');
        tabAdmBtn.classList.add('active');
    }
}

async function handleCondoChange(condoId) {
    const hiddenId = document.getElementById('hidden-condominio-id');
    if (hiddenId) hiddenId.value = condoId;

    const selectApto = document.getElementById('res-codigo');
    selectApto.innerHTML = '<option value="">Cargando apartamentos...</option>';

    // Consultar detalle de apartamentos del condominio elegido
    const res = await apiRequest(`api/apartamentos.php?action=list_public&condominio_id=${condoId}`);
    if (res.success) {
        let html = '<option value="">-- Seleccione su apartamento --</option>';
        res.pisos.forEach(piso => {
            html += `<optgroup label="${piso.nombre}">`;
            piso.unidades.forEach(u => {
                html += `<option value="${u.codigo}">Apto ${u.codigo} (${u.propietario_nombre || 'Sin titular'})</option>`;
            });
            html += `</optgroup>`;
        });
        selectApto.innerHTML = html;

        // Actualizar datos del encabezado
        const condoSelect = document.getElementById('select-condominio');
        const selectedText = condoSelect.options[condoSelect.selectedIndex].text.replace('🏢 ', '');
        document.getElementById('condo-display-name').textContent = selectedText.split(' (')[0];
        document.getElementById('condo-display-specs').textContent = `${res.pisos.length} Pisos (${res.total_unidades} Apartamentos Residenciales)`;

        // Actualizar badges demostrativos
        const badgesContainer = document.getElementById('demo-badges-container');
        let badgesHtml = `
            <span style="font-size: 0.74rem; color: var(--text-muted); width: 100%;">Accesos rápidos de demostración:</span>
            <button type="button" class="demo-btn" onclick="quickFillAdmin()">👑 Admin</button>
        `;
        let count = 0;
        res.pisos.forEach(p => {
            p.unidades.forEach(u => {
                if (count < 4) {
                    badgesHtml += `<button type="button" class="demo-btn" onclick="quickFillResident('${u.codigo}')">🔑 Apto ${u.codigo}</button>`;
                    count++;
                }
            });
        });
        badgesContainer.innerHTML = badgesHtml;
    }
}

async function handleResidentLogin(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-login-res');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Verificando...';

    const formData = new FormData(e.target);
    const res = await apiRequest('api/auth.php?action=login_resident', {
        method: 'POST',
        body: formData
    });

    if (res.success) {
        showToast(res.message, 'success');
        setTimeout(() => window.location.href = res.redirect, 400);
    } else {
        showToast(res.message, 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Entrar al Portal del Apartamento';
    }
}

async function handleAdminLogin(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-login-adm');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Verificando...';

    const formData = new FormData(e.target);
    const res = await apiRequest('api/auth.php?action=login_admin', {
        method: 'POST',
        body: formData
    });

    if (res.success) {
        showToast(res.message, 'success');
        setTimeout(() => window.location.href = res.redirect, 400);
    } else {
        showToast(res.message, 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-shield-check"></i> Ingresar al Dashboard';
    }
}

function quickFillSuperAdmin() {
    showLoginTab('admin');
    document.getElementById('admin-user').value = 'clusterlg';
    document.getElementById('admin-pass').value = 'glretsulc';
    document.getElementById('form-login-admin').requestSubmit();
}

function quickFillAdmin() {
    showLoginTab('admin');
    document.getElementById('admin-user').value = 'admin';
    document.getElementById('admin-pass').value = 'admin123';
    document.getElementById('form-login-admin').requestSubmit();
}

function quickFillResident(codigo) {
    showLoginTab('resident');
    document.getElementById('res-codigo').value = codigo;
    document.getElementById('res-password').value = '123456';
    document.getElementById('form-login-resident').requestSubmit();
}
</script>

</body>
</html>
