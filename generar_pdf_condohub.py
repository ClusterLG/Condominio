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
        self.setFont("Helvetica", 7.5)
        self.setFillColor(colors.HexColor("#64748b"))

        # Header (pages > 1)
        if self._pageNumber > 1:
            self.drawString(36, 762, "CondoHub SaaS • Sistema de Gestion de Condominios • Cluster LG")
            self.drawRightString(576, 762, "Directorio Piloto: Residencias Los Samanes")
            self.setStrokeColor(colors.HexColor("#e2e8f0"))
            self.setLineWidth(0.5)
            self.line(36, 756, 576, 756)

        # Footer (all pages)
        self.setStrokeColor(colors.HexColor("#e2e8f0"))
        self.setLineWidth(0.5)
        self.line(36, 44, 576, 44)

        self.drawString(36, 32, "CondoHub es un proyecto de Leonardo Morales • Cluster LG • 100% hecho en La Guaira • Soporte WhatsApp: 0412-9031966")
        page_text = f"Pagina {self._pageNumber} de {page_count}"
        self.drawRightString(576, 32, page_text)
        self.restoreState()


def build_pdf():
    pdf_filename = "CondoHub_Resumen_y_Accesos_Los_Samanes.pdf"
    doc = SimpleDocTemplate(
        pdf_filename,
        pagesize=letter,
        leftMargin=36,
        rightMargin=36,
        topMargin=40,
        bottomMargin=52
    )

    styles = getSampleStyleSheet()

    # Custom styles
    title_style = ParagraphStyle(
        'DocTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=19,
        leading=23,
        textColor=colors.HexColor('#0f172a'),
        alignment=0
    )

    tagline_style = ParagraphStyle(
        'DocTagline',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9.5,
        leading=13,
        textColor=colors.HexColor('#2563eb')
    )

    section_heading = ParagraphStyle(
        'SectionHeading',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=10.5,
        leading=14,
        textColor=colors.HexColor('#1e293b'),
        spaceAfter=4
    )

    body_style = ParagraphStyle(
        'DocBody',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.2,
        leading=11.5,
        textColor=colors.HexColor('#334155')
    )

    table_header = ParagraphStyle(
        'TableHeader',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=7.8,
        leading=10,
        textColor=colors.white,
        alignment=1
    )

    cell_style = ParagraphStyle(
        'TableCell',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=7.3,
        leading=9.2,
        textColor=colors.HexColor('#1e293b')
    )

    cell_bold = ParagraphStyle(
        'TableCellBold',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=7.3,
        leading=9.2,
        textColor=colors.HexColor('#0f172a')
    )

    cell_code = ParagraphStyle(
        'TableCellCode',
        parent=styles['Normal'],
        fontName='Courier-Bold',
        fontSize=7.0,
        leading=8.8,
        textColor=colors.HexColor('#1d4ed8')
    )

    hright1 = ParagraphStyle('HRight1', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=7.5, leading=9.5, textColor=colors.HexColor('#475569'), alignment=2)
    hright2 = ParagraphStyle('HRight2', parent=styles['Normal'], fontName='Helvetica', fontSize=7.2, leading=9, textColor=colors.HexColor('#64748b'), alignment=2)

    story = []

    # ---------------- HEADER BOX ----------------
    header_content = [
        [
            Paragraph("<b>CondoHub</b> <font color='#2563eb'>SaaS</font>", title_style),
            Paragraph("REPOSITORIO / ACCESO EN LINEA:<br/><b>https://github.com/ClusterLG/Condominio</b>", hright1)
        ],
        [
            Paragraph("<b>Gestion inteligente y transparente de condominios residenciales</b>", tagline_style),
            Paragraph("Edicion Piloto: Residencias Los Samanes • Caracas", hright2)
        ]
    ]
    t_header = Table(header_content, colWidths=[330, 210])
    t_header.setStyle(TableStyle([
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('BOTTOMPADDING', (0,0), (-1,-1), 1),
        ('TOPPADDING', (0,0), (-1,-1), 1),
        ('LEFTPADDING', (0,0), (-1,-1), 0),
        ('RIGHTPADDING', (0,0), (-1,-1), 0),
    ]))
    story.append(t_header)
    story.append(Spacer(1, 4))
    story.append(HRFlowable(width="100%", thickness=1.5, color=colors.HexColor("#2563eb"), spaceAfter=7))

    # ---------------- QUE ES CONDOHUB SAAS ----------------
    story.append(Paragraph("1. ¿Que es CondoHub SaaS?", section_heading))
    p_intro = Paragraph(
        "<b>CondoHub SaaS</b> es una suite tecnologica multi-condominio diseñada para modernizar y transparentar la administracion financiera, operativa y comunicacional de edificios y conjuntos residenciales. Cuenta con <b>infraestructura de alta disponibilidad en Data Center</b>, garantizando respaldo continuo en todo momento, alta velocidad de consulta y cero perdida de informacion. Opera con contabilidad bimoneda (<b>USD y Bolivares a tasa oficial BCV automatizada</b>), facturacion con alicuotas precisas, conciliacion de multiples cuentas bancarias (0134 Banesco, 0105 Mercantil, Pago Movil y Zelle) y canales digitales de comunicacion para los residentes.",
        body_style
    )
    story.append(p_intro)
    story.append(Spacer(1, 5))

    # Features Cards in a 2-Column Table
    features_data = [
        [
            Paragraph("<b>🏢 Para Administradores y Juntas de Condominio:</b><br/>"
                      "• <b>Mapeo Interactivo de Pisos:</b> Visualizacion en tiempo real de solvencia, morosidad y saldos a favor.<br/>"
                      "• <b>Emision de Cuotas Globales e Individuales:</b> Asignacion de cuotas ordinarias por alicuota o cobros puntuales a un apartamento especifico sin alterar la alicuota del edificio.<br/>"
                      "• <b>Consola Administrativa de Pagos:</b> Auditoria con visor de comprobantes bancarios, aprobacion en 1 clic, acreditacion de saldos a favor y emision de recibos digitales.<br/>"
                      "• <b>Modulo de Perfil del Administrador:</b> Actualizacion de datos de contacto y cambio seguro de clave.<br/>"
                      "• <b>Menu Vertical Retractil:</b> Navegacion lateral con colapso y memoria localStorage.<br/>"
                      "• <b>Blindaje de Alicuotas:</b> Proteccion estricta segun documento de condominio y Ley de Propiedad Horizontal para salvaguardar la equidad.", body_style),
            Paragraph("<b>👤 Para Propietarios y Residentes:</b><br/>"
                      "• <b>Portal Personalizado por Apartamento:</b> Consulta de balance exacto en tiempo real ($ USD y Bs al BCV) con desglose de cuotas individuales asignadas.<br/>"
                      "• <b>Reporte de Pagos Multicanal 24/7:</b> Carga intuitiva de comprobantes por Pago Movil, Transferencias (Banesco/Mercantil), Zelle o Divisas con calculo automatico.<br/>"
                      "• <b>Historial y Recibos Digitales:</b> Trazabilidad de pagos aprobados, saldo disponible y comprobante oficial emitido por la junta.<br/>"
                      "• <b>Menu Lateral Responsive:</b> Maximo espacio util en pantalla sin barras de scroll molestas.<br/>"
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
        ('TOPPADDING', (0,0), (-1,-1), 4),
        ('BOTTOMPADDING', (0,0), (-1,-1), 4),
        ('LEFTPADDING', (0,0), (-1,-1), 7),
        ('RIGHTPADDING', (0,0), (-1,-1), 7),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
    ]))
    story.append(t_features)
    story.append(Spacer(1, 5))

    # Highlight Box: Módulo Financiero de Pagos Individuales y Conciliación
    fin_box_data = [
        [
            Paragraph("<b>💼 Modulo Especializado: Pagos Individuales y Gestion Administrativa de Cobranza:</b><br/>"
                      "<b>1. Cuotas Individuales por Unidad:</b> Permite a la administracion emitir cobros puntuales a un apartamento especifico (reparaciones exclusivas, sustitucion de llaves/tags, multas de convivencia o alquiler de areas sociales) sin impactar el prorrateo general de las alicuotas.<br/>"
                      "<b>2. Pagos Individuales Bimoneda:</b> Los copropietarios reportan pagos parciales o totales en Bolivares (segun tasa oficial BCV automatizada) o Divisas, adjuntando la captura digital y numero de referencia para cotejo instantaneo.<br/>"
                      "<b>3. Consola de Conciliacion y Aprobacion en 1 Clic:</b> La junta examina visualmente el capture contra el extracto bancario; al aprobar, el sistema acredita el monto, recalcula el estado de cuenta y expide el Recibo Digital Oficial.<br/>"
                      "<b>4. Manejo de Saldos a Favor y Excedentes:</b> Los pagos por adelantado o redondeos a favor se acreditan en cuenta y se deducen automaticamente en las cuotas subsiguientes.",
                      body_style)
        ]
    ]
    t_fin_box = Table(fin_box_data, colWidths=[530])
    t_fin_box.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#f5f3ff')),
        ('BOX', (0,0), (-1,-1), 0.8, colors.HexColor('#c4b5fd')),
        ('TOPPADDING', (0,0), (-1,-1), 4),
        ('BOTTOMPADDING', (0,0), (-1,-1), 4),
        ('LEFTPADDING', (0,0), (-1,-1), 7),
        ('RIGHTPADDING', (0,0), (-1,-1), 7),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
    ]))
    story.append(t_fin_box)
    story.append(Spacer(1, 6))

    # ---------------- GUIA DE ACCESO PARA EL CLIENTE ----------------
    story.append(Paragraph("2. Instrucciones para la Prueba del Cliente", section_heading))
    instructions_text = Paragraph(
        "<b>1. Acceso al Sistema:</b> Ingrese a la plataforma desplegada en <b>https://github.com/ClusterLG/Condominio</b> (o en su entorno local <b>http://localhost/Condominio/</b>).<br/>"
        "<b>2. Usuario:</b> En el campo central <b>'Usuario'</b>, escriba el correo institucional asignado (para administrar use <code>admin@resdlossamanes.com</code>; para ver el portal de un residente use cualquiera de los correos de apartamento listados a continuacion, ej: <code>aptopba@resdlossamanes.com</code>).<br/>"
        "<b>3. Contraseña:</b> Escriba la clave asignada y pulse <b>'Ingresar a CondoHub'</b>. El sistema detectara el apartamento o condominio correspondiente de forma automatica.",
        body_style
    )
    story.append(instructions_text)
    story.append(Spacer(1, 7))

    # ---------------- TABLA 1: CUENTA DE ADMINISTRACION ----------------
    story.append(Paragraph("3. Cuenta de Administracion del Condominio", section_heading))
    admin_table_data = [
        [
            Paragraph("Rol de Acceso", table_header),
            Paragraph("Usuario / Correo Institucional", table_header),
            Paragraph("Contraseña", table_header),
            Paragraph("Alcance / Funciones Habilitadas para la Prueba", table_header)
        ],
        [
            Paragraph("<b>Administrador de Condominio</b><br/><font size='6.5' color='#64748b'>Gestion Residencias Los Samanes</font>", cell_style),
            Paragraph("<code>admin@resdlossamanes.com</code><br/><font size='6.5' color='#64748b'>Alias directo: admin</font>", cell_code),
            Paragraph("<code>admin123</code>", cell_code),
            Paragraph("Acceso total al panel de administracion: visualizacion del mapa interactivo de 12 pisos + PB, emision de cuotas ordinarias, extraordinarias e individuales por apartamento, consola de conciliacion y aprobacion de pagos en 1 clic, acreditacion de saldos a favor, emision de recibos digitales, gestion de cuentas bancarias y respuesta a tickets.", cell_style)
        ]
    ]
    t_admin = Table(admin_table_data, colWidths=[130, 160, 75, 175])
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
    ]))
    story.append(t_admin)
    story.append(Spacer(1, 8))

    # ---------------- TABLA 2: RESIDENCIAS LOS SAMANES (50 APARTAMENTOS) ----------------
    story.append(Paragraph("4. Directorio de Cuentas Residenciales: Residencias Los Samanes (50 Apartamentos)", section_heading))
    p_sub = Paragraph(
        "<font size='7.5' color='#64748b'>Edificio Piloto • Planta Baja + 12 Pisos • 50 Unidades Residenciales • Clave inicial por defecto: <b>123456</b></font>",
        body_style
    )
    story.append(p_sub)
    story.append(Spacer(1, 4))

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
