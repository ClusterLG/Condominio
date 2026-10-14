// assets/js/app.js

// Utilidad para notificaciones Toast
function showToast(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    let icon = 'info-circle';
    if (type === 'success') icon = 'check-circle';
    if (type === 'error') icon = 'exclamation-circle';
    
    toast.innerHTML = `<i class="bi bi-${icon}"></i> <span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4500);
}

// Control de modales
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Cerrar modal al presionar Escape o click fuera
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(m => {
            m.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});

document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// Control del Menú Lateral Retráctil
function setupCollapsibleSidebar() {
    const toggleBtn = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    let overlay = document.querySelector('.sidebar-overlay');

    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            if (window.innerWidth <= 992) {
                sidebar.classList.toggle('mobile-open');
                overlay.classList.toggle('active', sidebar.classList.contains('mobile-open'));
            } else {
                sidebar.classList.toggle('collapsed');
            }
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        });

        // Cerrar menú móvil al hacer click en cualquier opción de navegación
        document.querySelectorAll('.sidebar .nav-item').forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 992) {
                    sidebar.classList.remove('mobile-open');
                    overlay.classList.remove('active');
                }
            });
        });
    }
}

// Helper de peticiones Fetch
async function apiRequest(url, options = {}) {
    try {
        const response = await fetch(url, options);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, message: 'Error de conexión con el servidor.' };
    }
}

// -------------------------------------------------------------
// LOGICA DASHBOARD ADMINISTRADOR
// -------------------------------------------------------------
const AdminApp = {
    tasa: 876.80,
    filterStatus: 'todos',
    pisosData: null,

    init() {
        setupCollapsibleSidebar();
        this.setupNavigation();
        this.loadPisosMatrix();
        this.loadMetrics();
    },

    setupNavigation() {
        const buttons = document.querySelectorAll('.dash-nav-btn, .nav-item[data-view], .dash-btn-card[data-view]');
        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetView = btn.dataset.view;
                this.switchView(targetView);
            });
        });
    },

    switchView(viewId) {
        document.querySelectorAll('.nav-item[data-view]').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.dash-btn-card').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.section-view').forEach(s => s.classList.remove('active'));

        const activeNav = document.querySelector(`.nav-item[data-view="${viewId}"]`);
        if (activeNav) activeNav.classList.add('active');

        const activeCard = document.querySelector(`.dash-btn-card[data-view="${viewId}"]`);
        if (activeCard) activeCard.classList.add('active');

        const activeSection = document.getElementById(`view-${viewId}`);
        if (activeSection) activeSection.classList.add('active');

        if (viewId === 'pisos') this.loadPisosMatrix();
        if (viewId === 'cuotas') this.loadCuotas();
        if (viewId === 'pagos') this.loadPagos();
        if (viewId === 'tickets') this.loadTickets();
        if (viewId === 'comunicados') this.loadComunicados();
        if (viewId === 'config') this.loadConfig();
    },

    async syncBCV() {
        showToast('Consultando tasa oficial en www.bcv.org.ve...', 'info');
        const res = await apiRequest('api/configuracion.php?action=sync_bcv');
        if (res.success) {
            this.tasa = res.data.tasa;
            showToast(res.message, 'success');
            this.loadMetrics();
            this.loadPisosMatrix();
        } else {
            showToast(res.message, 'error');
        }
    },

    async loadMetrics() {
        const res = await apiRequest('api/apartamentos.php?action=list');
        if (res.success) {
            this.tasa = res.tasa_dolar;
            document.querySelectorAll('.val-tasa').forEach(el => el.textContent = 'Bs. ' + Number(this.tasa).toLocaleString('es-VE', { minimumFractionDigits: 2 }));
            
            const fechaValorEl = document.getElementById('metric-bcv-fecha');
            if (fechaValorEl && res.fecha_valor_bcv) {
                fechaValorEl.textContent = res.fecha_valor_bcv;
            }

            let totalDeuda = 0;
            let totalRevision = 0;
            let solventes = 0;

            res.pisos.forEach(p => {
                p.unidades.forEach(u => {
                    totalDeuda += Number(u.deuda_usd);
                    if (u.estado_solvencia === 'en_revision') totalRevision++;
                    if (u.estado_solvencia === 'solvente') solventes++;
                });
            });

            const debtEl = document.getElementById('metric-total-deuda');
            if (debtEl) debtEl.textContent = '$' + totalDeuda.toLocaleString('en-US', { minimumFractionDigits: 2 });

            const revEl = document.getElementById('metric-pagos-revision');
            if (revEl) {
                revEl.textContent = totalRevision;
                const badgePagos = document.getElementById('badge-pagos-pendientes');
                if (badgePagos) {
                    badgePagos.textContent = totalRevision;
                    badgePagos.style.display = totalRevision > 0 ? 'inline-block' : 'none';
                }
            }

            const solvEl = document.getElementById('metric-aptos-solventes');
            if (solvEl) solvEl.textContent = solventes + ' / ' + (res.total_unidades || '0');
        }
    },

    setFilterPisos(status) {
        this.filterStatus = status;

        document.querySelectorAll('.filter-pill-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.filter === status);
        });

        this.renderPisosMatrix();
    },

    // 1. Matriz de Pisos y Apartamentos
    async loadPisosMatrix() {
        const container = document.getElementById('matrix-pisos-container');
        if (!container) return;

        container.innerHTML = `<div style="text-align:center; padding: 2rem; color: var(--text-muted);"><i class="bi bi-arrow-repeat spin"></i> Cargando pisos y apartamentos...</div>`;

        const res = await apiRequest('api/apartamentos.php?action=list');
        if (!res.success) {
            container.innerHTML = `<div class="card" style="color:var(--danger)">${res.message}</div>`;
            return;
        }

        this.tasa = res.tasa_dolar;
        this.pisosData = res.pisos;

        // Calcular conteos para los botones de filtro
        let countTodos = 0;
        let countSolvente = 0;
        let countRevision = 0;
        let countDeuda = 0;

        this.pisosData.forEach(p => {
            p.unidades.forEach(u => {
                countTodos++;
                if (u.estado_solvencia === 'solvente') countSolvente++;
                if (u.estado_solvencia === 'en_revision') countRevision++;
                if (u.estado_solvencia === 'deuda') countDeuda++;
            });
        });

        const elTodos = document.getElementById('count-filter-todos');
        if (elTodos) elTodos.textContent = countTodos;

        const elSolvente = document.getElementById('count-filter-solvente');
        if (elSolvente) elSolvente.textContent = countSolvente;

        const elRevision = document.getElementById('count-filter-revision');
        if (elRevision) elRevision.textContent = countRevision;

        const elDeuda = document.getElementById('count-filter-deuda');
        if (elDeuda) elDeuda.textContent = countDeuda;

        this.renderPisosMatrix();
    },

    renderPisosMatrix() {
        const container = document.getElementById('matrix-pisos-container');
        if (!container || !this.pisosData) return;

        let totalVisibles = 0;
        let html = '';

        this.pisosData.forEach(piso => {
            const unidadesFiltradas = piso.unidades.filter(u => {
                if (this.filterStatus === 'todos') return true;
                return u.estado_solvencia === this.filterStatus;
            });

            if (unidadesFiltradas.length > 0) {
                totalVisibles += unidadesFiltradas.length;

                html += `
                    <div class="floor-row">
                        <div class="floor-header">
                            <div class="floor-title">
                                <i class="bi bi-layers-fill" style="color: var(--primary);"></i>
                                <span>${piso.nombre}</span>
                            </div>
                            <div class="floor-meta">${unidadesFiltradas.length} de ${piso.unidades.length} mostrados</div>
                        </div>
                        <div class="apartments-grid">
                `;

                unidadesFiltradas.forEach(apto => {
                    let badgeClass = 'badge-solvente';
                    let badgeText = 'Solvente';
                    let badgeIcon = 'check-circle';

                    if (apto.estado_solvencia === 'deuda') {
                        badgeClass = 'badge-deuda';
                        badgeText = 'Deudor';
                        badgeIcon = 'exclamation-circle';
                    } else if (apto.estado_solvencia === 'en_revision') {
                        badgeClass = 'badge-en_revision';
                        badgeText = 'En Revisión';
                        badgeIcon = 'hourglass-split';
                    }

                    let ticketsBadge = '';
                    if (apto.tickets_abiertos > 0) {
                        ticketsBadge = `<span class="badge badge-en_revision" style="font-size:0.65rem; padding:0.15rem 0.4rem;" title="${apto.tickets_abiertos} solicitudes abiertas"><i class="bi bi-chat-dots"></i> ${apto.tickets_abiertos}</span>`;
                    }

                    let saldoUsd = apto.estado_solvencia === 'solvente' ? '$0.00' : apto.deuda_usd_formatted;
                    let saldoBs = apto.estado_solvencia === 'solvente' ? 'Al día (Solvente)' : apto.deuda_bs_formatted;

                    html += `
                        <div class="apto-card" onclick="AdminApp.viewAptoDetail(${apto.id})">
                            <div class="apto-top">
                                <div class="apto-code">
                                    <i class="bi bi-door-closed" style="color:var(--primary);"></i> ${apto.codigo}
                                </div>
                                <div style="display:flex; align-items:center; gap:0.35rem;">
                                    ${ticketsBadge}
                                    <span class="badge ${badgeClass}"><i class="bi bi-${badgeIcon}"></i> ${badgeText}</span>
                                </div>
                            </div>
                            <div class="apto-owner" title="${apto.propietario_nombre || 'Sin asignar'}">
                                <i class="bi bi-person"></i> <span>${apto.propietario_nombre || 'Sin asignar'}</span>
                            </div>
                            <div class="apto-bottom">
                                <div class="apto-balance">
                                    <div class="balance-row">
                                        <span class="balance-tag">Saldo:</span>
                                        <span class="balance-usd ${apto.estado_solvencia}">${saldoUsd}</span>
                                    </div>
                                    <div class="balance-bs">${saldoBs}</div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += `</div></div>`;
            }
        });

        if (totalVisibles === 0) {
            let labelFiltro = this.filterStatus === 'solvente' ? 'solventes' : (this.filterStatus === 'en_revision' ? 'con pagos en revisión' : 'deudores');
            html = `
                <div class="card" style="text-align:center; padding:3.5rem 1.5rem; color:var(--text-muted);">
                    <i class="bi bi-funnel" style="font-size:2.2rem; display:block; margin-bottom:0.75rem; color:var(--primary);"></i>
                    <h3 style="color:#fff; font-size:1.15rem; margin-bottom:0.35rem;">No se encontraron apartamentos ${labelFiltro}</h3>
                    <p style="font-size:0.85rem;">Actualmente ningún apartamento coincide con este criterio de búsqueda.</p>
                    <button class="btn btn-secondary btn-sm" style="margin-top:1rem;" onclick="AdminApp.setFilterPisos('todos')">
                        <i class="bi bi-grid-fill"></i> Ver Todos los Apartamentos
                    </button>
                </div>
            `;
        }

        container.innerHTML = html;
    },

    // 2. Ventana Emergente con Datos Específicos del Apartamento (Último Pago, Adjudicatario, Quejas, etc.)
    async viewAptoDetail(aptoId) {
        const modal = document.getElementById('modal-apto-detail');
        if (!modal) return;

        const res = await apiRequest(`api/apartamentos.php?action=detail&id=${aptoId}`);
        if (!res.success) {
            showToast(res.message, 'error');
            return;
        }

        const a = res.apartamento;
        document.getElementById('detail-codigo').textContent = a.codigo;
        document.getElementById('detail-tipo').textContent = a.tipo.toUpperCase();
        document.getElementById('detail-alicuota').textContent = a.alicuota + '%';
        document.getElementById('detail-deuda-usd').textContent = a.deuda_total_usd_formatted;
        document.getElementById('detail-deuda-bs').textContent = a.deuda_total_bs_formatted;

        // Ficha del Adjudicatario
        document.getElementById('detail-nombre').textContent = a.propietario_nombre || 'No registrado';
        document.getElementById('detail-cedula').textContent = a.cedula_identidad || 'No registrada';
        document.getElementById('detail-condicion').textContent = (a.condicion_ocupacion || 'propietario').toUpperCase();
        document.getElementById('detail-telefono').textContent = a.telefono || 'No registrado';
        document.getElementById('detail-telefono-sec').textContent = a.telefono_secundario || 'No registrado';
        document.getElementById('detail-email').textContent = a.email || 'No registrado';
        document.getElementById('detail-habitantes').textContent = a.habitantes_cant + ' personas';
        document.getElementById('detail-vehiculos').textContent = a.vehiculos_info || 'Ninguno registrado';
        document.getElementById('detail-mascotas').textContent = a.mascotas_info || 'Ninguna registrada';
        document.getElementById('detail-emergencia').textContent = a.contacto_emergencia || 'No registrado';

        // Llenar Formulario de Edición
        document.getElementById('edit-apto-id').value = a.id;
        document.getElementById('edit-apto-nombre').value = a.propietario_nombre || '';
        document.getElementById('edit-apto-cedula').value = a.cedula_identidad || '';
        document.getElementById('edit-apto-telefono').value = a.telefono || '';
        document.getElementById('edit-apto-telefono-sec').value = a.telefono_secundario || '';
        document.getElementById('edit-apto-email').value = a.email || '';
        document.getElementById('edit-apto-condicion').value = a.condicion_ocupacion || 'propietario';
        document.getElementById('edit-apto-habitantes').value = a.habitantes_cant || 1;
        document.getElementById('edit-apto-vehiculos').value = a.vehiculos_info || '';
        document.getElementById('edit-apto-mascotas').value = a.mascotas_info || '';
        document.getElementById('edit-apto-emergencia').value = a.contacto_emergencia || '';
        document.getElementById('edit-apto-alicuota').value = a.alicuota;

        // Visualización del ÚLTIMO PAGO
        const ultimoPagoContainer = document.getElementById('detail-ultimo-pago-box');
        if (res.ultimo_pago) {
            const up = res.ultimo_pago;
            let badgeUp = 'badge-solvente';
            if (up.estado === 'pendiente') badgeUp = 'badge-en_revision';
            if (up.estado === 'rechazado') badgeUp = 'badge-deuda';

            let comprobanteLink = up.comprobante_path ? `<a href="${up.comprobante_path}" target="_blank" class="btn btn-secondary btn-sm" style="font-size:0.75rem;"><i class="bi bi-file-earmark-image"></i> Ver Comprobante</a>` : '<span style="color:var(--text-muted); font-size:0.75rem;">Sin captura</span>';

            ultimoPagoContainer.innerHTML = `
                <div style="background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 12px; padding: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <div>
                            <strong style="color: var(--primary); font-size: 0.85rem;"><i class="bi bi-check2-circle"></i> Último Pago Reportado:</strong>
                            <span style="font-size:0.85rem; font-weight:700; margin-left:0.5rem; color:#fff;">${up.cuota_titulo}</span>
                        </div>
                        <span class="badge ${badgeUp}">${up.estado.toUpperCase()}</span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.75rem; font-size: 0.82rem;">
                        <div><span style="color: var(--text-muted);">Monto:</span> <strong style="font-family:'JetBrains Mono'">${up.monto_pagado_formatted}</strong></div>
                        <div><span style="color: var(--text-muted);">Método:</span> <strong>${up.metodo_pago.replace('_', ' ').toUpperCase()}</strong></div>
                        <div><span style="color: var(--text-muted);">Referencia:</span> <code>${up.referencia}</code></div>
                        <div><span style="color: var(--text-muted);">Fecha:</span> ${up.fecha_pago}</div>
                        <div>${comprobanteLink}</div>
                    </div>
                    ${up.motivo_rechazo ? `<div style="margin-top:0.5rem; color:var(--danger); font-size:0.75rem;">Motivo rechazo: ${up.motivo_rechazo}</div>` : ''}
                </div>
            `;
        } else {
            ultimoPagoContainer.innerHTML = `
                <div style="background: var(--bg-card); border: 1px dashed var(--border-color); border-radius: 10px; padding: 0.85rem; color: var(--text-muted); font-size: 0.82rem; text-align: center;">
                    <i class="bi bi-info-circle"></i> No registra pagos reportados aún para este apartamento.
                </div>
            `;
        }

        // Llenar Quejas y Solicitudes
        const ticketsContainer = document.getElementById('detail-tickets-feed');
        if (res.tickets.length === 0) {
            ticketsContainer.innerHTML = `<div style="text-align:center; color:var(--text-muted); padding:1rem; font-size:0.85rem;">No hay quejas o solicitudes registradas por este apartamento.</div>`;
        } else {
            ticketsContainer.innerHTML = res.tickets.map(t => {
                let badgeEstado = 'badge-en_revision';
                if (t.estado === 'resuelto') badgeEstado = 'badge-solvente';
                if (t.estado === 'rechazado') badgeEstado = 'badge-deuda';

                let adjunto = t.foto_path ? `<a href="${t.foto_path}" target="_blank" class="btn btn-secondary btn-sm" style="font-size:0.7rem; padding:0.2rem 0.5rem;"><i class="bi bi-paperclip"></i> Evidencia</a>` : '';
                let resp = t.respuesta_admin ? `<div style="margin-top:0.5rem; padding:0.5rem; background:rgba(59,130,246,0.06); border-left:2px solid var(--primary); font-size:0.8rem;"><strong>Respuesta Admin:</strong> ${t.respuesta_admin}</div>` : '';

                return `
                    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 0.75rem; margin-bottom: 0.65rem;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.35rem;">
                            <div>
                                <span class="badge badge-ordinaria" style="font-size:0.65rem;">${t.tipo.toUpperCase()}</span>
                                <span class="badge ${badgeEstado}" style="font-size:0.65rem;">${t.estado}</span>
                                <strong style="font-size:0.85rem; margin-left:0.35rem; color:#fff;">${t.titulo}</strong>
                            </div>
                            <small style="color:var(--text-muted); font-size:0.7rem;">${t.created_at}</small>
                        </div>
                        <p style="font-size:0.82rem; color:var(--text-secondary); margin-bottom:0.35rem;">${t.descripcion}</p>
                        ${adjunto}
                        ${resp}
                    </div>
                `;
            }).join('');
        }

        // Llenar tabla de cuotas
        const tbodyCuotas = document.getElementById('tbody-apto-cuotas');
        if (res.cuotas.length === 0) {
            tbodyCuotas.innerHTML = `<tr><td colspan="5" style="text-align:center; color:var(--text-muted);">Sin cuotas asignadas.</td></tr>`;
        } else {
            tbodyCuotas.innerHTML = res.cuotas.map(c => `
                <tr>
                    <td><strong>${c.titulo}</strong></td>
                    <td><span class="badge badge-${c.tipo}">${c.tipo}</span></td>
                    <td>${c.monto_usd_formatted} <br><small style="color:var(--text-muted)">${c.monto_bs_formatted}</small></td>
                    <td>${c.fecha_vencimiento}</td>
                    <td><span class="badge badge-${c.estado}">${c.estado}</span></td>
                </tr>
            `).join('');
        }

        // Pestaña por defecto: Resumen
        showModalTab('resumen');
        openModal('modal-apto-detail');
    },

    // 3. Emisión de Cuotas
    async loadCuotas() {
        const container = document.getElementById('cuotas-table-container');
        if (!container) return;

        const res = await apiRequest('api/cuotas.php?action=list');
        if (!res.success) return;

        let html = `
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Título y Tipo</th>
                        <th>Monto Unidad</th>
                        <th>Vencimiento</th>
                        <th>Recaudación</th>
                        <th>Progreso</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
        `;

        if (res.cuotas.length === 0) {
            html += `<tr><td colspan="6" style="text-align:center; color:var(--text-muted); padding:2rem;">No hay cuotas emitidas.</td></tr>`;
        } else {
            res.cuotas.forEach(c => {
                html += `
                    <tr>
                        <td>
                            <strong>${c.titulo}</strong><br>
                            <span class="badge badge-${c.tipo}">${c.tipo}</span>
                            <small style="color:var(--text-secondary); display:block; margin-top:2px;">${c.descripcion || ''}</small>
                        </td>
                        <td>
                            <strong style="font-family:'JetBrains Mono'">${c.monto_usd_formatted}</strong><br>
                            <small style="color:var(--text-muted)">${c.monto_bs_formatted}</small>
                        </td>
                        <td>${c.fecha_vencimiento}</td>
                        <td>
                            <strong>${c.total_recaudado_usd_formatted}</strong> / ${c.total_esperado_usd_formatted}<br>
                            <small style="color:var(--text-muted)">${c.total_pagados} pagados de ${c.total_asignados}</small>
                        </td>
                        <td style="min-width: 140px;">
                            <div style="display:flex; justify-content:space-between; font-size:0.75rem;">
                                <span>${c.porcentaje_recaudado}%</span>
                                <span>${c.total_pendientes} pdtes</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: ${c.porcentaje_recaudado}%"></div>
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-danger btn-sm" onclick="AdminApp.deleteCuota(${c.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }

        html += `</tbody></table>`;
        container.innerHTML = html;
    },

    async deleteCuota(id) {
        if (!confirm('¿Está seguro de eliminar esta cuota y todas sus asignaciones?')) return;
        const formData = new FormData();
        formData.append('id', id);

        const res = await apiRequest('api/cuotas.php?action=delete', {
            method: 'POST',
            body: formData
        });

        if (res.success) {
            showToast(res.message, 'success');
            this.loadCuotas();
            this.loadMetrics();
        } else {
            showToast(res.message, 'error');
        }
    },

    // 4. Auditoría de Pagos
    async loadPagos(filtro = 'todos') {
        const container = document.getElementById('pagos-table-container');
        if (!container) return;

        const res = await apiRequest(`api/pagos.php?action=list&estado=${filtro}`);
        if (!res.success) return;

        let html = `
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Apartamento</th>
                        <th>Cuota Aplicada</th>
                        <th>Método & Referencia</th>
                        <th>Monto Reportado</th>
                        <th>Fecha</th>
                        <th>Comprobante</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
        `;

        if (res.pagos.length === 0) {
            html += `<tr><td colspan="8" style="text-align:center; color:var(--text-muted); padding:2rem;">No hay pagos registrados bajo este filtro.</td></tr>`;
        } else {
            res.pagos.forEach(p => {
                let badgeClass = 'badge-solvente';
                if (p.estado === 'pendiente') badgeClass = 'badge-en_revision';
                if (p.estado === 'rechazado') badgeClass = 'badge-deuda';

                let comprobanteHtml = '<span style="color:var(--text-muted)">Sin adjunto</span>';
                if (p.comprobante_path) {
                    comprobanteHtml = `<a href="${p.comprobante_path}" target="_blank" class="btn btn-secondary btn-sm" style="font-size:0.75rem;"><i class="bi bi-image"></i> Ver</a>`;
                }

                let acciones = '-';
                if (p.estado === 'pendiente') {
                    acciones = `
                        <div style="display:flex; gap:0.4rem;">
                            <button class="btn btn-success btn-sm" title="Aprobar pago" onclick="AdminApp.approvePago(${p.id})">
                                <i class="bi bi-check-lg"></i> Aprobar
                            </button>
                            <button class="btn btn-danger btn-sm" title="Rechazar pago" onclick="AdminApp.openRejectPagoModal(${p.id})">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    `;
                } else if (p.estado === 'rechazado') {
                    acciones = `<small style="color:var(--danger)">${p.motivo_rechazo || 'Rechazado'}</small>`;
                } else if (p.estado === 'aprobado') {
                    acciones = `<span style="color:var(--success); font-size:0.8rem;"><i class="bi bi-check-all"></i> Auditado</span>`;
                }

                html += `
                    <tr>
                        <td>
                            <strong style="font-family:'JetBrains Mono'; font-size:1rem;">Apto ${p.apto_codigo}</strong><br>
                            <small style="color:var(--text-secondary)">${p.propietario_nombre || ''}</small>
                        </td>
                        <td>
                            <strong>${p.cuota_titulo}</strong><br>
                            <span class="badge badge-${p.cuota_tipo}">${p.cuota_tipo}</span>
                        </td>
                        <td>
                            <strong>${p.metodo_pago.replace('_', ' ').toUpperCase()}</strong><br>
                            <code style="color:var(--primary);">${p.referencia}</code>
                        </td>
                        <td>
                            <strong style="font-family:'JetBrains Mono'">${p.monto_pagado_formatted}</strong>
                            <div style="font-size:0.7rem; color:var(--text-muted)">Cuota: ${p.cuota_monto_usd_formatted}</div>
                        </td>
                        <td>${p.fecha_pago}</td>
                        <td>${comprobanteHtml}</td>
                        <td><span class="badge ${badgeClass}">${p.estado}</span></td>
                        <td>${acciones}</td>
                    </tr>
                `;
            });
        }

        html += `</tbody></table>`;
        container.innerHTML = html;
    },

    async approvePago(pagoId) {
        if (!confirm('¿Confirma que ha verificado el pago y desea marcar la cuota como SOLVENTE?')) return;
        const formData = new FormData();
        formData.append('pago_id', pagoId);

        const res = await apiRequest('api/pagos.php?action=approve', {
            method: 'POST',
            body: formData
        });

        if (res.success) {
            showToast(res.message, 'success');
            this.loadPagos();
            this.loadMetrics();
        } else {
            showToast(res.message, 'error');
        }
    },

    openRejectPagoModal(pagoId) {
        document.getElementById('reject-pago-id').value = pagoId;
        document.getElementById('reject-motivo').value = 'El número de referencia o captura de comprobante no coincide en el estado de cuenta bancario.';
        openModal('modal-reject-pago');
    },

    // 5. Quejas y Solicitudes
    async loadTickets(filtroTipo = 'todos', filtroEstado = 'todos') {
        const container = document.getElementById('tickets-container');
        if (!container) return;

        const res = await apiRequest(`api/tickets.php?action=list&tipo=${filtroTipo}&estado=${filtroEstado}`);
        if (!res.success) return;

        let html = '';
        if (res.tickets.length === 0) {
            html = `<div class="card" style="text-align:center; color:var(--text-muted); padding:3rem;">No hay tickets ni quejas registradas.</div>`;
        } else {
            res.tickets.forEach(t => {
                let badgeEstado = 'badge-en_revision';
                if (t.estado === 'resuelto') badgeEstado = 'badge-solvente';
                if (t.estado === 'rechazado') badgeEstado = 'badge-deuda';

                let adjunto = '';
                if (t.foto_path) {
                    adjunto = `<div style="margin-top:0.75rem;"><a href="${t.foto_path}" target="_blank" class="btn btn-secondary btn-sm"><i class="bi bi-paperclip"></i> Ver Evidencia Adjunta</a></div>`;
                }

                let respuestaHtml = '';
                if (t.respuesta_admin) {
                    respuestaHtml = `
                        <div style="margin-top:1rem; padding:0.75rem 1rem; background-color:rgba(59, 130, 246, 0.08); border-left:3px solid var(--primary); border-radius:4px;">
                            <strong style="color:var(--primary); font-size:0.8rem;"><i class="bi bi-reply"></i> Respuesta de la Administración:</strong>
                            <p style="font-size:0.85rem; margin-top:0.25rem;">${t.respuesta_admin}</p>
                            <small style="color:var(--text-muted)">Respondido el: ${t.fecha_respuesta || ''}</small>
                        </div>
                    `;
                }

                html += `
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <span class="badge badge-ordinaria" style="margin-right:0.5rem;">${t.tipo.replace('_', ' ').toUpperCase()}</span>
                                <span class="badge ${badgeEstado}">${t.estado}</span>
                                <span class="badge" style="background-color:rgba(255,255,255,0.06)">Prioridad ${t.prioridad.toUpperCase()}</span>
                                <h3 style="margin-top:0.5rem; font-size:1.15rem; color:#fff;">${t.titulo}</h3>
                            </div>
                            <div style="text-align:right;">
                                <strong style="font-family:'JetBrains Mono'; font-size:1.05rem;">Apto ${t.apto_codigo}</strong><br>
                                <small style="color:var(--text-secondary)">${t.propietario_nombre || ''} (${t.apto_telefono || ''})</small>
                            </div>
                        </div>
                        <div style="font-size:0.92rem; color:var(--text-primary); margin-bottom:0.75rem;">
                            ${t.descripcion}
                        </div>
                        ${adjunto}
                        ${respuestaHtml}
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:1.25rem; padding-top:0.75rem; border-top:1px solid var(--border-color); flex-wrap:wrap; gap:0.5rem;">
                            <small style="color:var(--text-muted)">Fecha: ${t.created_at}</small>
                            <button class="btn btn-primary btn-sm" onclick="AdminApp.openTicketReplyModal(${t.id}, '${t.estado}')">
                                <i class="bi bi-chat-left-text"></i> Responder / Cambiar Estado
                            </button>
                        </div>
                    </div>
                `;
            });
        }

        container.innerHTML = html;
    },

    openTicketReplyModal(ticketId, estadoActual) {
        document.getElementById('reply-ticket-id').value = ticketId;
        document.getElementById('reply-ticket-estado').value = estadoActual;
        document.getElementById('reply-ticket-texto').value = '';
        openModal('modal-reply-ticket');
    },

    // 6. Cartelera Digital
    async loadComunicados() {
        const container = document.getElementById('comunicados-container');
        if (!container) return;

        const res = await apiRequest('api/comunicados.php?action=list');
        if (!res.success) return;

        let html = '';
        if (res.comunicados.length === 0) {
            html = `<div class="card" style="text-align:center; color:var(--text-muted); padding:3rem;">No hay comunicados publicados.</div>`;
        } else {
            res.comunicados.forEach(c => {
                let badgeClass = 'badge-ordinaria';
                if (c.prioridad === 'urgente') badgeClass = 'badge-deuda';
                if (c.prioridad === 'importante') badgeClass = 'badge-en_revision';

                html += `
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <span class="badge ${badgeClass}">${c.prioridad.toUpperCase()}</span>
                                <h3 style="font-size:1.15rem; color:#fff; margin-top:0.35rem;">${c.titulo}</h3>
                            </div>
                            <button class="btn btn-danger btn-sm" onclick="AdminApp.deleteComunicado(${c.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <p style="font-size:0.92rem; color:var(--text-secondary); white-space:pre-line;">${c.contenido}</p>
                        <div style="margin-top:1rem; font-size:0.75rem; color:var(--text-muted);">
                            Emitido por: <strong>${c.publicado_por}</strong> | Fecha: ${c.created_at}
                        </div>
                    </div>
                `;
            });
        }

        container.innerHTML = html;
    },

    async deleteComunicado(id) {
        if (!confirm('¿Desea retirar este comunicado de la cartelera?')) return;
        const formData = new FormData();
        formData.append('id', id);

        const res = await apiRequest('api/comunicados.php?action=delete', {
            method: 'POST',
            body: formData
        });

        if (res.success) {
            showToast(res.message, 'success');
            this.loadComunicados();
        } else {
            showToast(res.message, 'error');
        }
    },

    // 7. Configuraciones
    async loadConfig() {
        const res = await apiRequest('api/configuracion.php?action=get');
        if (!res.success) return;

        const c = res.config;
        if (document.getElementById('cfg-tasa-dolar')) document.getElementById('cfg-tasa-dolar').value = c.tasa_dolar || '';
        if (document.getElementById('cfg-nombre-edificio')) document.getElementById('cfg-nombre-edificio').value = c.nombre_edificio || '';
        if (document.getElementById('cfg-pago-movil')) document.getElementById('cfg-pago-movil').value = c.banco_pago_movil || '';
        if (document.getElementById('cfg-transferencia')) document.getElementById('cfg-transferencia').value = c.banco_transferencia || '';
        if (document.getElementById('cfg-zelle')) document.getElementById('cfg-zelle').value = c.cuenta_zelle || '';
    },

    // 8. Métodos de Gestión Multi-Condominio y Estructura
    async switchCondominio(condoId) {
        showToast('Conmutando al condominio...', 'info');
        const res = await apiRequest(`api/condominios.php?action=switch&id=${condoId}`);
        if (res.success) {
            window.location.reload();
        } else {
            showToast(res.message, 'error');
        }
    },

    async handleAddUnit(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        const res = await apiRequest('api/apartamentos.php?action=create_unit', {
            method: 'POST',
            body: formData
        });

        if (res.success) {
            showToast(res.message, 'success');
            closeModal('modal-add-unit');
            form.reset();
            this.loadPisosMatrix();
            this.loadMetrics();
        } else {
            showToast(res.message, 'error');
        }
    },

    async deleteUnit(unitId, codigo) {
        if (!confirm(`¿Está seguro de eliminar el apartamento ${codigo}? Esta acción borrará sus cuotas y pagos asociados.`)) {
            return;
        }

        const formData = new FormData();
        formData.append('id', unitId);

        const res = await apiRequest('api/apartamentos.php?action=delete_unit', {
            method: 'POST',
            body: formData
        });

        if (res.success) {
            showToast(res.message, 'success');
            closeModal('modal-apto-detail');
            this.loadPisosMatrix();
            this.loadMetrics();
        } else {
            showToast(res.message, 'error');
        }
    },

    async handleRegenerateStructure(e) {
        e.preventDefault();
        if (!confirm('ADVERTENCIA: Esta acción reconfigurará la estructura de apartamentos y pisos de este condominio. ¿Desea continuar?')) {
            return;
        }

        const form = e.target;
        const formData = new FormData(form);

        const res = await apiRequest('api/apartamentos.php?action=regenerate_structure', {
            method: 'POST',
            body: formData
        });

        if (res.success) {
            showToast(res.message, 'success');
            closeModal('modal-structure-condo');
            this.loadPisosMatrix();
            this.loadMetrics();
        } else {
            showToast(res.message, 'error');
        }
    }
};

// -------------------------------------------------------------
// LOGICA PORTAL DEL RESIDENTE
// -------------------------------------------------------------
const ResidentApp = {
    tasa: 876.80,
    cuotasPendientes: [],

    init() {
        setupCollapsibleSidebar();
        this.loadPendingCuotas();
        this.loadHistory();
        this.loadTickets();
        this.loadNotices();
        this.loadProfile();
    },

    async loadPendingCuotas() {
        const container = document.getElementById('resident-cuotas-container');
        const selectModal = document.getElementById('modal-report-cuota-select');
        if (!container) return;

        const res = await apiRequest('api/pagos.php?action=resident_pending_cuotas');
        if (!res.success) return;

        this.tasa = res.tasa_dolar;
        this.cuotasPendientes = res.cuotas;

        let totalDeudaUsd = 0;
        let html = `
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Monto USD</th>
                        <th>Monto Bs (BCV Oficial)</th>
                        <th>Vencimiento</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
        `;

        if (selectModal) {
            selectModal.innerHTML = '<option value="">-- Seleccione la cuota a pagar --</option>';
        }

        if (res.cuotas.length === 0) {
            html += `<tr><td colspan="6" style="text-align:center; color:var(--success); padding:2rem;"><i class="bi bi-shield-check" style="font-size:1.8rem; display:block; margin-bottom:0.5rem;"></i>¡Felicidades! Su apartamento se encuentra solvente y al día.</td></tr>`;
        } else {
            res.cuotas.forEach(c => {
                if (c.estado === 'pendiente') {
                    totalDeudaUsd += Number(c.monto_usd);
                }

                let badgeEstado = c.estado === 'en_revision' ? 'badge-en_revision' : 'badge-deuda';
                let labelEstado = c.estado === 'en_revision' ? 'Pago en Auditoría' : 'Pendiente';

                if (selectModal && c.estado === 'pendiente') {
                    selectModal.innerHTML += `<option value="${c.id}" data-usd="${c.monto_usd}">${c.titulo} (${c.monto_usd_formatted})</option>`;
                }

                let accionBtn = '-';
                if (c.estado === 'pendiente') {
                    accionBtn = `<button class="btn btn-primary btn-sm" onclick="ResidentApp.openReportModalForCuota(${c.id}, ${c.monto_usd})"><i class="bi bi-wallet2"></i> Reportar Pago</button>`;
                } else {
                    accionBtn = `<span style="font-size:0.78rem; color:var(--warning)"><i class="bi bi-hourglass-split"></i> Verificando</span>`;
                }

                html += `
                    <tr>
                        <td>
                            <strong>${c.titulo}</strong><br>
                            <span class="badge badge-${c.tipo}">${c.tipo}</span>
                        </td>
                        <td><strong style="font-family:'JetBrains Mono'">${c.monto_usd_formatted}</strong></td>
                        <td><strong style="font-family:'JetBrains Mono'">${c.monto_bs_formatted}</strong></td>
                        <td>${c.fecha_vencimiento}</td>
                        <td><span class="badge ${badgeEstado}">${labelEstado}</span></td>
                        <td>${accionBtn}</td>
                    </tr>
                `;
            });
        }

        html += `</tbody></table>`;
        container.innerHTML = html;

        const totalBs = totalDeudaUsd * this.tasa;
        const usdEl = document.getElementById('res-deuda-usd');
        const bsEl = document.getElementById('res-deuda-bs');
        if (usdEl) usdEl.textContent = '$' + totalDeudaUsd.toLocaleString('en-US', { minimumFractionDigits: 2 });
        if (bsEl) bsEl.textContent = 'Bs. ' + totalBs.toLocaleString('es-VE', { minimumFractionDigits: 2 });
    },

    openReportModalForCuota(cuotaId, montoUsd) {
        const select = document.getElementById('modal-report-cuota-select');
        if (select) {
            select.value = cuotaId;
        }
        const montoInput = document.getElementById('report-monto');
        if (montoInput) {
            const moneda = document.getElementById('report-moneda').value;
            if (moneda === 'USD') {
                montoInput.value = montoUsd.toFixed(2);
            } else {
                montoInput.value = (montoUsd * this.tasa).toFixed(2);
            }
        }
        openModal('modal-reportar-pago');
    },

    async loadHistory() {
        const container = document.getElementById('resident-history-container');
        if (!container) return;

        const res = await apiRequest('api/pagos.php?action=resident_history');
        if (!res.success) return;

        let html = `
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Método & Referencia</th>
                        <th>Monto</th>
                        <th>Fecha de Pago</th>
                        <th>Estado</th>
                        <th>Comprobante</th>
                    </tr>
                </thead>
                <tbody>
        `;

        if (res.pagos.length === 0) {
            html += `<tr><td colspan="6" style="text-align:center; color:var(--text-muted); padding:2rem;">No registra pagos anteriores.</td></tr>`;
        } else {
            res.pagos.forEach(p => {
                let badgeClass = 'badge-solvente';
                if (p.estado === 'pendiente') badgeClass = 'badge-en_revision';
                if (p.estado === 'rechazado') badgeClass = 'badge-deuda';

                let adjunto = p.comprobante_path ? `<a href="${p.comprobante_path}" target="_blank" class="btn btn-secondary btn-sm"><i class="bi bi-file-earmark-check"></i> Ver</a>` : '-';

                html += `
                    <tr>
                        <td><strong>${p.cuota_titulo}</strong></td>
                        <td>
                            <strong>${p.metodo_pago.replace('_', ' ').toUpperCase()}</strong><br>
                            <code>${p.referencia}</code>
                        </td>
                        <td><strong style="font-family:'JetBrains Mono'">${p.monto_pagado_formatted}</strong></td>
                        <td>${p.fecha_pago}</td>
                        <td>
                            <span class="badge ${badgeClass}">${p.estado}</span>
                            ${p.motivo_rechazo ? `<div style="font-size:0.75rem; color:var(--danger); margin-top:3px;">${p.motivo_rechazo}</div>` : ''}
                        </td>
                        <td>${adjunto}</td>
                    </tr>
                `;
            });
        }

        html += `</tbody></table>`;
        container.innerHTML = html;
    },

    async loadTickets() {
        const container = document.getElementById('resident-tickets-container');
        if (!container) return;

        const res = await apiRequest('api/tickets.php?action=resident_list');
        if (!res.success) return;

        let html = '';
        if (res.tickets.length === 0) {
            html = `<div style="text-align:center; color:var(--text-muted); padding:2rem;">No has presentado quejas o solicitudes actualmente.</div>`;
        } else {
            res.tickets.forEach(t => {
                let badgeEstado = 'badge-en_revision';
                if (t.estado === 'resuelto') badgeEstado = 'badge-solvente';
                if (t.estado === 'rechazado') badgeEstado = 'badge-deuda';

                let respuestaHtml = '';
                if (t.respuesta_admin) {
                    respuestaHtml = `
                        <div style="margin-top:0.75rem; padding:0.75rem; background-color:rgba(59, 130, 246, 0.08); border-left:3px solid var(--primary); border-radius:4px;">
                            <strong style="color:var(--primary); font-size:0.8rem;"><i class="bi bi-chat-quote-fill"></i> Respuesta de la Administración:</strong>
                            <p style="font-size:0.85rem; margin-top:0.25rem;">${t.respuesta_admin}</p>
                        </div>
                    `;
                }

                html += `
                    <div class="card" style="margin-bottom:1rem; border-color:rgba(255,255,255,0.06);">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:0.5rem;">
                            <div>
                                <span class="badge badge-ordinaria">${t.tipo.replace('_', ' ').toUpperCase()}</span>
                                <span class="badge ${badgeEstado}">${t.estado}</span>
                                <h4 style="margin-top:0.4rem; color:#fff;">${t.titulo}</h4>
                            </div>
                            <small style="color:var(--text-muted)">${t.created_at}</small>
                        </div>
                        <p style="font-size:0.88rem; color:var(--text-secondary); margin-top:0.5rem;">${t.descripcion}</p>
                        ${respuestaHtml}
                    </div>
                `;
            });
        }

        container.innerHTML = html;
    },

    async loadNotices() {
        const container = document.getElementById('resident-notices-container');
        if (!container) return;

        const res = await apiRequest('api/comunicados.php?action=list');
        if (!res.success) return;

        let html = '';
        if (res.comunicados.length === 0) {
            html = `<div style="text-align:center; color:var(--text-muted); padding:2rem;">No hay avisos recientes.</div>`;
        } else {
            res.comunicados.forEach(c => {
                html += `
                    <div class="card" style="margin-bottom:1rem;">
                        <span class="badge badge-ordinaria">${c.prioridad.toUpperCase()}</span>
                        <h4 style="color:#fff; margin-top:0.35rem;">${c.titulo}</h4>
                        <p style="font-size:0.88rem; color:var(--text-secondary); margin-top:0.5rem; white-space:pre-line;">${c.contenido}</p>
                        <small style="color:var(--text-muted); display:block; margin-top:0.5rem;">${c.publicado_por} • ${c.created_at}</small>
                    </div>
                `;
            });
        }
        container.innerHTML = html;
    },

    async loadProfile() {
        // Carga los datos del adjudicatario en el formulario de "Mi Perfil"
        const form = document.getElementById('form-resident-profile');
        if (!form) return;
        // Los datos iniciales ya se imprimen en el PHP pero si se actualizan se refrescan aquí
    }
};

window.AdminApp = AdminApp;
window.ResidentApp = ResidentApp;
window.openModal = openModal;
window.closeModal = closeModal;
window.showToast = showToast;
