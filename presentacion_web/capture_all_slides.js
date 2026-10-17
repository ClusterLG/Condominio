const puppeteer = require('puppeteer-core');
const path = require('path');
const fs = require('fs');

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const OUT_DIR = path.join(__dirname, 'imagenes');

if (!fs.existsSync(OUT_DIR)) {
  fs.mkdirSync(OUT_DIR, { recursive: true });
}

const SLIDES = [
  { index: 1, name: 'diapositiva_01_portada.png', title: 'Portada CondoHub SaaS' },
  { index: 2, name: 'diapositiva_02_problema.png', title: 'El Problema Tradicional' },
  { index: 3, name: 'diapositiva_03_solucion.png', title: 'Ecosistema y Solución' },
  { index: 4, name: 'diapositiva_04_roles.png', title: 'Tres Portales Especializados' },
  {
    index: 5,
    name: 'diapositiva_05_cuotas_individuales.png',
    title: 'Cuotas Individuales por Apartamento',
    action: async (page) => {
      await page.click('#btnAsignar').catch(() => {});
      await new Promise(r => setTimeout(r, 600));
    }
  },
  { index: 6, name: 'diapositiva_06_pago_residente.png', title: 'Pagos del Residente y Calculadora BCV' },
  {
    index: 7,
    name: 'diapositiva_07_conciliacion.png',
    title: 'Consola Administrativa de Conciliación',
    action: async (page) => {
      const btns = await page.$$('#queue .approve-btn');
      for (const btn of btns) {
        await btn.click().catch(() => {});
        await new Promise(r => setTimeout(r, 200));
      }
      await new Promise(r => setTimeout(r, 600));
    }
  },
  { index: 8, name: 'diapositiva_08_saldos_auditoria.png', title: 'Saldos a Favor y Auditoría' },
  { index: 9, name: 'diapositiva_09_ciclo_mensual.png', title: 'Ciclo Mensual en 4 Pasos' },
  {
    index: 10,
    name: 'diapositiva_10_mapa_pisos.png',
    title: 'Mapa Interactivo de Pisos 2D',
    action: async (page) => {
      const units = await page.$$('.unit.m');
      if (units[0]) await units[0].click().catch(() => {});
      await new Promise(r => setTimeout(r, 500));
    }
  },
  { index: 11, name: 'diapositiva_11_seguridad_ux.png', title: 'Seguridad Legal y Menú Retráctil' },
  { index: 12, name: 'diapositiva_12_tecnologia.png', title: 'Tecnología y Arquitectura' },
  { index: 13, name: 'diapositiva_13_impacto.png', title: 'Impacto y Métricas Comprobables' },
  { index: 14, name: 'diapositiva_14_piloto.png', title: 'Edición Piloto Residencias Los Samanes' },
  { index: 15, name: 'diapositiva_15_contacto.png', title: 'Demostración y Llamado a la Acción' }
];

async function main() {
  console.log('📸 Iniciando captura en alta resolución (1920x1080) de las diapositivas...');
  console.log(`📁 Carpeta de destino: ${OUT_DIR}`);

  const browser = await puppeteer.launch({
    executablePath: CHROME_PATH,
    headless: 'new',
    args: [
      '--window-size=1920,1080',
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-gpu-vsync',
      '--use-gl=angle',
      '--use-angle=d3d11',
    ],
    defaultViewport: {
      width: 1920,
      height: 1080,
      deviceScaleFactor: 1,
    }
  });

  const page = await browser.newPage();
  const url = 'http://localhost/Condominio/presentacion_web/index.html#1';
  console.log(`🌐 Navegando a ${url}...`);
  await page.goto(url, { waitUntil: 'networkidle0' });

  for (const s of SLIDES) {
    console.log(`▶ Capturando Diapositiva ${s.index}/15: "${s.title}"...`);

    // Navegar a la diapositiva
    await page.evaluate((idx) => {
      const dots = document.querySelectorAll('#dots button');
      if (dots && dots[idx]) {
        dots[idx].click();
      } else {
        window.location.hash = `#${idx + 1}`;
      }
    }, s.index - 1);

    // Esperar a que se asienten las animaciones de entrada y contadores
    await new Promise(r => setTimeout(r, 1400));

    // Si tiene una acción de interacción
    if (s.action) {
      await s.action(page);
    }

    const outPath = path.join(OUT_DIR, s.name);
    await page.screenshot({ path: outPath, type: 'png' });

    const stats = fs.statSync(outPath);
    const sizeKb = (stats.size / 1024).toFixed(1);
    console.log(`✅ Guardada: ${s.name} (${sizeKb} KB)`);
  }

  await browser.close();
  console.log('\n🎉 ¡Las 15 capturas en alta definición fueron guardadas exitosamente!');
}

main().catch(err => {
  console.error('❌ Error capturando diapositivas:', err);
  process.exit(1);
});
