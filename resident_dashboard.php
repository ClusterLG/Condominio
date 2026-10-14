<?php
// resident_dashboard.php
require_once __DIR__ . '/config/helpers.php';
requireResidentAuth();

$pdo = getDBConnection();
$aptoId = (int)$_SESSION['apto_id'];

// Obtener datos completos del apartamento y adjudicatario
$stmtApto = $pdo->prepare("SELECT * FROM apartamentos WHERE id = ?");
$stmtApto->execute([$aptoId]);
$apto = $stmtApto->fetch();

if (!$apto) {
    header("Location: api/auth.php?action=logout");
    exit;
}

$condoId = (int)($apto['condominio_id'] ?? 1);
$condo = getCondominio($condoId);
$edificioNombre = $condo['nombre'] ?? getConfig('nombre_edificio', 'Condominio');
$tasaDolar = getTasaDolar($condoId);
$fechaValorBCV = getConfig('tasa_bcv_fecha_valor', 'Oficial');
$pagoMovilInfo = !empty($condo['banco_pago_movil']) ? $condo['banco_pago_movil'] : getConfig('banco_pago_movil', 'Consultar con administración');
$transferenciaInfo = !empty($condo['banco_transferencia']) ? $condo['banco_transferencia'] : getConfig('banco_transferencia', 'Consultar con administración');
$zelleInfo = !empty($condo['cuenta_zelle']) ? $condo['cuenta_zelle'] : getConfig('cuenta_zelle', 'Consultar con administración');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Residente - Apto <?= htmlspecialchars($apto['codigo']) ?> - <?= htmlspecialchars($edificioNombre) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .bank-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        .bank-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 1.15rem;
            position: relative;
        }
        .bank-card-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .bank-card-desc {
            font-size: 0.82rem;
            color: var(--text-primary);
            line-height: 1.4;
            font-family: 'JetBrains Mono', monospace;
        }
        .copy-btn {
            position: absolute;
            top: 0.85rem;
            right: 0.85rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            font-size: 0.72rem;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transition);
        }
        .copy-btn:hover {
            background: var(--primary-light);
            color: #60a5fa;
            border-color: var(--primary);
        }
    </style>
</head>
<body>

<div class="sidebar-overlay"></div>

<div class="app-container">
    <!-- Sidebar Residente Retráctil -->
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">
                <i class="bi bi-door-closed"></i>
            </div>
            <div>
                <div class="brand-title">Apto <?= htmlspecialchars($apto['codigo']) ?></div>
                <div class="brand-subtitle"><?= htmlspecialchars($edificioNombre) ?></div>
            </div>
        </div>

        <div class="nav-menu">
            <button class="nav-item active" onclick="showResidentSection('cuotas')">
                <i class="bi bi-wallet2"></i>
                <span>Cuotas y Pagos</span>
            </button>
            <button class="nav-item" onclick="showResidentSection('perfil')">
                <i class="bi bi-person-vcard"></i>
                <span>Mi Perfil</span>
            </button>
            <button class="nav-item" onclick="showResidentSection('tickets')">
                <i class="bi bi-chat-heart"></i>
                <span>Quejas y Solicitudes</span>
            </button>
            <button class="nav-item" onclick="showResidentSection('notices')">
                <i class="bi bi-megaphone"></i>
                <span>Cartelera de Avisos</span>
            </button>
        </div>

        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar" style="background:linear-gradient(135deg, var(--primary), #8b5cf6);">
                    <?= htmlspecialchars(substr($apto['codigo'], 0, 2)) ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($apto['propietario_nombre'] ?: 'Residente') ?></div>
                    <div class="user-role"><?= $apto['piso'] == 0 ? 'Planta Baja' : 'Piso ' . $apto['piso'] ?></div>
                </div>
                <a href="api/auth.php?action=logout" class="btn-logout" title="Cerrar sesión">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Área Principal Residente -->
    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" id="sidebar-toggle" class="btn-toggle-sidebar" title="Plegar / Desplegar Menú">
                    <i class="bi bi-list"></i>
                </button>
                <div class="topbar-title">
                    <h1>Portal de Autogestión - Apto <?= htmlspecialchars($apto['codigo']) ?></h1>
                    <p><?= htmlspecialchars($apto['propietario_nombre'] ?: 'Adjudicatario') ?> | Alícuota: <?= $apto['alicuota'] ?>%</p>
                </div>
            </div>

            <div class="topbar-metrics">
                <div class="metric-pill" style="border-color:rgba(16, 185, 129, 0.35);">
                    <i class="bi bi-bank2" style="color: #34d399;"></i>
                    <div>
                        <span class="label">Tasa Oficial BCV</span>
                        <span class="val" style="color:#34d399;">Bs. <?= number_format($tasaDolar, 2, ',', '.') ?></span>
                        <div style="font-size:0.65rem; color:var(--text-muted);"><?= htmlspecialchars($fechaValorBCV) ?></div>
                    </div>
                </div>
                <div class="metric-pill" style="border-color:rgba(239, 68, 68, 0.35);">
                    <i class="bi bi-cash-stack" style="color: #f87171;"></i>
                    <div>
                        <span class="label">Deuda Pendiente (USD)</span>
                        <span class="val" id="res-deuda-usd" style="color:#f87171;">$0.00</span>
                    </div>
                </div>
                <div class="metric-pill" style="border-color:rgba(245, 158, 11, 0.35);">
                    <i class="bi bi-wallet" style="color: #fbbf24;"></i>
                    <div>
                        <span class="label">Monto en Bolívares (BCV)</span>
                        <span class="val" id="res-deuda-bs" style="color:#fbbf24;">Bs. 0,00</span>
                    </div>
                </div>
            </div>
        </header>

        <div class="view-container">

            <!-- Coordenadas Bancarias para Pagos -->
            <div class="card" style="padding: 1.25rem;">
                <div class="card-header" style="margin-bottom:0.75rem; padding-bottom:0.5rem;">
                    <div class="card-title" style="font-size:1rem;"><i class="bi bi-bank2"></i> Cuentas Receptoras del Condominio</div>
                    <div class="card-subtitle">Realice su transferencia y presione "Reportar Pago"</div>
                </div>
                <div class="bank-info-grid">
                    <div class="bank-card">
                        <div class="bank-card-title"><i class="bi bi-phone"></i> Pago Móvil</div>
                        <div class="bank-card-desc" id="info-pago-movil"><?= nl2br(htmlspecialchars($pagoMovilInfo)) ?></div>
                        <button class="copy-btn" onclick="copyText('info-pago-movil')"><i class="bi bi-clipboard"></i> Copiar</button>
                    </div>
                    <div class="bank-card">
                        <div class="bank-card-title"><i class="bi bi-arrow-left-right"></i> Transferencia Bs</div>
                        <div class="bank-card-desc" id="info-transferencia"><?= nl2br(htmlspecialchars($transferenciaInfo)) ?></div>
                        <button class="copy-btn" onclick="copyText('info-transferencia')"><i class="bi bi-clipboard"></i> Copiar</button>
                    </div>
                    <div class="bank-card">
                        <div class="bank-card-title"><i class="bi bi-currency-dollar"></i> Zelle / Divisas</div>
                        <div class="bank-card-desc" id="info-zelle"><?= nl2br(htmlspecialchars($zelleInfo)) ?></div>
                        <button class="copy-btn" onclick="copyText('info-zelle')"><i class="bi bi-clipboard"></i> Copiar</button>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 1: CUOTAS Y PAGOS -->
            <div id="res-section-cuotas">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title"><i class="bi bi-receipt"></i> Mis Cuotas Pendientes por Pagar</div>
                            <div class="card-subtitle">Calculadas automáticamente a la tasa oficial del BCV</div>
                        </div>
                        <button class="btn btn-primary" onclick="openModal('modal-reportar-pago')">
                            <i class="bi bi-wallet2"></i> Reportar Nuevo Pago
                        </button>
                    </div>
                    <div class="table-responsive" id="resident-cuotas-container">
                        <!-- Generado dinámicamente -->
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title"><i class="bi bi-clock-history"></i> Historial de Pagos Reportados</div>
                            <div class="card-subtitle">Comprobantes enviados y estatus de auditoría por parte del administrador</div>
                        </div>
                    </div>
                    <div class="table-responsive" id="resident-history-container">
                        <!-- Generado dinámicamente -->
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: PERFIL DEL ADJUDICATARIO -->
            <div id="res-section-perfil" style="display:none;">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title"><i class="bi bi-person-vcard"></i> Perfil del Adjudicatario y Residente</div>
                            <div class="card-subtitle">Mantenga sus datos actualizados para contacto, censos del edificio, seguridad y emergencias</div>
                        </div>
                    </div>
                    <form id="form-resident-profile" onsubmit="handleSaveProfile(event)">
                        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:1rem;">
                            <div class="form-group">
                                <label class="form-label">Nombre Completo del Adjudicatario / Responsable:</label>
                                <input type="text" name="propietario_nombre" class="form-control" value="<?= htmlspecialchars($apto['propietario_nombre'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Cédula de Identidad / RIF:</label>
                                <input type="text" name="cedula_identidad" class="form-control" placeholder="Ej: V-12.345.678" value="<?= htmlspecialchars($apto['cedula_identidad'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Condición en el Inmueble:</label>
                                <select name="condicion_ocupacion" class="form-control">
                                    <option value="propietario" <?= ($apto['condicion_ocupacion'] ?? '') === 'propietario' ? 'selected' : '' ?>>Propietario</option>
                                    <option value="arrendatario" <?= ($apto['condicion_ocupacion'] ?? '') === 'arrendatario' ? 'selected' : '' ?>>Arrendatario</option>
                                    <option value="residente" <?= ($apto['condicion_ocupacion'] ?? '') === 'residente' ? 'selected' : '' ?>>Residente / Familiar</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Teléfono Principal:</label>
                                <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($apto['telefono'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Teléfono Secundario / WhatsApp:</label>
                                <input type="text" name="telefono_secundario" class="form-control" value="<?= htmlspecialchars($apto['telefono_secundario'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Correo Electrónico:</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($apto['email'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Número de Habitantes en el Apto:</label>
                                <input type="number" min="1" max="20" name="habitantes_cant" class="form-control" value="<?= (int)($apto['habitantes_cant'] ?? 1) ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Vehículos y Puestos de Estacionamiento:</label>
                            <input type="text" name="vehiculos_info" class="form-control" placeholder="Ej: Marca, Modelo, Color, Placa y Puesto asignado" value="<?= htmlspecialchars($apto['vehiculos_info'] ?? '') ?>">
                        </div>

                        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:1rem;">
                            <div class="form-group">
                                <label class="form-label">Mascotas en el Apartamento:</label>
                                <input type="text" name="mascotas_info" class="form-control" placeholder="Ej: 1 Perro pequeño, Ninguna" value="<?= htmlspecialchars($apto['mascotas_info'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Contacto de Emergencia:</label>
                                <input type="text" name="contacto_emergencia" class="form-control" placeholder="Nombre, parentesco y número telefónico" value="<?= htmlspecialchars($apto['contacto_emergencia'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Cambiar Contraseña de Acceso (Opcional):</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Dejar en blanco si desea conservar su clave actual">
                        </div>

                        <button type="submit" class="btn btn-primary" id="btn-save-profile">
                            <i class="bi bi-save"></i> Guardar y Actualizar Mis Datos
                        </button>
                    </form>
                </div>
            </div>

            <!-- SECCIÓN 3: QUEJAS, SOLICITUDES Y NOVEDADES -->
            <div id="res-section-tickets" style="display:none;">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title"><i class="bi bi-chat-left-dots"></i> Buzón de Quejas, Solicitudes y Novedades</div>
                            <div class="card-subtitle">Comuníquese directamente con la administración y reciba respuestas oficiales</div>
                        </div>
                        <button class="btn btn-primary" onclick="openModal('modal-nuevo-ticket')">
                            <i class="bi bi-plus-lg"></i> Redactar Solicitud / Queja
                        </button>
                    </div>
                    <div id="resident-tickets-container">
                        <!-- Generado dinámicamente -->
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 4: CARTELERA DE AVISOS -->
            <div id="res-section-notices" style="display:none;">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="bi bi-megaphone"></i> Cartelera Digital del Edificio</div>
                        <div class="card-subtitle">Comunicados oficiales de la Junta de Condominio</div>
                    </div>
                    <div id="resident-notices-container">
                        <!-- Generado dinámicamente -->
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- ========================================== -->
<!-- MODALES DEL RESIDENTE                      -->
<!-- ========================================== -->

<!-- Modal: Reportar Pago -->
<div class="modal-overlay" id="modal-reportar-pago">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title"><i class="bi bi-wallet2"></i> Cargar Reporte de Pago</div>
            <button class="btn-close-modal" onclick="closeModal('modal-reportar-pago')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form id="form-reportar-pago" onsubmit="handleReportPago(event)">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Cuota a la cual aplica el pago:</label>
                    <select name="cuota_apartamento_id" id="modal-report-cuota-select" class="form-control" required onchange="onCuotaSelectChange(this)">
                        <option value="">-- Cargando cuotas pendientes... --</option>
                    </select>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Método de Pago:</label>
                        <select name="metodo_pago" class="form-control" required>
                            <option value="pago_movil">Pago Móvil</option>
                            <option value="transferencia_bs">Transferencia Bancaria (Bs)</option>
                            <option value="zelle">Zelle (USD)</option>
                            <option value="efectivo_usd">Efectivo Divisas</option>
                            <option value="transferencia_usd">Transferencia Internacional USD</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Moneda en que realizó el pago:</label>
                        <select name="moneda" id="report-moneda" class="form-control" onchange="onMonedaChange(this)">
                            <option value="USD">Dólares (USD $)</option>
                            <option value="BS">Bolívares (Bs. BCV)</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Monto Pagado:</label>
                        <input type="number" step="0.01" name="monto_pagado" id="report-monto" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Número de Referencia:</label>
                        <input type="text" name="referencia" class="form-control" placeholder="Últimos dígitos o confirmación" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Fecha del Pago:</label>
                    <input type="date" name="fecha_pago" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="bi bi-paperclip"></i> Captura o Comprobante de Pago (Imagen o PDF):</label>
                    <input type="file" name="comprobante" class="form-control" accept="image/*,application/pdf">
                    <small style="color:var(--text-muted);">Adjunte la captura para agilizar la verificación del administrador.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Observaciones adicionales (Opcional):</label>
                    <textarea name="notas" class="form-control" rows="2" placeholder="Ej: Pago realizado desde cuenta de banco Provincial..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-reportar-pago')">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btn-submit-pago"><i class="bi bi-cloud-arrow-up"></i> Enviar Reporte de Pago</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Nuevo Ticket -->
<div class="modal-overlay" id="modal-nuevo-ticket">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title"><i class="bi bi-chat-heart"></i> Notificar Queja, Solicitud o Novedad</div>
            <button class="btn-close-modal" onclick="closeModal('modal-nuevo-ticket')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form id="form-nuevo-ticket" onsubmit="handleCreateTicket(event)">
            <div class="modal-body">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Tipo de Notificación:</label>
                        <select name="tipo" class="form-control" required>
                            <option value="solicitud">Solicitud de Servicio / Permiso</option>
                            <option value="queja">Queja o Reclamo</option>
                            <option value="novedad_averia">Novedad / Avería en Áreas Comunes</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nivel de Urgencia:</label>
                        <select name="prioridad" class="form-control">
                            <option value="baja">Baja</option>
                            <option value="media" selected>Media</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Asunto / Título:</label>
                    <input type="text" name="titulo" class="form-control" placeholder="Ej: Ruido molesto en pasillo, Fuga de agua, Llave magnética" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Detalle de la situación:</label>
                    <textarea name="descripcion" class="form-control" rows="4" placeholder="Describa ampliamente los hechos o lo solicitado..." required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="bi bi-camera"></i> Foto o Evidencia Adjunta (Opcional):</label>
                    <input type="file" name="foto" class="form-control" accept="image/*,application/pdf">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-nuevo-ticket')">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btn-submit-ticket"><i class="bi bi-send-check"></i> Enviar a Administración</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/app.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    ResidentApp.init();
});

function showResidentSection(section) {
    document.getElementById('res-section-cuotas').style.display = section === 'cuotas' ? 'block' : 'none';
    document.getElementById('res-section-perfil').style.display = section === 'perfil' ? 'block' : 'none';
    document.getElementById('res-section-tickets').style.display = section === 'tickets' ? 'block' : 'none';
    document.getElementById('res-section-notices').style.display = section === 'notices' ? 'block' : 'none';

    document.querySelectorAll('.sidebar .nav-item').forEach(b => b.classList.remove('active'));
    event.currentTarget.classList.add('active');
}

function onCuotaSelectChange(select) {
    const selected = select.options[select.selectedIndex];
    const usd = parseFloat(selected.dataset.usd || 0);
    const moneda = document.getElementById('report-moneda').value;
    const montoInput = document.getElementById('report-monto');

    if (usd > 0) {
        if (moneda === 'USD') {
            montoInput.value = usd.toFixed(2);
        } else {
            montoInput.value = (usd * ResidentApp.tasa).toFixed(2);
        }
    }
}

function onMonedaChange(select) {
    const cuotaSelect = document.getElementById('modal-report-cuota-select');
    onCuotaSelectChange(cuotaSelect);
}

async function handleSaveProfile(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-profile');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Guardando...';

    const formData = new FormData(e.target);
    const res = await apiRequest('api/apartamentos.php?action=resident_update_profile', {
        method: 'POST',
        body: formData
    });

    if (res.success) {
        showToast(res.message, 'success');
    } else {
        showToast(res.message, 'error');
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-save"></i> Guardar y Actualizar Mis Datos';
}

async function handleReportPago(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit-pago');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Enviando...';

    const formData = new FormData(e.target);
    const res = await apiRequest('api/pagos.php?action=report', {
        method: 'POST',
        body: formData
    });

    if (res.success) {
        showToast(res.message, 'success');
        closeModal('modal-reportar-pago');
        e.target.reset();
        ResidentApp.loadPendingCuotas();
        ResidentApp.loadHistory();
    } else {
        showToast(res.message, 'error');
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-cloud-arrow-up"></i> Enviar Reporte de Pago';
}

async function handleCreateTicket(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit-ticket');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Enviando...';

    const formData = new FormData(e.target);
    const res = await apiRequest('api/tickets.php?action=create', {
        method: 'POST',
        body: formData
    });

    if (res.success) {
        showToast(res.message, 'success');
        closeModal('modal-nuevo-ticket');
        e.target.reset();
        ResidentApp.loadTickets();
    } else {
        showToast(res.message, 'error');
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-send-check"></i> Enviar a Administración';
}

function copyText(elementId) {
    const text = document.getElementById(elementId).innerText;
    navigator.clipboard.writeText(text).then(() => {
        showToast('Datos bancarios copiados al portapapeles', 'info');
    });
}
</script>

</body>
</html>
