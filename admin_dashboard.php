<?php
// admin_dashboard.php
require_once __DIR__ . '/config/helpers.php';
requireAdminAuth();

$pdo = getDBConnection();
$condominio = getCondominio();
$edificioNombre = $condominio['nombre'] ?? getConfig('nombre_edificio', 'Condominio');
$pisosCant = $condominio['pisos_cant'] ?? 12;
$tienePB = !empty($condominio['tiene_pb']);
$aptosPiso = $condominio['aptos_por_piso'] ?? 4;
$tasaDolar = getTasaDolar($condominio['id'] ?? null);
$fechaValorBCV = getConfig('tasa_bcv_fecha_valor', 'Oficial');
$ultimaSyncBCV = getConfig('tasa_bcv_ultima_actualizacion', 'Hoy');
$isSuperAdmin = isSuperAdmin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - <?= htmlspecialchars($edificioNombre) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="sidebar-overlay"></div>

<div class="app-container">
    <!-- Barra Lateral de Navegación Retráctil -->
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">
                <i class="bi bi-buildings"></i>
            </div>
            <div>
                <div class="brand-title">CondoAdmin</div>
                <div class="brand-subtitle"><?= htmlspecialchars($edificioNombre) ?></div>
            </div>
        </div>

        <div class="nav-menu">
            <?php if ($isSuperAdmin): ?>
            <a href="superadmin_dashboard.php" class="nav-item" style="color: #c084fc; background: rgba(139, 92, 246, 0.12); border: 1px dashed rgba(139, 92, 246, 0.35); text-decoration: none; margin-bottom: 0.5rem;">
                <i class="bi bi-buildings"></i>
                <span>🏢 Super Admin</span>
            </a>
            <?php endif; ?>
            <button class="nav-item active" data-view="pisos">
                <i class="bi bi-building"></i>
                <span>Pisos y Aptos</span>
            </button>
            <button class="nav-item" data-view="cuotas">
                <i class="bi bi-cash-stack"></i>
                <span>Emisión de Cuotas</span>
            </button>
            <button class="nav-item" data-view="pagos">
                <i class="bi bi-wallet2"></i>
                <span>Auditoría de Pagos</span>
                <span class="nav-badge" id="badge-pagos-pendientes" style="display:none;">0</span>
            </button>
            <button class="nav-item" data-view="tickets">
                <i class="bi bi-chat-left-dots"></i>
                <span>Quejas y Solicitudes</span>
            </button>
            <button class="nav-item" data-view="comunicados">
                <i class="bi bi-megaphone"></i>
                <span>Cartelera Digital</span>
            </button>
            <button class="nav-item" data-view="config">
                <i class="bi bi-gear"></i>
                <span>Tasa y Cuentas</span>
            </button>
        </div>

        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar"><?= $isSuperAdmin ? 'SA' : 'AD' ?></div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Administrador') ?></div>
                    <div class="user-role"><?= $isSuperAdmin ? 'Super Administrador' : 'Administrador Local' ?></div>
                </div>
                <a href="api/auth.php?action=logout" class="btn-logout" title="Cerrar sesión">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Contenido Principal -->
    <main class="main-content">
        <!-- Topbar con Botón de Menú Retráctil y Métricas Oficiales BCV -->
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" id="sidebar-toggle" class="btn-toggle-sidebar" title="Plegar / Desplegar Menú">
                    <i class="bi bi-list"></i>
                </button>
                <div class="topbar-title">
                    <h1><?= htmlspecialchars($edificioNombre) ?></h1>
                    <p><?= ($tienePB ? 'PB + ' : '') . $pisosCant ?> Pisos | Administración Central</p>
                </div>
            </div>

            <div class="topbar-metrics">
                <?php if ($isSuperAdmin): 
                    $todosCondos = $pdo->query("SELECT id, nombre FROM condominios ORDER BY nombre ASC")->fetchAll();
                ?>
                <div style="display: flex; align-items: center; gap: 0.4rem; background: var(--bg-card); padding: 0.35rem 0.65rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    <i class="bi bi-buildings" style="color: #c084fc;"></i>
                    <select class="form-control" style="font-size: 0.78rem; padding: 0.25rem 0.5rem; background: transparent; border: none; color: #fff; cursor: pointer;" onchange="AdminApp.switchCondominio(this.value)">
                        <?php foreach ($todosCondos as $tc): ?>
                            <option value="<?= $tc['id'] ?>" <?= $tc['id'] == ($condominio['id'] ?? 1) ? 'selected' : '' ?> style="background: var(--bg-card); color: #fff;">
                                <?= htmlspecialchars($tc['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="metric-pill" style="border-color: rgba(16, 185, 129, 0.35);">
                    <i class="bi bi-bank2" style="color: #34d399;"></i>
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <span class="label">Tasa Oficial BCV</span>
                            <button class="btn-sync-bcv" onclick="AdminApp.syncBCV()" title="Actualizar desde el Banco Central de Venezuela">
                                <i class="bi bi-arrow-repeat"></i> Actualizar BCV
                            </button>
                        </div>
                        <span class="val val-tasa" style="color: #34d399;">Bs. <?= number_format($tasaDolar, 2, ',', '.') ?></span>
                        <div style="font-size:0.65rem; color:var(--text-muted);" id="metric-bcv-fecha"><?= htmlspecialchars($fechaValorBCV) ?></div>
                    </div>
                </div>

                <div class="metric-pill">
                    <i class="bi bi-exclamation-triangle" style="color: #f87171;"></i>
                    <div>
                        <span class="label">Deuda Total Edificio</span>
                        <span class="val" id="metric-total-deuda" style="color:#f87171;">$0.00</span>
                    </div>
                </div>

                <div class="metric-pill">
                    <i class="bi bi-clock-history" style="color: #fbbf24;"></i>
                    <div>
                        <span class="label">Pagos por Auditar</span>
                        <span class="val" id="metric-pagos-revision" style="color:#fbbf24;">0</span>
                    </div>
                </div>

                <div class="metric-pill">
                    <i class="bi bi-check-circle" style="color: #60a5fa;"></i>
                    <div>
                        <span class="label">Aptos Solventes</span>
                        <span class="val" id="metric-aptos-solventes">0</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Contenedor de Vistas -->
        <div class="view-container">

            <!-- Botones Disgregados de Necesidades en Cabecera -->
            <div class="dash-actions-grid">
                <div class="dash-btn-card active" data-view="pisos">
                    <div class="dash-btn-icon" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="dash-btn-title">Control Pisos y Aptos</div>
                    <div class="dash-btn-desc">Fichas de adjudicatarios y estado</div>
                </div>

                <div class="dash-btn-card" data-view="cuotas">
                    <div class="dash-btn-icon" style="background: rgba(139, 92, 246, 0.15); color: #8b5cf6;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="dash-btn-title">Emisión de Cuotas</div>
                    <div class="dash-btn-desc">Ordinarias y Extraordinarias</div>
                </div>

                <div class="dash-btn-card" data-view="pagos">
                    <div class="dash-btn-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="dash-btn-title">Auditoría de Pagos</div>
                    <div class="dash-btn-desc">Aprobar y auditar transferencias</div>
                </div>

                <div class="dash-btn-card" data-view="tickets">
                    <div class="dash-btn-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                        <i class="bi bi-chat-left-dots"></i>
                    </div>
                    <div class="dash-btn-title">Quejas y Solicitudes</div>
                    <div class="dash-btn-desc">Buzón de averías y residentes</div>
                </div>

                <div class="dash-btn-card" data-view="comunicados">
                    <div class="dash-btn-icon" style="background: rgba(6, 182, 212, 0.15); color: #06b6d4;">
                        <i class="bi bi-megaphone"></i>
                    </div>
                    <div class="dash-btn-title">Cartelera Digital</div>
                    <div class="dash-btn-desc">Publicar avisos a la comunidad</div>
                </div>

                <div class="dash-btn-card" data-view="config">
                    <div class="dash-btn-icon" style="background: rgba(100, 116, 139, 0.15); color: #94a3b8;">
                        <i class="bi bi-gear"></i>
                    </div>
                    <div class="dash-btn-title">Tasa y Cuentas</div>
                    <div class="dash-btn-desc">Sincronización BCV y bancos</div>
                </div>
            </div>

            <!-- SECCIÓN 1: PISOS Y APARTAMENTOS (ACTIVA POR DEFECTO) -->
            <section class="section-view active" id="view-pisos">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title"><i class="bi bi-building"></i> Mapa General por Pisos y Apartamentos</div>
                            <div class="card-subtitle">Haga clic sobre cualquier apartamento para desplegar la ventana emergente con todos sus datos específicos, último pago y quejas</div>
                        </div>
                        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                            <div class="status-filter-pills" id="pisos-status-filters">
                                <button type="button" class="filter-pill-btn active" data-filter="todos" onclick="AdminApp.setFilterPisos('todos')">
                                    <i class="bi bi-grid-fill"></i> Todos (<span id="count-filter-todos">0</span>)
                                </button>
                                <button type="button" class="filter-pill-btn btn-pill-solvente" data-filter="solvente" onclick="AdminApp.setFilterPisos('solvente')">
                                    <i class="bi bi-check-circle-fill"></i> Solvente (<span id="count-filter-solvente">0</span>)
                                </button>
                                <button type="button" class="filter-pill-btn btn-pill-revision" data-filter="en_revision" onclick="AdminApp.setFilterPisos('en_revision')">
                                    <i class="bi bi-hourglass-split"></i> En Revisión (<span id="count-filter-revision">0</span>)
                                </button>
                                <button type="button" class="filter-pill-btn btn-pill-deuda" data-filter="deuda" onclick="AdminApp.setFilterPisos('deuda')">
                                    <i class="bi bi-exclamation-circle-fill"></i> Deudor (<span id="count-filter-deuda">0</span>)
                                </button>
                            </div>
                            <div style="display: flex; gap: 0.4rem;">
                                <button type="button" class="btn btn-sm btn-primary" onclick="openModal('modal-add-unit')">
                                    <i class="bi bi-plus-lg"></i> Agregar Apto
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="openModal('modal-structure-condo')" title="Reconfigurar Pisos y Apartamentos">
                                    <i class="bi bi-sliders"></i> Estructura
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="floor-container" id="matrix-pisos-container">
                        <!-- Generado dinámicamente -->
                    </div>
                </div>
            </section>

            <!-- SECCIÓN 2: EMISIÓN DE CUOTAS -->
            <section class="section-view" id="view-cuotas">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title"><i class="bi bi-cash-coin"></i> Gestión de Cuotas Ordinarias y Extraordinarias</div>
                            <div class="card-subtitle">Cree cuotas en USD (convertibles automáticamente a Bs a la tasa oficial BCV)</div>
                        </div>
                        <button class="btn btn-primary" onclick="openModal('modal-crear-cuota')">
                            <i class="bi bi-plus-lg"></i> Emitir Nueva Cuota
                        </button>
                    </div>
                    <div class="table-responsive" id="cuotas-table-container">
                        <!-- Generado dinámicamente -->
                    </div>
                </div>
            </section>

            <!-- SECCIÓN 3: AUDITORÍA DE PAGOS -->
            <section class="section-view" id="view-pagos">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title"><i class="bi bi-wallet2"></i> Bandeja de Pagos Reportados por Apartamento</div>
                            <div class="card-subtitle">Verifique comprobantes de Pago Móvil, Zelle o transferencias antes de aprobar la solvencia</div>
                        </div>
                        <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
                            <button class="btn btn-secondary btn-sm" onclick="AdminApp.loadPagos('todos')">Todos</button>
                            <button class="btn btn-secondary btn-sm" onclick="AdminApp.loadPagos('pendiente')">Pendientes</button>
                            <button class="btn btn-secondary btn-sm" onclick="AdminApp.loadPagos('aprobado')">Aprobados</button>
                            <button class="btn btn-secondary btn-sm" onclick="AdminApp.loadPagos('rechazado')">Rechazados</button>
                        </div>
                    </div>
                    <div class="table-responsive" id="pagos-table-container">
                        <!-- Generado dinámicamente -->
                    </div>
                </div>
            </section>

            <!-- SECCIÓN 4: QUEJAS Y SOLICITUDES -->
            <section class="section-view" id="view-tickets">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title"><i class="bi bi-chat-left-dots"></i> Atención a Residentes: Quejas, Solicitudes y Averías</div>
                            <div class="card-subtitle">Seguimiento de requerimientos enviados desde las cuentas de cada apartamento</div>
                        </div>
                        <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
                            <button class="btn btn-secondary btn-sm" onclick="AdminApp.loadTickets('todos', 'todos')">Ver Todos</button>
                            <button class="btn btn-secondary btn-sm" onclick="AdminApp.loadTickets('queja')">Quejas</button>
                            <button class="btn btn-secondary btn-sm" onclick="AdminApp.loadTickets('solicitud')">Solicitudes</button>
                            <button class="btn btn-secondary btn-sm" onclick="AdminApp.loadTickets('novedad_averia')">Averías</button>
                        </div>
                    </div>
                    <div id="tickets-container">
                        <!-- Generado dinámicamente -->
                    </div>
                </div>
            </section>

            <!-- SECCIÓN 5: CARTELERA INFORMATIVA -->
            <section class="section-view" id="view-comunicados">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title"><i class="bi bi-megaphone"></i> Cartelera Informativa Digital</div>
                            <div class="card-subtitle">Publique circulares y avisos oficiales visibles para todos los propietarios</div>
                        </div>
                        <button class="btn btn-primary" onclick="openModal('modal-nuevo-comunicado')">
                            <i class="bi bi-plus-lg"></i> Publicar Comunicado
                        </button>
                    </div>
                    <div id="comunicados-container">
                        <!-- Generado dinámicamente -->
                    </div>
                </div>
            </section>

            <!-- SECCIÓN 6: TASA Y CUENTAS BANCARIAS -->
            <section class="section-view" id="view-config">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="bi bi-gear"></i> Tasa BCV y Ajustes Bancarios Receptoras</div>
                        <button class="btn btn-success btn-sm" onclick="AdminApp.syncBCV()">
                            <i class="bi bi-arrow-repeat"></i> Forzar Sincronización con bcv.org.ve
                        </button>
                    </div>
                    <form id="form-config" onsubmit="handleConfigUpdate(event)">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                            <div class="form-group">
                                <label class="form-label">Tasa de Cambio del Dólar (USD a Bs Oficial BCV):</label>
                                <input type="number" step="0.01" name="tasa_dolar" id="cfg-tasa-dolar" class="form-control" required>
                                <small style="color:var(--text-muted)">Se actualiza automáticamente desde www.bcv.org.ve o manualmente.</small>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nombre Oficial del Edificio / Residencias:</label>
                                <input type="text" name="nombre_edificio" id="cfg-nombre-edificio" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Datos de Pago Móvil del Condominio:</label>
                            <input type="text" name="banco_pago_movil" id="cfg-pago-movil" class="form-control" placeholder="Ej: Banesco 0134 | V-12345678 | 0414-0000000">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Datos de Transferencia Bancaria (Bolívares):</label>
                            <input type="text" name="banco_transferencia" id="cfg-transferencia" class="form-control" placeholder="Banco, Número de Cuenta de 20 dígitos y Titular / RIF">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Cuenta Zelle u otra cuenta en Divisas:</label>
                            <input type="text" name="cuenta_zelle" id="cfg-zelle" class="form-control" placeholder="Correo electrónico o teléfono Zelle">
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-top:0.5rem;">
                            <i class="bi bi-check2-circle"></i> Guardar Configuraciones
                        </button>
                    </form>
                </div>
            </section>

        </div>
    </main>
</div>

<!-- ========================================== -->
<!-- VENTANA EMERGENTE ESPECÍFICA DE APARTAMENTO -->
<!-- ========================================== -->
<div class="modal-overlay" id="modal-apto-detail">
    <div class="modal-container modal-lg">
        <div class="modal-header">
            <div class="modal-title">
                <i class="bi bi-door-open-fill" style="color:var(--primary);"></i> 
                <span>Ficha Apartamento <strong id="detail-codigo" style="font-family:'JetBrains Mono'; color:#60a5fa;"></strong></span>
                <span class="badge" id="detail-tipo" style="margin-left:0.5rem; background:rgba(255,255,255,0.1);"></span>
            </div>
            <button class="btn-close-modal" onclick="closeModal('modal-apto-detail')"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            
            <!-- Resumen de Solvencia y Deuda -->
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:0.85rem; margin-bottom:1.25rem;">
                <div class="metric-pill" style="border-color:rgba(239, 68, 68, 0.4);">
                    <i class="bi bi-cash-stack" style="color:var(--danger);"></i>
                    <div>
                        <span class="label">Deuda Total Pendiente</span>
                        <span class="val" id="detail-deuda-usd" style="color:var(--danger); font-size:1.05rem;">-</span>
                        <div style="font-size:0.7rem; color:var(--text-muted);" id="detail-deuda-bs">-</div>
                    </div>
                </div>
                <div class="metric-pill">
                    <i class="bi bi-pie-chart" style="color:var(--purple);"></i>
                    <div>
                        <span class="label">Alícuota Inmueble</span>
                        <span class="val" id="detail-alicuota">2.00%</span>
                    </div>
                </div>
            </div>

            <!-- Recuadro Destacado de Último Pago Realizado -->
            <div id="detail-ultimo-pago-box" style="margin-bottom:1.25rem;">
                <!-- Rellenado dinámicamente -->
            </div>

            <!-- Navegación por Pestañas del Modal -->
            <div style="display:flex; gap:0.4rem; margin-bottom:1rem; border-bottom:1px solid var(--border-color); padding-bottom:0.5rem; flex-wrap:wrap;">
                <button class="btn btn-secondary btn-sm" id="btn-tab-resumen" onclick="showModalTab('resumen')">
                    <i class="bi bi-person-lines-fill"></i> Datos del Adjudicatario
                </button>
                <button class="btn btn-secondary btn-sm" id="btn-tab-tickets" onclick="showModalTab('tickets')">
                    <i class="bi bi-chat-left-dots"></i> Quejas y Solicitudes
                </button>
                <button class="btn btn-secondary btn-sm" id="btn-tab-cuotas" onclick="showModalTab('cuotas')">
                    <i class="bi bi-receipt"></i> Historial de Cuotas
                </button>
                <button class="btn btn-secondary btn-sm" id="btn-tab-editar" onclick="showModalTab('editar')">
                    <i class="bi bi-pencil-square"></i> Editar Datos
                </button>
            </div>

            <!-- Tab 1: Datos del Adjudicatario / Inmueble -->
            <div id="modal-tab-resumen">
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:0.85rem;">
                    <div style="background:var(--bg-card); padding:0.85rem; border-radius:8px; border:1px solid var(--border-color);">
                        <span style="font-size:0.72rem; color:var(--text-muted); display:block;">Adjudicatario / Titular:</span>
                        <strong style="color:#fff; font-size:0.95rem;" id="detail-nombre">-</strong>
                    </div>
                    <div style="background:var(--bg-card); padding:0.85rem; border-radius:8px; border:1px solid var(--border-color);">
                        <span style="font-size:0.72rem; color:var(--text-muted); display:block;">Cédula de Identidad / RIF:</span>
                        <strong style="color:var(--primary); font-family:'JetBrains Mono';" id="detail-cedula">-</strong>
                    </div>
                    <div style="background:var(--bg-card); padding:0.85rem; border-radius:8px; border:1px solid var(--border-color);">
                        <span style="font-size:0.72rem; color:var(--text-muted); display:block;">Condición de Ocupación:</span>
                        <strong style="color:#34d399;" id="detail-condicion">-</strong>
                    </div>
                    <div style="background:var(--bg-card); padding:0.85rem; border-radius:8px; border:1px solid var(--border-color);">
                        <span style="font-size:0.72rem; color:var(--text-muted); display:block;">Teléfono Principal:</span>
                        <strong style="color:#fff;" id="detail-telefono">-</strong>
                    </div>
                    <div style="background:var(--bg-card); padding:0.85rem; border-radius:8px; border:1px solid var(--border-color);">
                        <span style="font-size:0.72rem; color:var(--text-muted); display:block;">Teléfono Secundario / WhatsApp:</span>
                        <strong style="color:#fff;" id="detail-telefono-sec">-</strong>
                    </div>
                    <div style="background:var(--bg-card); padding:0.85rem; border-radius:8px; border:1px solid var(--border-color);">
                        <span style="font-size:0.72rem; color:var(--text-muted); display:block;">Correo Electrónico:</span>
                        <strong style="color:#fff; font-size:0.85rem;" id="detail-email">-</strong>
                    </div>
                    <div style="background:var(--bg-card); padding:0.85rem; border-radius:8px; border:1px solid var(--border-color);">
                        <span style="font-size:0.72rem; color:var(--text-muted); display:block;">Habitantes en el Inmueble:</span>
                        <strong style="color:#fff;" id="detail-habitantes">-</strong>
                    </div>
                    <div style="background:var(--bg-card); padding:0.85rem; border-radius:8px; border:1px solid var(--border-color);">
                        <span style="font-size:0.72rem; color:var(--text-muted); display:block;">Vehículos y Puestos de Estacionamiento:</span>
                        <span style="color:#fff; font-size:0.85rem;" id="detail-vehiculos">-</span>
                    </div>
                    <div style="background:var(--bg-card); padding:0.85rem; border-radius:8px; border:1px solid var(--border-color);">
                        <span style="font-size:0.72rem; color:var(--text-muted); display:block;">Mascotas:</span>
                        <span style="color:#fff; font-size:0.85rem;" id="detail-mascotas">-</span>
                    </div>
                    <div style="background:var(--bg-card); padding:0.85rem; border-radius:8px; border:1px solid var(--border-color);">
                        <span style="font-size:0.72rem; color:var(--text-muted); display:block;">Contacto de Emergencia:</span>
                        <span style="color:#fbbf24; font-size:0.85rem;" id="detail-emergencia">-</span>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Quejas y Solicitudes del Apartamento -->
            <div id="modal-tab-tickets" style="display:none;">
                <div id="detail-tickets-feed">
                    <!-- Rellenado dinámicamente -->
                </div>
            </div>

            <!-- Tab 3: Cuotas Asignadas -->
            <div id="modal-tab-cuotas" style="display:none;">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Cuota</th>
                                <th>Tipo</th>
                                <th>Monto</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-apto-cuotas"></tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 4: Formulario de Edición Completo -->
            <div id="modal-tab-editar" style="display:none;">
                <form id="form-edit-apto" onsubmit="handleUpdateApto(event)">
                    <input type="hidden" name="id" id="edit-apto-id">
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:0.85rem;">
                        <div class="form-group">
                            <label class="form-label">Nombre del Adjudicatario:</label>
                            <input type="text" name="propietario_nombre" id="edit-apto-nombre" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Cédula de Identidad / RIF:</label>
                            <input type="text" name="cedula_identidad" id="edit-apto-cedula" class="form-control" placeholder="Ej: V-12.345.678">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Condición:</label>
                            <select name="condicion_ocupacion" id="edit-apto-condicion" class="form-control">
                                <option value="propietario">Propietario</option>
                                <option value="arrendatario">Arrendatario</option>
                                <option value="residente">Residente / Familiar</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Teléfono Principal:</label>
                            <input type="text" name="telefono" id="edit-apto-telefono" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Teléfono Secundario / WhatsApp:</label>
                            <input type="text" name="telefono_secundario" id="edit-apto-telefono-sec" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Correo Electrónico:</label>
                            <input type="email" name="email" id="edit-apto-email" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Habitantes (Cantidad):</label>
                            <input type="number" min="1" max="20" name="habitantes_cant" id="edit-apto-habitantes" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alícuota (%):</label>
                            <input type="number" step="0.01" name="alicuota" id="edit-apto-alicuota" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Vehículos y Puestos de Estacionamiento:</label>
                        <input type="text" name="vehiculos_info" id="edit-apto-vehiculos" class="form-control" placeholder="Ej: Toyota Blanco ABC-123 - Puesto #02">
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:0.85rem;">
                        <div class="form-group">
                            <label class="form-label">Mascotas:</label>
                            <input type="text" name="mascotas_info" id="edit-apto-mascotas" class="form-control" placeholder="Ej: 1 Perro Schnauzer">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contacto de Emergencia:</label>
                            <input type="text" name="contacto_emergencia" id="edit-apto-emergencia" class="form-control" placeholder="Nombre y Teléfono de contacto">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Cambiar Contraseña del Apartamento (Opcional):</label>
                        <input type="text" name="new_password" class="form-control" placeholder="Dejar en blanco si no desea modificarla">
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar Cambios del Adjudicatario</button>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Modal: Emitir Cuota -->
<div class="modal-overlay" id="modal-crear-cuota">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title"><i class="bi bi-cash-coin"></i> Emitir Nueva Cuota</div>
            <button class="btn-close-modal" onclick="closeModal('modal-crear-cuota')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form id="form-crear-cuota" onsubmit="handleCreateCuota(event)">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Título o Concepto de la Cuota:</label>
                    <input type="text" name="titulo" class="form-control" placeholder="Ej: Cuota Ordinaria Mes Actual o Reparación Ascensor" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción detallada:</label>
                    <textarea name="descripcion" class="form-control" placeholder="Detalle de los gastos cubiertos por la cuota..."></textarea>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Tipo de Cuota:</label>
                        <select name="tipo" class="form-control" required>
                            <option value="ordinaria">Cuota Ordinaria</option>
                            <option value="extraordinaria">Cuota Extraordinaria</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Monto por Apartamento (USD $):</label>
                        <input type="number" step="0.01" name="monto_usd" class="form-control" placeholder="35.00" required>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Fecha de Emisión:</label>
                        <input type="date" name="fecha_emision" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha de Vencimiento:</label>
                        <input type="date" name="fecha_vencimiento" class="form-control" value="<?= date('Y-m-d', strtotime('+15 days')) ?>" required>
                    </div>
                <div class="form-group">
                    <label class="form-label">Asignar a:</label>
                    <select name="destino" class="form-control">
                        <option value="todos">Todos los Apartamentos Residenciales (Edificio Completo)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-crear-cuota')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-send-check"></i> Emitir y Asignar Cuota</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Rechazar Pago -->
<div class="modal-overlay" id="modal-reject-pago">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title"><i class="bi bi-x-circle text-danger"></i> Rechazar Pago Reportado</div>
            <button class="btn-close-modal" onclick="closeModal('modal-reject-pago')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form id="form-reject-pago" onsubmit="handleRejectPago(event)">
            <input type="hidden" name="pago_id" id="reject-pago-id">
            <div class="modal-body">
                <p style="font-size:0.88rem; color:var(--text-secondary); margin-bottom:1rem;">
                    Indique al residente el motivo por el cual no se pudo verificar su pago, para que proceda a corregirlo:
                </p>
                <div class="form-group">
                    <label class="form-label">Motivo u Observación del Rechazo:</label>
                    <textarea name="motivo_rechazo" id="reject-motivo" class="form-control" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-reject-pago')">Cancelar</button>
                <button type="submit" class="btn btn-danger"><i class="bi bi-x-lg"></i> Confirmar Rechazo</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Responder Ticket -->
<div class="modal-overlay" id="modal-reply-ticket">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title"><i class="bi bi-reply"></i> Responder a Residente</div>
            <button class="btn-close-modal" onclick="closeModal('modal-reply-ticket')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form id="form-reply-ticket" onsubmit="handleReplyTicket(event)">
            <input type="hidden" name="ticket_id" id="reply-ticket-id">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Actualizar Estado del Requerimiento:</label>
                    <select name="estado" id="reply-ticket-estado" class="form-control">
                        <option value="en_revision">En Revisión / Proceso</option>
                        <option value="resuelto">Resuelto y Cerrado</option>
                        <option value="rechazado">Rechazado / No Procede</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Mensaje de Respuesta de la Junta / Administración:</label>
                    <textarea name="respuesta_admin" id="reply-ticket-texto" class="form-control" rows="4" placeholder="Escriba la respuesta oficial para el residente..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-reply-ticket')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Enviar Respuesta</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Publicar Comunicado -->
<div class="modal-overlay" id="modal-nuevo-comunicado">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title"><i class="bi bi-megaphone"></i> Nuevo Comunicado a la Comunidad</div>
            <button class="btn-close-modal" onclick="closeModal('modal-nuevo-comunicado')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form id="form-nuevo-comunicado" onsubmit="handleCreateComunicado(event)">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Título del Comunicado:</label>
                    <input type="text" name="titulo" class="form-control" placeholder="Ej: Convocatoria a Asamblea o Corte de Agua Programado" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Prioridad / Tipo de Aviso:</label>
                    <select name="prioridad" class="form-control">
                        <option value="informativo">Informativo</option>
                        <option value="importante">Importante</option>
                        <option value="urgente">Urgente</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Contenido del Aviso:</label>
                    <textarea name="contenido" class="form-control" rows="5" placeholder="Redacte el texto completo del comunicado..." required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Firma / Publicado por:</label>
                    <input type="text" name="publicado_por" class="form-control" value="Junta de Condominio">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-nuevo-comunicado')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-megaphone-fill"></i> Publicar en Cartelera</button>
            </div>
        </form>
    </div>
<!-- Modal: Agregar Apartamento Individual -->
<div class="modal-overlay" id="modal-add-unit">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title"><i class="bi bi-door-open-fill text-primary"></i> Agregar Unidad / Apartamento</div>
            <button class="btn-close-modal" onclick="closeModal('modal-add-unit')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form id="form-add-unit" onsubmit="AdminApp.handleAddUnit(event)">
            <input type="hidden" name="condominio_id" value="<?= (int)($condominio['id'] ?? 1) ?>">
            <div class="modal-body">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Número de Piso:</label>
                        <input type="number" name="piso" class="form-control" placeholder="0 para PB, 1 a N" min="0" max="100" value="1" required>
                        <small style="color:var(--text-muted); font-size:0.7rem;">Coloque 0 para Planta Baja</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Código del Apartamento:</label>
                        <input type="text" name="codigo" class="form-control" placeholder="Ej: 13A, PH-1, Local-01" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre del Titular / Propietario:</label>
                    <input type="text" name="propietario_nombre" class="form-control" placeholder="Nombre completo">
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Alícuota (%):</label>
                        <input type="number" step="0.01" name="alicuota" class="form-control" value="2.00" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contraseña Inicial:</label>
                        <input type="text" name="password" class="form-control" value="123456" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-add-unit')">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Guardar Apartamento</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Reconfigurar Estructura del Condominio -->
<div class="modal-overlay" id="modal-structure-condo">
    <div class="modal-container" style="max-width: 580px;">
        <div class="modal-header">
            <div class="modal-title"><i class="bi bi-sliders text-primary"></i> Estructura y Generador del Edificio</div>
            <button class="btn-close-modal" onclick="closeModal('modal-structure-condo')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form id="form-structure-condo" onsubmit="AdminApp.handleRegenerateStructure(event)">
            <input type="hidden" name="condominio_id" value="<?= (int)($condominio['id'] ?? 1) ?>">
            <div class="modal-body">
                <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.8rem; color: #fbbf24;">
                    <i class="bi bi-exclamation-triangle-fill"></i> Atención: Regenerar la estructura reconstruirá las unidades residenciales del condominio según los pisos y apartamentos especificados.
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Cantidad de Pisos:</label>
                        <input type="number" name="pisos_cant" class="form-control" min="1" max="60" value="<?= $pisosCant ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Aptos por Piso:</label>
                        <input type="number" name="aptos_por_piso" class="form-control" min="1" max="20" value="<?= $aptosPiso ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Nomenclatura:</label>
                    <select name="nomenclatura_tipo" class="form-control">
                        <option value="letras" <?= ($condominio['nomenclatura_tipo'] ?? '') === 'letras' ? 'selected' : '' ?>>Letras (1A, 1B, 1C...)</option>
                        <option value="numeros" <?= ($condominio['nomenclatura_tipo'] ?? '') === 'numeros' ? 'selected' : '' ?>>Números (101, 102, 103...)</option>
                    </select>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                    <div class="form-group" style="background: var(--bg-primary); padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color);">
                        <label style="display:flex; align-items:center; gap:0.4rem; font-size:0.82rem; font-weight:600; cursor:pointer;">
                            <input type="checkbox" name="tiene_pb" value="1" <?= $tienePB ? 'checked' : '' ?>>
                            ¿Tiene Planta Baja?
                        </label>
                        <input type="number" name="aptos_pb_cant" class="form-control" min="1" max="10" value="<?= $condominio['aptos_pb_cant'] ?? 2 ?>" style="margin-top:0.4rem;" placeholder="Aptos en PB">
                    </div>
                    <div class="form-group" style="background: var(--bg-primary); padding: 0.65rem 0.85rem; border-radius: 8px; border: 1px solid var(--border-color);">
                        <label style="display:flex; align-items:center; gap:0.4rem; font-size:0.82rem; font-weight:600; cursor:pointer;">
                            <input type="checkbox" name="tiene_ph" value="1">
                            ¿Tiene Penthouse (PH)?
                        </label>
                        <input type="number" name="aptos_ph_cant" class="form-control" min="1" max="10" value="2" style="margin-top:0.4rem;" placeholder="Aptos en PH">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-structure-condo')">Cancelar</button>
                <button type="submit" class="btn btn-warning"><i class="bi bi-arrow-repeat"></i> Aplicar y Regenerar Estructura</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/app.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    AdminApp.init();
});

function showModalTab(tab) {
    document.getElementById('modal-tab-resumen').style.display = tab === 'resumen' ? 'block' : 'none';
    document.getElementById('modal-tab-tickets').style.display = tab === 'tickets' ? 'block' : 'none';
    document.getElementById('modal-tab-cuotas').style.display = tab === 'cuotas' ? 'block' : 'none';
    document.getElementById('modal-tab-editar').style.display = tab === 'editar' ? 'block' : 'none';
}

async function handleUpdateApto(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const res = await apiRequest('api/apartamentos.php?action=update', {
        method: 'POST',
        body: formData
    });
    if (res.success) {
        showToast(res.message, 'success');
        AdminApp.loadPisosMatrix();
        closeModal('modal-apto-detail');
    } else {
        showToast(res.message, 'error');
    }
}

async function handleCreateCuota(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const res = await apiRequest('api/cuotas.php?action=create', {
        method: 'POST',
        body: formData
    });
    if (res.success) {
        showToast(res.message, 'success');
        closeModal('modal-crear-cuota');
        e.target.reset();
        AdminApp.loadCuotas();
        AdminApp.loadPisosMatrix();
        AdminApp.loadMetrics();
    } else {
        showToast(res.message, 'error');
    }
}

async function handleRejectPago(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const res = await apiRequest('api/pagos.php?action=reject', {
        method: 'POST',
        body: formData
    });
    if (res.success) {
        showToast(res.message, 'success');
        closeModal('modal-reject-pago');
        AdminApp.loadPagos();
        AdminApp.loadMetrics();
    } else {
        showToast(res.message, 'error');
    }
}

async function handleReplyTicket(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const res = await apiRequest('api/tickets.php?action=respond', {
        method: 'POST',
        body: formData
    });
    if (res.success) {
        showToast(res.message, 'success');
        closeModal('modal-reply-ticket');
        AdminApp.loadTickets();
    } else {
        showToast(res.message, 'error');
    }
}

async function handleCreateComunicado(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const res = await apiRequest('api/comunicados.php?action=create', {
        method: 'POST',
        body: formData
    });
    if (res.success) {
        showToast(res.message, 'success');
        closeModal('modal-nuevo-comunicado');
        e.target.reset();
        AdminApp.loadComunicados();
    } else {
        showToast(res.message, 'error');
    }
}

async function handleConfigUpdate(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const res = await apiRequest('api/configuracion.php?action=update', {
        method: 'POST',
        body: formData
    });
    if (res.success) {
        showToast(res.message, 'success');
        AdminApp.loadMetrics();
        AdminApp.loadPisosMatrix();
    } else {
        showToast(res.message, 'error');
    }
}
</script>

</body>
</html>
