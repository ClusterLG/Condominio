# -*- coding: utf-8 -*-
import os
import glob
from reportlab.pdfgen import canvas

def create_horizontal_pdf():
    images_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), "imagenes")
    output_pdf = os.path.join(images_dir, "presentacion_condohub_diapositivas.pdf")

    # Obtener todas las imágenes ordenadas numéricamente
    pattern = os.path.join(images_dir, "diapositiva_*.png")
    image_files = sorted(glob.glob(pattern))

    if not image_files:
        print(f"Error: No se encontraron imagenes en {images_dir}")
        return

    print(f"Encontradas {len(image_files)} imagenes para generar el PDF horizontal.")

    # Dimensiones exactas 16:9 Full HD (1920 x 1080 puntos)
    PAGE_WIDTH = 1920
    PAGE_HEIGHT = 1080

    c = canvas.Canvas(output_pdf, pagesize=(PAGE_WIDTH, PAGE_HEIGHT))
    c.setTitle("CondoHub SaaS - Presentacion del Sistema de Gestion de Condominios")
    c.setAuthor("Cluster LG - Leonardo Morales")
    c.setSubject("Presentacion interactiva de diapositivas CondoHub SaaS")

    for idx, img_path in enumerate(image_files, 1):
        filename = os.path.basename(img_path)
        print(f"[{idx}/{len(image_files)}] Insertando {filename} en pagina horizontal...")
        
        # Dibujar imagen ocupando exactamente toda la pagina horizontal
        c.drawImage(img_path, 0, 0, width=PAGE_WIDTH, height=PAGE_HEIGHT)
        c.showPage()

    c.save()

    size_mb = os.path.getsize(output_pdf) / (1024 * 1024)
    print(f"\nPDF generado con exito: {output_pdf}")
    print(f"Tamano: {size_mb:.2f} MB ({len(image_files)} paginas horizontales)")

if __name__ == '__main__':
    create_horizontal_pdf()
