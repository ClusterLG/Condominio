# -*- coding: utf-8 -*-
import os
import json
from reportlab.lib.pagesizes import letter
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, KeepTogether, HRFlowable
)
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib import colors
from reportlab.pdfgen import canvas

class NumberedCanvas(canvas.Canvas):
    def __init__(self, *args, **kwargs):
        super(NumberedCanvas, self).__init__(*args, **kwargs)
        self._saved_page_states = []

    def showPage(self):
        self._saved_page_states.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        num_pages = len(self._saved_page_states)
        for state in self._saved_page_states:
            self.__dict__.update(state)
            self.draw_page_decorations(num_pages)
            super(NumberedCanvas, self).showPage()
        super(NumberedCanvas, self).save()

    def draw_page_decorations(self, page_count):
        self.saveState()
        self.setFont("Helvetica", 8)
        self.setFillColor(colors.HexColor("#64748b"))

        # Header (pages > 1)
        if self._pageNumber > 1:
            self.drawString(36, 758, "CondoHub SaaS - Plataforma Integral de Gestion de Condominios")
            self.drawRightString(576, 758, "Credenciales Piloto: Residencias Los Samanes")
            self.setStrokeColor(colors.HexColor("#e2e8f0"))
            self.setLineWidth(0.5)
            self.line(36, 752, 576, 752)

        # Footer (all pages)
        self.setStrokeColor(colors.HexColor("#e2e8f0"))
        self.setLineWidth(0.5)
        self.line(36, 42, 576, 42)

        self.drawString(36, 30, "Documento Confidencial - Guia de Acceso para Pruebas de Cliente - CondoHub SaaS")
        page_text = f"Pagina {self._pageNumber} de {page_count}"
        self.drawRightString(576, 30, page_text)
        self.restoreState()


def build_pdf():
    pdf_filename = "CondoHub_Resumen_y_Accesos_Los_Samanes.pdf"
    doc = SimpleDocTemplate(
        pdf_filename,
        pagesize=letter,
        leftMargin=36,
        rightMargin=36,
        topMargin=44,
        bottomMargin=50
    )

    styles = getSampleStyleSheet()

    # Custom styles
    title_style = ParagraphStyle(
        'DocTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=20,
        leading=24,
        textColor=colors.HexColor('#0f172a'),
        alignment=0
    )

    tagline_style = ParagraphStyle(
        'DocTagline',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=10,
        leading=14,
        textColor=colors.HexColor('#2563eb')
    )

    section_heading = ParagraphStyle(
        'SectionHeading',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=11,
        leading=15,
        textColor=colors.HexColor('#1e293b'),
        spaceAfter=5
    )

    body_style = ParagraphStyle(
        'DocBody',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=12,
        textColor=colors.HexColor('#334155')
    )

    table_header = ParagraphStyle(
        'TableHeader',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8,
        leading=10,
        textColor=colors.white,
        alignment=1
    )

    cell_style = ParagraphStyle(
        'TableCell',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=7.5,
        leading=9.5,
        textColor=colors.HexColor('#1e293b')
    )

    cell_bold = ParagraphStyle(
        'TableCellBold',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=7.5,
        leading=9.5,
        textColor=colors.HexColor('#0f172a')
    )

    cell_code = ParagraphStyle(
        'TableCellCode',
        parent=styles['Normal'],
        fontName='Courier-Bold',
        fontSize=7.2,
        leading=9,
        textColor=colors.HexColor('#1d4ed8')
    )

    hright1 = ParagraphStyle('HRight1', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=8, leading=10, textColor=colors.HexColor('#64748b'), alignment=2)
    hright2 = ParagraphStyle('HRight2', parent=styles['Normal'], fontName='Helvetica', fontSize=7.5, leading=9.5, textColor=colors.HexColor('#475569'), alignment=2)

    story = []

    # ---------------- HEADER BOX ----------------
    header_content = [
        [
            Paragraph("<b>CondoHub</b> <font color='#2563eb'>SaaS</font>", title_style),
            Paragraph("AMBIENTE PILOTO DE DEMOSTRACION<br/>URL de Acceso: <b>http://localhost/Condominio/</b>", hright1)
        ],
        [
            Paragraph("<b>Gestion inteligente y transparente de condominios residenciales</b>", tagline_style),
            Paragraph("Edicion Piloto: Residencias Los Samanes (50 Aptos)", hright2)
        ]
    ]
    t_header = Table(header_content, colWidths=[350, 190])
    t_header.setStyle(TableStyle([
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('BOTTOMPADDING', (0,0), (-1,-1), 1),
        ('TOPPADDING', (0,0), (-1,-1), 1),
        ('LEFTPADDING', (0,0), (-1,-1), 0),
        ('RIGHTPADDING', (0,0), (-1,-1), 0),
    ]))
    story.append(t_header)
    story.append(Spacer(1, 6))
    story.append(HRFlowable(width="100%", thickness=1.5, color=colors.HexColor("#2563eb"), spaceAfter=8))

    # ---------------- QUE ES CONDOHUB SAAS ----------------
    story.append(Paragraph("1. ¿Que es CondoHub SaaS?", section_heading))
    p_intro = Paragraph(
        "<b>CondoHub SaaS</b> es una suite tecnologica multi-condominio diseñada para automatizar de punta a punta la administracion financiera, operativa y comunicacional de edificios y conjuntos residenciales. Permite gestionar contabilidad bimoneda (<b>USD y Bolivares con tasa oficial BCV automatizada</b>), emision de cuotas ordinarias y extraordinarias, auditoria de pagos (Pago Movil, Transferencias bancarias y Zelle), gestion de tickets/incidencias y carteleras informativas digitales en tiempo real, con respaldo continuo en la nube (Supabase Cloud).",
        body_style
    )
    story.append(p_intro)
    story.append(Spacer(1, 6))

    # Features Cards in a 2-Column Table
    features_data = [
        [
            Paragraph("<b>🏢 Para Administradores y Juntas de Condominio:</b><br/>"
                      "• <b>Mapeo Interactivo de Pisos:</b> Monitoreo visual de cada unidad (Solvente, Moroso, Saldo a Favor).<br/>"
                      "• <b>Facturacion Multi-Moneda:</b> Emision de cuotas con alicuotas automaticas y conversion BCV diaria.<br/>"
                      "• <b>Auditoria y Conciliacion de Pagos:</b> Aprobacion inmediata con calculo de saldos a favor.<br/>"
                      "• <b>Multiples Cuentas Bancarias:</b> Registro de cuentas corrientes, Pago Movil (0134 Banesco, 0105 Mercantil) y Zelle.", body_style),
            Paragraph("<b>👤 Para Propietarios y Residentes:</b><br/>"
                      "• <b>Portal Personalizado por Apartamento:</b> Consulta de balance exacto en tiempo real ($ USD y Bs).<br/>"
                      "• <b>Reporte de Pagos Intuitivo:</b> Carga de comprobantes por Pago Movil, Transferencia o Zelle.<br/>"
                      "• <b>Modulo de Atencion / Tickets:</b> Envio y seguimiento de quejas, averias y solicitudes formales.<br/>"
                      "• <b>Seguridad Institucional:</b> Credenciales unicas por apartamento con alerta de cambio de clave.", body_style)
        ]
    ]
    t_features = Table(features_data, colWidths=[265, 265])
    t_features.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (0,0), colors.HexColor('#f8fafc')),
        ('BACKGROUND', (1,0), (1,0), colors.HexColor('#f0fdf4')),
        ('BOX', (0,0), (0,0), 0.8, colors.HexColor('#cbd5e1')),
        ('BOX', (1,0), (1,0), 0.8, colors.HexColor('#86efac')),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
        ('LEFTPADDING', (0,0), (-1,-1), 8),
        ('RIGHTPADDING', (0,0), (-1,-1), 8),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
    ]))
    story.append(t_features)
    story.append(Spacer(1, 8))

    # ---------------- GUIA DE ACCESO PARA EL CLIENTE ----------------
    story.append(Paragraph("2. Instrucciones para Probar el Sistema (Guia del Cliente)", section_heading))
    instructions_text = Paragraph(
        "<b>1. Acceso:</b> Ingrese desde su navegador web a la direccion <b>http://localhost/Condominio/</b> (o el enlace del servidor asignado).<br/>"
        "<b>2. Usuario:</b> En el campo central <b>'Usuario'</b>, escriba la direccion de correo institucional asignada (ejemplo: <code>admin@resdlossamanes.com</code> para administrar, o <code>aptopba@resdlossamanes.com</code> para ver el portal de un residente).<br/>"
        "<b>3. Contraseña:</b> Ingrese la clave correspondiente y haga clic en <b>'Ingresar a CondoHub'</b>. El sistema reconocera de forma automatica su rol y abrira el portal correspondiente sin necesidad de seleccionar edificio ni cambiar de pestaña.",
        body_style
    )
    story.append(instructions_text)
    story.append(Spacer(1, 8))

    # ---------------- TABLA 1: CUENTAS ADMINISTRATIVAS ----------------
    story.append(Paragraph("3. Cuentas Administrativas de Demostracion", section_heading))
    admin_table_data = [
        [
            Paragraph("Rol / Nivel", table_header),
            Paragraph("Usuario / Correo Institucional", table_header),
            Paragraph("Contraseña", table_header),
            Paragraph("Alcance / Funcionalidad de Prueba", table_header)
        ],
        [
            Paragraph("<b>Administrador Local</b><br/><font size='6.5' color='#64748b'>Condominio Los Samanes</font>", cell_style),
            Paragraph("<code>admin@resdlossamanes.com</code><br/><font size='6.5' color='#64748b'>Alias: admin</font>", cell_code),
            Paragraph("<code>admin123</code>", cell_code),
            Paragraph("Administracion completa del edificio: mapa de pisos, aprobacion de pagos pendientes, emision de cuotas ordinarias/extraordinarias, comunicados y respuesta a tickets.", cell_style)
        ],
        [
            Paragraph("<b>Superadministrador Global</b><br/><font size='6.5' color='#64748b'>Consola Maestra ClusterLG</font>", cell_style),
            Paragraph("<code>clusterlg</code><br/><font size='6.5' color='#64748b'>superadmin@condohub.com</font>", cell_code),
            Paragraph("<code>glretsulc</code>", cell_code),
            Paragraph("Consola global multi-edificio: alta de nuevos condominios, auditoria profunda de residencias, inspeccion de credenciales y bitacora de auditoria en vivo.", cell_style)
        ]
    ]
    t_admin = Table(admin_table_data, colWidths=[115, 145, 75, 205])
    t_admin.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor('#0f172a')),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor('#cbd5e1')),
        ('INNERGRID', (0,0), (-1,-1), 0.5, colors.HexColor('#e2e8f0')),
        ('TOPPADDING', (0,0), (-1,-1), 4),
        ('BOTTOMPADDING', (0,0), (-1,-1), 4),
        ('LEFTPADDING', (0,0), (-1,-1), 6),
        ('RIGHTPADDING', (0,0), (-1,-1), 6),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('BACKGROUND', (0,1), (-1,1), colors.HexColor('#eff6ff')),
        ('BACKGROUND', (0,2), (-1,2), colors.HexColor('#f8fafc')),
    ]))
    story.append(t_admin)
    story.append(Spacer(1, 10))

    # ---------------- TABLA 2: RESIDENCIAS LOS SAMANES (50 APARTAMENTOS) ----------------
    story.append(Paragraph("4. Directorio de Cuentas Residenciales: Residencias Los Samanes (50 Apartamentos)", section_heading))
    p_sub = Paragraph(
        "<font size='8' color='#64748b'>Estructura: Planta Baja + 12 Pisos • 50 Unidades Residenciales • Clave inicial por defecto: <b>123456</b></font>",
        body_style
    )
    story.append(p_sub)
    story.append(Spacer(1, 5))

    # Load 50 units from samanes_units.json
    with open('samanes_units.json', 'r', encoding='utf-8') as f:
        units = json.load(f)

    # Table columns:
    # [Apto, Piso, Alicuota, Propietario / Estatus, Usuario / Correo Institucional, Contraseña]
    apto_table_data = [
        [
            Paragraph("Apto", table_header),
            Paragraph("Piso", table_header),
            Paragraph("Alic.", table_header),
            Paragraph("Propietario Adjudicado", table_header),
            Paragraph("Usuario / Correo Institucional", table_header),
            Paragraph("Clave", table_header),
        ]
    ]

    for idx, u in enumerate(units):
        code = u.get('codigo', '')
        piso = u.get('piso', 0)
        piso_str = "PB" if piso == 0 else f"Piso {piso}"
        alicuota = f"{u.get('alicuota', 2)}%"
        prop = u.get('propietario')
        
        if prop:
            prop_p = Paragraph(f"<b>{prop}</b>", cell_style)
        else:
            prop_p = Paragraph("<font color='#64748b'><i>Disponible (Sin adjudicar)</i></font>", cell_style)

        clean_code = code.lower().replace("-", "").replace(" ", "")
        email = f"apto{clean_code}@resdlossamanes.com"
        email_p = Paragraph(f"<code>{email}</code>", cell_code)
        
        clave = u.get('password') or '123456'
        clave_p = Paragraph(f"<code>{clave}</code>", cell_code)

        row = [
            Paragraph(f"<b>{code}</b>", cell_bold),
            Paragraph(piso_str, cell_style),
            Paragraph(alicuota, cell_style),
            prop_p,
            email_p,
            clave_p
        ]
        apto_table_data.append(row)

    # 540 total width: [42, 45, 38, 175, 180, 60] = 540
    t_aptos = Table(apto_table_data, colWidths=[42, 45, 38, 175, 180, 60], repeatRows=1)
    
    t_style = [
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor('#1e293b')),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor('#cbd5e1')),
        ('INNERGRID', (0,0), (-1,-1), 0.4, colors.HexColor('#e2e8f0')),
        ('TOPPADDING', (0,0), (-1,-1), 2.2),
        ('BOTTOMPADDING', (0,0), (-1,-1), 2.2),
        ('LEFTPADDING', (0,0), (-1,-1), 4),
        ('RIGHTPADDING', (0,0), (-1,-1), 4),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('ALIGN', (0,1), (2,-1), 'CENTER'),
        ('ALIGN', (5,1), (5,-1), 'CENTER'),
    ]

    # Alternate row colors
    for r in range(1, len(apto_table_data)):
        if r % 2 == 0:
            t_style.append(('BACKGROUND', (0, r), (-1, r), colors.HexColor('#f8fafc')))

    t_aptos.setStyle(TableStyle(t_style))
    story.append(t_aptos)

    doc.build(story, canvasmaker=NumberedCanvas)
    print(f"PDF generado con exito: {pdf_filename}")

if __name__ == '__main__':
    build_pdf()
