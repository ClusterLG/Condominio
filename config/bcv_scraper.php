<?php
// config/bcv_scraper.php
require_once __DIR__ . '/helpers.php';

function fetchAndSyncTasaBCV($force = false) {
    $pdo = getDBConnection();
    
    // Si no es forzado, revisar si ya se actualizó en las últimas 2 horas
    if (!$force) {
        $ultimaActualizacion = getConfig('tasa_bcv_ultima_actualizacion', '');
        if (!empty($ultimaActualizacion)) {
            $diffHours = (time() - strtotime($ultimaActualizacion)) / 3600;
            if ($diffHours < 2) {
                return [
                    'success' => true,
                    'tasa' => (float)getConfig('tasa_dolar', 0),
                    'fecha_valor' => getConfig('tasa_bcv_fecha_valor', ''),
                    'ultima_actualizacion' => $ultimaActualizacion,
                    'cached' => true,
                    'origen' => 'BCV Oficial (Caché reciente)'
                ];
            }
        }
    }

    $url = 'https://www.bcv.org.ve/';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);

    $html = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($html && $httpCode === 200) {
        $tasaFloat = null;
        $fechaValor = '';

        // Buscar contenedor del Dólar en el HTML del BCV
        if (preg_match('/id=["\']dolar["\'][^>]*>.*?<strong[^>]*>\s*([0-9.,]+)\s*<\/strong>/is', $html, $matches)) {
            $raw = str_replace([' ', "\n", "\r", "\t"], '', $matches[1]);
            $raw = str_replace('.', '', $raw); // Quitar puntos de miles si hubiere
            $raw = str_replace(',', '.', $raw); // Convertir coma decimal
            $tasaFloat = (float)$raw;
        }

        // Buscar Fecha Valor
        if (preg_match('/Fecha\s*Valor:?\s*<span[^>]*>(.*?)<\/span>/is', $html, $mFecha)) {
            $fechaValor = trim(strip_tags($mFecha[1]));
        }

        if ($tasaFloat && $tasaFloat > 0) {
            $tasaRedondeada = number_format($tasaFloat, 2, '.', '');
            setConfig('tasa_dolar', $tasaRedondeada);
            setConfig('tasa_bcv_exacta', (string)$tasaFloat);
            if ($fechaValor) {
                setConfig('tasa_bcv_fecha_valor', $fechaValor);
            }
            setConfig('tasa_bcv_ultima_actualizacion', date('Y-m-d H:i:s'));

            return [
                'success' => true,
                'tasa' => (float)$tasaRedondeada,
                'tasa_exacta' => $tasaFloat,
                'fecha_valor' => $fechaValor,
                'ultima_actualizacion' => date('Y-m-d H:i:s'),
                'origen' => 'Banco Central de Venezuela (Oficial bcv.org.ve)'
            ];
        }
    }

    // Si falló la extracción pero tenemos tasa guardada previa
    $tasaActual = (float)getConfig('tasa_dolar', 72.40);
    return [
        'success' => false,
        'tasa' => $tasaActual,
        'message' => 'No se pudo conectar al portal del BCV (' . ($error ?: "HTTP $httpCode") . '). Se conserva la última tasa registrada.',
        'ultima_actualizacion' => getConfig('tasa_bcv_ultima_actualizacion', 'N/A')
    ];
}
