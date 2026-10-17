const puppeteer = require('puppeteer-core');
const { spawn } = require('child_process');
const path = require('path');
const fs = require('fs');

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const FFMPEG_PATH = 'C:\\xampp\\htdocs\\Condominio\\presentacion_video\\node_modules\\@remotion\\compositor-win32-x64-msvc\\ffmpeg.exe';
const AUDIO_PATH = 'C:\\xampp\\htdocs\\Condominio\\presentacion_video\\public\\tech_ambient.wav';
const OUT_DIR = path.join(__dirname, 'videos');

if (!fs.existsSync(OUT_DIR)) {
  fs.mkdirSync(OUT_DIR, { recursive: true });
}

const SLIDES_CONFIG = [
  { index: 1, name: 'diapositiva_01_portada.mp4', title: 'Portada CondoHub SaaS', durationMs: 5000 },
  { index: 2, name: 'diapositiva_02_problema.mp4', title: 'El Problema Tradicional', durationMs: 5000 },
  { index: 3, name: 'diapositiva_03_solucion.mp4', title: 'Ecosistema y Solución', durationMs: 5000 },
  { index: 4, name: 'diapositiva_04_roles.mp4', title: 'Tres Portales Especializados', durationMs: 5000 },
  {
    index: 5,
    name: 'diapositiva_05_cuotas_individuales.mp4',
    title: 'Cuotas Individuales por Apartamento',
    durationMs: 6500,
    action: async (page) => {
      await new Promise(r => setTimeout(r, 2200));
      await page.click('#btnAsignar').catch(() => {});
    }
  },
  {
    index: 6,
    name: 'diapositiva_06_pago_residente.mp4',
    title: 'Pagos del Residente y Calculadora BCV',
    durationMs: 6500,
    action: async (page) => {
      await new Promise(r => setTimeout(r, 2000));
      await page.$eval('#calcUsd', el => { el.value = '100'; el.dispatchEvent(new Event('input')); }).catch(() => {});
      await new Promise(r => setTimeout(r, 1800));
      await page.$eval('#calcUsd', el => { el.value = '45'; el.dispatchEvent(new Event('input')); }).catch(() => {});
    }
  },
  {
    index: 7,
    name: 'diapositiva_07_conciliacion.mp4',
    title: 'Consola Administrativa de Conciliación',
    durationMs: 7500,
    action: async (page) => {
      await new Promise(r => setTimeout(r, 1800));
      const btns = await page.$$('#queue .approve-btn');
      if (btns[0]) await btns[0].click();
      await new Promise(r => setTimeout(r, 1400));
      if (btns[1]) await btns[1].click();
      await new Promise(r => setTimeout(r, 1400));
      if (btns[2]) await btns[2].click();
    }
  },
  { index: 8, name: 'diapositiva_08_saldos_auditoria.mp4', title: 'Saldos a Favor y Auditoría', durationMs: 5000 },
  { index: 9, name: 'diapositiva_09_ciclo_mensual.mp4', title: 'Ciclo Mensual en 4 Pasos', durationMs: 5000 },
  {
    index: 10,
    name: 'diapositiva_10_mapa_pisos.mp4',
    title: 'Mapa Interactivo de Pisos 2D',
    durationMs: 6500,
    action: async (page) => {
      await new Promise(r => setTimeout(r, 2000));
      const units = await page.$$('.unit.m');
      if (units[0]) await units[0].click();
      await new Promise(r => setTimeout(r, 1800));
      const favorUnits = await page.$$('.unit.f');
      if (favorUnits[0]) await favorUnits[0].click();
    }
  },
  { index: 11, name: 'diapositiva_11_seguridad_ux.mp4', title: 'Seguridad Legal y Menú Retráctil', durationMs: 5000 },
  { index: 12, name: 'diapositiva_12_tecnologia.mp4', title: 'Tecnología y Arquitectura', durationMs: 5000 },
  { index: 13, name: 'diapositiva_13_impacto.mp4', title: 'Impacto y Métricas Comprobables', durationMs: 5000 },
  { index: 14, name: 'diapositiva_14_piloto.mp4', title: 'Edición Piloto Residencias Los Samanes', durationMs: 5000 },
  { index: 15, name: 'diapositiva_15_contacto.mp4', title: 'Demostración y Llamado a la Acción', durationMs: 5500 }
];

async function recordSlide(page, config) {
  console.log(`\n▶ Grabando Diapositiva ${config.index}/15: "${config.title}"...`);
  const outFile = path.join(OUT_DIR, config.name);

  // Ir a la diapositiva directamente ejecutando la función go(index - 1)
  await page.evaluate((idx) => {
    // Si la función go está disponible en scope o simulamos click en dots
    const dots = document.querySelectorAll('#dots button');
    if (dots && dots[idx]) {
      dots[idx].click();
    } else {
      window.location.hash = `#${idx + 1}`;
    }
  }, config.index - 1);

  // Breve espera para que comiencen las animaciones de entrada
  await new Promise(r => setTimeout(r, 200));

  const hasAudio = fs.existsSync(AUDIO_PATH);
  const ffmpegArgs = [
    '-y',
    '-f', 'image2pipe',
    '-vcodec', 'mjpeg',
    '-framerate', '30',
    '-i', 'pipe:0',
  ];

  if (hasAudio) {
    ffmpegArgs.push(
      '-stream_loop', '-1',
      '-i', AUDIO_PATH,
      '-af', 'volume=0.30'
    );
  }

  ffmpegArgs.push(
    '-c:v', 'libx264',
    '-pix_fmt', 'yuv420p',
    '-preset', 'fast',
    '-crf', '19',
    '-movflags', '+faststart'
  );

  if (hasAudio) {
    ffmpegArgs.push(
      '-c:a', 'aac',
      '-b:a', '192k',
      '-shortest'
    );
  }

  ffmpegArgs.push(outFile);

  const ffmpeg = spawn(FFMPEG_PATH, ffmpegArgs);
  ffmpeg.stderr.on('data', () => {});

  const client = await page.createCDPSession();
  let frameCount = 0;
  let isRecording = true;

  const onFrame = async ({ data, sessionId }) => {
    if (!isRecording) return;
    frameCount++;
    try {
      if (ffmpeg.stdin.writable) {
        ffmpeg.stdin.write(Buffer.from(data, 'base64'));
      }
      await client.send('Page.screencastFrameAck', { sessionId }).catch(() => {});
    } catch (e) {}
  };

  client.on('Page.screencastFrame', onFrame);

  await client.send('Page.startScreencast', {
    format: 'jpeg',
    quality: 95,
    everyNthFrame: 1,
  });

  // Ejecutar acción interactiva si existe
  if (config.action) {
    await config.action(page);
  }

  // Esperar tiempo de duración
  const remainingTime = config.durationMs - (config.action ? 2000 : 0);
  await new Promise(r => setTimeout(r, Math.max(2500, remainingTime)));

  isRecording = false;
  await client.send('Page.stopScreencast').catch(() => {});
  client.off('Page.screencastFrame', onFrame);

  await new Promise(r => {
    ffmpeg.stdin.end();
    ffmpeg.on('close', r);
  });

  await client.detach().catch(() => {});

  const stats = fs.statSync(outFile);
  const sizeMb = (stats.size / (1024 * 1024)).toFixed(2);
  console.log(`✅ Completado: ${config.name} (${frameCount} frames grabados, ${sizeMb} MB)`);
}

async function main() {
  console.log('🚀 Iniciando grabador de pantalla Full HD (1920x1080) para CondoHub SaaS...');
  console.log(`📁 Directorio de destino: ${OUT_DIR}`);

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

  for (const config of SLIDES_CONFIG) {
    await recordSlide(page, config);
  }

  await browser.close();
  console.log('\n🎉 ¡Las 15 diapositivas animadas fueron grabadas exitosamente en formato MP4!');
}

main().catch(err => {
  console.error('❌ Error durante la grabación:', err);
  process.exit(1);
});
