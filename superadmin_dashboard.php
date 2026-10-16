<?php
// superadmin_dashboard.php
require_once __DIR__ . '/config/helpers.php';
requireSuperAdminAuth();

$pdo = getDBConnection();
$tasaDolar = getTasaDolar();
$fechaValorBCV = getConfig('tasa_bcv_fecha_valor', 'Oficial');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super-Administrador Global - Multi-Condominios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .super-header {
            background: rgba(17, 24, 39, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .super-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        .condo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        .condo-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            transition: var(--transition);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .condo-card:hover {
            transform: translateY(-4px);
            border-color: var(--border-glow);
            box-shadow: var(--shadow-glow);
        }
        .condo-card.active-session {
            border-color: var(--primary);
            box-shadow: 0 0 0 1px var(--primary);
        }
        .condo-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .condo-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1.25rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }
        .stat-mini-box {
            background: var(--bg-primary);
            border-radius: var(--radius-sm);
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            text-align: center;
        }
        .structure-preview-box {
            background: var(--bg-primary);
            border: 1px dashed var(--primary);
            border-radius: var(--radius-sm);
            padding: 1rem;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        @media (max-width: 768px) {
            .super-header {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            .super-container {
                padding: 1rem;
            }
            .condo-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<header class="super-header">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <div class="brand-icon" style="background: linear-gradient(135deg, #8b5cf6, #3b82f6); width: 44px; height: 44px;">
            <i class="bi bi-buildings"></i>
        </div>
        <div>
            <div style="font-size: 1.15rem; font-weight: 800; color: #fff; display: flex; align-items: center; gap: 0.5rem;">
                CondoAdmin <span class="badge" style="background: var(--purple-bg); color: #c084fc; font-size: 0.7rem; border: 1px solid rgba(139,92,246,0.3);">SUPER ADMIN</span>
            </div>
            <div style="font-size: 0.78rem; color: var(--text-secondary);">
                Plataforma Centralizada de Gestión Multi-Condominio
            </div>
        </div>
    </div>

    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <div style="background: var(--bg-card); padding: 0.4rem 0.85rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color); font-size: 0.8rem; display: flex; align-items: center; gap: 0.5rem;">
            <i class="bi bi-bank2" style="color: var(--success);"></i>
            <span>BCV: <strong>Bs. <?= number_format($tasaDolar, 2, ',', '.') ?></strong></span>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size: 0.85rem; color: var(--text-secondary);">
                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Super Admin') ?>
            </span>
            <a href="admin_dashboard.php" class="btn btn-secondary btn-sm" title="Ir al panel y editar mi perfil">
                <i class="bi bi-person-gear"></i> Mi Perfil
            </a>
            <a href="api/auth.php?action=logout" class="btn btn-secondary btn-sm" title="Cerrar Sesión">
                <i class="bi bi-box-arrow-right"></i> Salir
            </a>
        </div>
    </div>
</header>

<main class="super-container">
    <!-- Métricas Globales -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--primary-light); color: var(--primary);">
                <i class="bi bi-buildings"></i>
            </div>
            <div>
                <div class="stat-label">Condominios Activos</div>
                <div class="stat-value" id="stat-total-condos">--</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: var(--info-bg); color: var(--info);">
                <i class="bi bi-door-open"></i>
            </div>
            <div>
                <div class="stat-label">Total Unidades Residenciales</div>
                <div class="stat-value" id="stat-total-unidades">--</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: var(--warning-bg); color: var(--warning);">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <div class="stat-label">Pagos en Auditoría</div>
                <div class="stat-value" id="stat-total-revision">--</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: var(--danger-bg); color: var(--danger);">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <div class="stat-label">Deuda Total Acumulada</div>
                <div class="stat-value" id="stat-total-deuda">--</div>
            </div>
        </div>
    </div>

    <!-- Barra de Acciones y Título -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #fff;">Edificios y Proyectos Registrados</h1>
            <p style="font-size: 0.85rem; color: var(--text-secondary);">
                Cada condominio cuenta con su propia configuración de pisos, apartamentos, residentes y finanzas aisladas.
            </p>
        </div>

        <div style="display: flex; gap: 0.75rem;">
            <button class="btn btn-secondary" onclick="SuperApp.loadCondominios()">
                <i class="bi bi-arrow-clockwise"></i> Refrescar
            </button>
            <button class="btn btn-primary" onclick="SuperApp.openCreateModal()">
                <i class="bi bi-plus-circle"></i> Nuevo Condominio
            </button>
        </div>
    </div>

    <!-- Contenedor de Condominios -->
    <div class="condo-grid" id="condominios-container">
        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-muted);">
            <i class="bi bi-arrow-repeat spin"></i> Cargando condominios...
        </div>
    </div>
</main>

<!-- MODAL: Crear Nuevo Condominio (Asistente Arquitectónico) -->
<div class="modal-overlay" id="modal-create-condo">
    <div class="modal-content" style="max-width: 750px;">
        <div class="modal-header">
            <div class="modal-title">
                <i class="bi bi-building-add" style="color: var(--primary);"></i>
                <span>Asistente de Nuevo Condominio</span>
            </div>
            <button class="modal-close" onclick="closeModal('modal-create-condo')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form-create-condo" onsubmit="SuperApp.handleCreateCondo(event)">
                <div style="font-weight: 700; color: var(--primary); margin-bottom: 0.75rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.35rem;">
                    1. Identificación del Condominio
                </div>
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Nombre del Condominio / Edificio *</label>
                        <input type="text" name="nombre" id="new-condo-nombre" class="form-control" placeholder="Ej: Residencias Monte Sacro, Torre Norte" required oninput="SuperApp.updatePreview()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">RIF del Condominio</label>
                        <input type="text" name="rif" class="form-control" placeholder="Ej: J-12345678-9">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Dirección / Ubicación</label>
                    <input type="text" name="direccion" class="form-control" placeholder="Ej: Av. Francisco de Miranda, Urb. Los Palos Grandes, Caracas">
                </div>

                <div style="font-weight: 700; color: var(--primary); margin-top: 1.5rem; margin-bottom: 0.75rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.35rem;">
                    2. Estructura y Generador Automático de Apartamentos
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Cantidad de Pisos *</label>
                        <input type="number" name="pisos_cant" id="new-condo-pisos" class="form-control" min="1" max="60" value="8" required oninput="SuperApp.updatePreview()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Aptos por Piso *</label>
                        <input type="number" name="aptos_por_piso" id="new-condo-aptos-piso" class="form-control" min="1" max="20" value="4" required oninput="SuperApp.updatePreview()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nomenclatura</label>
                        <select name="nomenclatura_tipo" id="new-condo-nomenclatura" class="form-control" onchange="SuperApp.updatePreview()">
                            <option value="letras">Letras (1A, 1B, 1C...)</option>
                            <option value="numeros">Números (101, 102, 103...)</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.5rem;">
                    <div class="form-group" style="background: var(--bg-primary); padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.85rem; font-weight: 600;">
                            <input type="checkbox" name="tiene_pb" id="new-condo-pb" value="1" checked onchange="SuperApp.updatePreview()">
                            ¿Tiene Planta Baja (PB)?
                        </label>
                        <div id="pb-cant-container" style="margin-top: 0.5rem;">
                            <label style="font-size: 0.75rem; color: var(--text-muted);">Aptos en Planta Baja:</label>
                            <input type="number" name="aptos_pb_cant" id="new-condo-pb-cant" class="form-control" min="1" max="10" value="2" oninput="SuperApp.updatePreview()">
                        </div>
                    </div>

                    <div class="form-group" style="background: var(--bg-primary); padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.85rem; font-weight: 600;">
                            <input type="checkbox" name="tiene_ph" id="new-condo-ph" value="1" onchange="SuperApp.updatePreview()">
                            ¿Tiene Penthouse (PH)?
                        </label>
                        <div id="ph-cant-container" style="margin-top: 0.5rem; display: none;">
                            <label style="font-size: 0.75rem; color: var(--text-muted);">Aptos en Penthouse:</label>
                            <input type="number" name="aptos_ph_cant" id="new-condo-ph-cant" class="form-control" min="1" max="10" value="2" oninput="SuperApp.updatePreview()">
                        </div>
                    </div>
                </div>

                <!-- Recuadro de Previsualización en Vivo -->
                <div class="structure-preview-box" id="structure-preview">
                    <i class="bi bi-info-circle" style="color: var(--primary);"></i>
                    <span id="preview-text">Calculando unidades...</span>
                </div>

                <div style="font-weight: 700; color: var(--primary); margin-top: 1.5rem; margin-bottom: 0.75rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.35rem;">
                    3. Parámetros Financieros Iniciales
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Cuota Ordinaria Base (USD)</label>
                        <input type="number" step="0.01" name="monto_cuota_ordinaria_default" class="form-control" value="35.00" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tasa de Cambio Inicial (Bs. / USD)</label>
                        <input type="number" step="0.01" name="tasa_dolar" class="form-control" value="<?= htmlspecialchars($tasaDolar) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Datos de Pago Móvil</label>
                    <input type="text" name="banco_pago_movil" class="form-control" placeholder="Ej: Banco Banesco | 0134 | V-12345678 | 0414-0000000">
                </div>

                <div class="form-group">
                    <label class="form-label">Cuenta Zelle / Correo</label>
                    <input type="text" name="cuenta_zelle" class="form-control" placeholder="Ej: pagos.condominio@gmail.com (Titular: Junta de Condominio)">
                </div>

                <div style="font-weight: 700; color: var(--primary); margin-top: 1.5rem; margin-bottom: 0.75rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.35rem;">
                    4. Administrador Local del Edificio
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Usuario Administrador *</label>
                        <input type="text" name="admin_username" id="new-condo-admin-user" class="form-control" placeholder="Ej: admin_montesacro" required>
                        <small style="font-size:0.7rem; color:var(--text-muted);">Usuario exclusivo para este edificio</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contraseña de Administrador *</label>
                        <input type="password" name="admin_password" class="form-control" placeholder="Contraseña segura" value="admin123" required>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-create-condo')">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn-submit-create">
                        <i class="bi bi-rocket-takeoff"></i> Crear Condominio y Generar Estructura
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Editar Condominio -->
<div class="modal-overlay" id="modal-edit-condo">
    <div class="modal-content" style="max-width: 650px;">
        <div class="modal-header">
            <div class="modal-title">
                <i class="bi bi-pencil-square" style="color: var(--primary);"></i>
                <span>Editar Condominio y Administrador</span>
            </div>
            <button class="modal-close" onclick="closeModal('modal-edit-condo')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form-edit-condo" onsubmit="SuperApp.handleUpdateCondo(event)">
                <input type="hidden" name="id" id="edit-condo-id">
                
                <div style="font-weight: 700; color: var(--primary); margin-bottom: 0.75rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.35rem;">
                    1. Datos del Inmueble
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre del Condominio</label>
                    <input type="text" name="nombre" id="edit-condo-nombre" class="form-control" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">RIF</label>
                        <input type="text" name="rif" id="edit-condo-rif" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cuota Ordinaria Sugerida (USD)</label>
                        <input type="number" step="0.01" name="monto_cuota_ordinaria_default" id="edit-condo-cuota" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Dirección</label>
                    <input type="text" name="direccion" id="edit-condo-direccion" class="form-control">
                </div>

                <div style="font-weight: 700; color: var(--primary); margin-top: 1.25rem; margin-bottom: 0.75rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.35rem;">
                    2. Cuentas Bancarias
                </div>
                <div class="form-group">
                    <label class="form-label">Datos de Pago Móvil</label>
                    <input type="text" name="banco_pago_movil" id="edit-condo-pm" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Cuenta Bancaria de Transferencia</label>
                    <input type="text" name="banco_transferencia" id="edit-condo-transf" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Cuenta Zelle</label>
                    <input type="text" name="cuenta_zelle" id="edit-condo-zelle" class="form-control">
                </div>

                <div style="font-weight: 700; color: var(--primary); margin-top: 1.25rem; margin-bottom: 0.75rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.35rem;">
                    3. Administrador Asignado a este Edificio
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Usuario Administrador</label>
                        <input type="text" name="admin_username" id="edit-admin-username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nombre del Encargado</label>
                        <input type="text" name="admin_nombre" id="edit-admin-nombre" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Restablecer Contraseña del Administrador (Opcional):</label>
                    <input type="password" name="admin_password" id="edit-admin-password" class="form-control" placeholder="Dejar en blanco para no modificar">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-condo')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/app.js"></script>
<script>
const SuperApp = {
    condos: [],

    init() {
        this.loadCondominios();
        this.updatePreview();
    },

    async loadCondominios() {
        const container = document.getElementById('condominios-container');
        container.innerHTML = `<div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-muted);"><i class="bi bi-arrow-repeat spin"></i> Cargando condominios...</div>`;

        const res = await apiRequest('api/condominios.php?action=list');
        if (!res.success) {
            container.innerHTML = `<div class="card" style="grid-column: 1 / -1; color: var(--danger); text-align: center;">${res.message}</div>`;
            return;
        }

        this.condos = res.condominios;
        this.renderStats(res.condominios);
        this.renderCondos(res.condominios, res.activo_id);
    },

    renderStats(condos) {
        let totalUnidades = 0;
        let totalRevision = 0;
        let totalDeuda = 0;

        condos.forEach(c => {
            totalUnidades += Number(c.total_apartamentos || 0);
            totalRevision += Number(c.pagos_en_revision || 0);
            totalDeuda += Number(c.total_deuda_usd || 0);
        });

        document.getElementById('stat-total-condos').textContent = condos.length;
        document.getElementById('stat-total-unidades').textContent = totalUnidades;
        document.getElementById('stat-total-revision').textContent = totalRevision;
        document.getElementById('stat-total-deuda').textContent = '$' + totalDeuda.toLocaleString('en-US', { minimumFractionDigits: 2 });
    },

    renderCondos(condos, activoId) {
        const container = document.getElementById('condominios-container');
        if (!condos.length) {
            container.innerHTML = `<div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-muted);">No hay condominios registrados. ¡Crea el primero!</div>`;
            return;
        }

        container.innerHTML = condos.map(c => {
            const isActive = c.es_activo_en_sesion;
            const tienePB = c.tiene_pb == 1 ? `PB (${c.aptos_pb_cant || 2}) + ` : '';
            return `
                <div class="condo-card ${isActive ? 'active-session' : ''}">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.75rem;">
                            <div style="font-size: 1.2rem; font-weight: 800; color: #fff;">
                                ${c.nombre}
                            </div>
                            ${isActive ? '<span class="condo-badge" style="background: rgba(59,130,246,0.2); color: #60a5fa; border: 1px solid rgba(59,130,246,0.4);"><i class="bi bi-check-circle-fill"></i> Activo</span>' : ''}
                        </div>

                        <div style="font-size: 0.82rem; color: var(--text-secondary); margin-bottom: 0.85rem; display: flex; flex-direction: column; gap: 0.25rem;">
                            <div><i class="bi bi-geo-alt"></i> ${c.direccion || 'Sin dirección registrada'}</div>
                            ${c.rif ? `<div><i class="bi bi-card-heading"></i> RIF: ${c.rif}</div>` : ''}
                        </div>

                        <!-- Estructura arquitectónica -->
                        <div style="background: var(--bg-primary); padding: 0.65rem 0.85rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color); font-size: 0.82rem; margin-bottom: 1rem;">
                            <span style="color: var(--text-muted);">Estructura:</span>
                            <strong style="color: #fff;">${tienePB}${c.pisos_cant} Pisos</strong> • 
                            <strong style="color: var(--primary);">${c.total_apartamentos} Apartamentos</strong>
                        </div>

                        <!-- Balance rápido -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <div class="stat-mini-box">
                                <div style="font-size: 0.7rem; color: var(--text-muted);">Deuda Pendiente</div>
                                <div style="font-size: 0.95rem; font-weight: 700; color: ${Number(c.total_deuda_usd) > 0 ? 'var(--danger)' : 'var(--success)'};">
                                    ${c.total_deuda_usd_formatted}
                                </div>
                            </div>
                            <div class="stat-mini-box">
                                <div style="font-size: 0.7rem; color: var(--text-muted);">Recaudado</div>
                                <div style="font-size: 0.95rem; font-weight: 700; color: var(--success);">
                                    ${c.total_recaudado_usd_formatted}
                                </div>
                            </div>
                        </div>

                        <div style="background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: var(--radius-sm); padding: 0.5rem 0.75rem; font-size: 0.8rem; margin-top: 0.75rem; display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <i class="bi bi-person-badge" style="color: #60a5fa;"></i> Admin: <strong style="color: #fff;">${c.admin_username || 'Sin admin asignado'}</strong>
                            </div>
                            <span style="font-size: 0.72rem; color: var(--text-muted);">${c.admin_nombre || ''}</span>
                        </div>

                        ${Number(c.pagos_en_revision) > 0 ? `
                            <div style="background: var(--warning-bg); color: var(--warning); padding: 0.35rem 0.65rem; border-radius: var(--radius-sm); font-size: 0.75rem; text-align: center; margin-top: 0.5rem; font-weight: 600;">
                                <i class="bi bi-exclamation-triangle-fill"></i> ${c.pagos_en_revision} pago(s) en espera de auditoría
                            </div>
                        ` : ''}
                    </div>

                    <div class="condo-actions">
                        <button class="btn btn-primary btn-sm" style="flex: 1;" onclick="SuperApp.switchToCondo(${c.id})">
                            <i class="bi bi-box-arrow-in-right"></i> Administrar Edificio
                        </button>
                        <button class="btn btn-secondary btn-sm" title="Editar Información y Admin" onclick="SuperApp.openEditModal(${c.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        ${c.id > 1 ? `
                            <button class="btn btn-danger btn-sm" title="Eliminar Condominio" onclick="SuperApp.deleteCondo(${c.id}, '${c.nombre.replace(/'/g, "\\'")}')">
                                <i class="bi bi-trash"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        }).join('');
    },

    updatePreview() {
        const nombre = document.getElementById('new-condo-nombre').value.trim() || 'Nuevo Condominio';
        const pisos = parseInt(document.getElementById('new-condo-pisos').value) || 1;
        const aptosPiso = parseInt(document.getElementById('new-condo-aptos-piso').value) || 1;
        const nomenclatura = document.getElementById('new-condo-nomenclatura').value;
        const tienePB = document.getElementById('new-condo-pb').checked;
        const pbCant = tienePB ? (parseInt(document.getElementById('new-condo-pb-cant').value) || 0) : 0;
        const tienePH = document.getElementById('new-condo-ph').checked;
        const phCant = tienePH ? (parseInt(document.getElementById('new-condo-ph-cant').value) || 0) : 0;

        document.getElementById('pb-cant-container').style.display = tienePB ? 'block' : 'none';
        document.getElementById('ph-cant-container').style.display = tienePH ? 'block' : 'none';

        const adminInput = document.getElementById('new-condo-admin-user');
        if (adminInput && (!adminInput.value || adminInput.dataset.auto === 'true')) {
            const cleanSlug = nombre.toLowerCase().replace(/[^a-z0-9]/g, '').substring(0, 12);
            adminInput.value = 'admin_' + (cleanSlug || 'condo');
            adminInput.dataset.auto = 'true';
        }

        const totalPisos = pisos * aptosPiso;
        const total = pbCant + totalPisos + phCant;
        const alicuota = total > 0 ? (100 / total).toFixed(2) : '0';

        let ejEjemplo = '';
        if (nomenclatura === 'letras') {
            ejEjemplo = `Pisos 1-${pisos} (ej. 1A...${pisos}${String.fromCharCode(64 + Math.min(aptosPiso, 26))})`;
        } else {
            ejEjemplo = `Pisos 1-${pisos} (ej. 101...${pisos}0${aptosPiso})`;
        }

        let pbStr = tienePB ? `PB (${pbCant} aptos) + ` : '';
        let phStr = tienePH ? ` + PH (${phCant} aptos)` : '';

        const totalUsuarios = total + 1;
        document.getElementById('preview-text').innerHTML = `
            <strong>Resumen de Arquitectura:</strong> Se generarán <strong>${total} apartamentos residenciales</strong> (${pbStr}${ejEjemplo}${phStr}).<br>
            <span style="color: var(--success); font-weight: 600;">Alícuota calculada automáticamente: ~${alicuota}% por apartamento.</span><br>
            <span style="color: var(--primary); font-weight: 600;"><i class="bi bi-people-fill"></i> Usuarios en Supabase: <strong>${totalUsuarios} usuarios listos</strong> (${total} residentes con clave <code>123456</code> + 1 admin con clave <code>admin123</code>).</span>
        `;
    },

    openCreateModal() {
        document.getElementById('form-create-condo').reset();
        const adminInput = document.getElementById('new-condo-admin-user');
        if (adminInput) adminInput.dataset.auto = 'true';
        this.updatePreview();
        openModal('modal-create-condo');
    },

    async handleCreateCondo(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const btn = document.getElementById('btn-submit-create');

        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Generando Condominio y Unidades...';

        const res = await apiRequest('api/condominios.php?action=create', {
            method: 'POST',
            body: formData
        });

        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-rocket-takeoff"></i> Crear Condominio y Generar Estructura';

        if (res.success) {
            showToast(res.message, 'success');
            closeModal('modal-create-condo');
            this.loadCondominios();
        } else {
            showToast(res.message, 'error');
        }
    },

    async switchToCondo(condoId) {
        const res = await apiRequest(`api/condominios.php?action=switch&id=${condoId}`);
        if (res.success) {
            showToast(res.message, 'success');
            setTimeout(() => {
                window.location.href = 'admin_dashboard.php';
            }, 500);
        } else {
            showToast(res.message, 'error');
        }
    },

    async openEditModal(condoId) {
        const res = await apiRequest(`api/condominios.php?action=detail&id=${condoId}`);
        if (res.success) {
            const c = res.condominio;
            document.getElementById('edit-condo-id').value = c.id;
            document.getElementById('edit-condo-nombre').value = c.nombre;
            document.getElementById('edit-condo-rif').value = c.rif || '';
            document.getElementById('edit-condo-direccion').value = c.direccion || '';
            document.getElementById('edit-condo-cuota').value = c.monto_cuota_ordinaria_default || '35.00';
            document.getElementById('edit-condo-pm').value = c.banco_pago_movil || '';
            document.getElementById('edit-condo-transf').value = c.banco_transferencia || '';
            document.getElementById('edit-condo-zelle').value = c.cuenta_zelle || '';

            if (c.admin) {
                document.getElementById('edit-admin-username').value = c.admin.username || '';
                document.getElementById('edit-admin-nombre').value = c.admin.nombre || '';
            } else {
                document.getElementById('edit-admin-username').value = '';
                document.getElementById('edit-admin-nombre').value = '';
            }
            document.getElementById('edit-admin-password').value = '';

            openModal('modal-edit-condo');
        } else {
            showToast(res.message, 'error');
        }
    },

    async handleUpdateCondo(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        const res = await apiRequest('api/condominios.php?action=update', {
            method: 'POST',
            body: formData
        });

        if (res.success) {
            showToast(res.message, 'success');
            closeModal('modal-edit-condo');
            this.loadCondominios();
        } else {
            showToast(res.message, 'error');
        }
    },

    async deleteCondo(condoId, nombre) {
        if (!confirm(`¿Está seguro de eliminar el condominio '${nombre}'?\n\nADVERTENCIA: Esta acción borrará todas sus unidades, cuotas, pagos y registros de manera irreversible.`)) {
            return;
        }

        const formData = new FormData();
        formData.append('id', condoId);

        const res = await apiRequest('api/condominios.php?action=delete', {
            method: 'POST',
            body: formData
        });

        if (res.success) {
            showToast(res.message, 'success');
            this.loadCondominios();
        } else {
            showToast(res.message, 'error');
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    SuperApp.init();
});
</script>

</body>
</html>
