<?php
// config/generator.php
// Generador y calculador dinámico de estructuras de apartamentos y pisos

require_once __DIR__ . '/db.php';

/**
 * Genera la estructura de apartamentos para un condominio dado
 * 
 * @param PDO $pdo Conexión a la base de datos
 * @param int $condominioId ID del condominio
 * @param array $config Opciones de estructura:
 *                      - pisos_cant: int (ej. 10)
 *                      - aptos_por_piso: int (ej. 4)
 *                      - tiene_pb: bool
 *                      - aptos_pb_cant: int (ej. 2)
 *                      - nomenclatura_tipo: 'letras' | 'numeros'
 *                      - tiene_ph: bool (ej. false)
 *                      - aptos_ph_cant: int (ej. 2)
 *                      - clave_default: string ('123456')
 *                      - limpiar_existentes: bool (false)
 * @return array ['success' => bool, 'total_generados' => int, 'codigos' => array]
 */
function generarEstructuraCondominio(PDO $pdo, int $condominioId, array $config = []): array {
    $pisosCant = max(1, (int)($config['pisos_cant'] ?? 12));
    $aptosPorPiso = max(1, (int)($config['aptos_por_piso'] ?? 4));
    $tienePB = !empty($config['tiene_pb']);
    $aptosPBCant = $tienePB ? max(1, (int)($config['aptos_pb_cant'] ?? 2)) : 0;
    $nomenclatura = $config['nomenclatura_tipo'] ?? 'letras';
    $tienePH = !empty($config['tiene_ph']);
    $aptosPHCant = $tienePH ? max(1, (int)($config['aptos_ph_cant'] ?? 2)) : 0;
    $claveDefault = $config['clave_default'] ?? '123456';
    $limpiarExistentes = !empty($config['limpiar_existentes']);

    if ($limpiarExistentes) {
        $stmtDel = $pdo->prepare("DELETE FROM apartamentos WHERE condominio_id = ?");
        $stmtDel->execute([$condominioId]);
    }

    $passwordHash = password_hash($claveDefault, PASSWORD_DEFAULT);
    $unidades = [];

    // 1. Planta Baja (Piso 0)
    if ($tienePB && $aptosPBCant > 0) {
        for ($i = 1; $i <= $aptosPBCant; $i++) {
            $sufijo = $nomenclatura === 'numeros' ? sprintf('%02d', $i) : chr(64 + $i);
            $codigo = 'PB-' . $sufijo;
            $unidades[] = [
                'piso' => 0,
                'codigo' => $codigo,
                'nombre' => 'Propietario ' . $codigo
            ];
        }
    }

    // 2. Pisos intermedios (1 al N)
    for ($piso = 1; $piso <= $pisosCant; $piso++) {
        for ($u = 1; $u <= $aptosPorPiso; $u++) {
            if ($nomenclatura === 'numeros') {
                $codigo = sprintf('%d%02d', $piso, $u); // Ej: 101, 102 / 1201, 1204
            } else {
                $letra = chr(64 + $u); // A, B, C, D...
                $codigo = $piso . $letra; // Ej: 1A, 1B / 12D
            }
            $unidades[] = [
                'piso' => $piso,
                'codigo' => $codigo,
                'nombre' => 'Propietario ' . $codigo
            ];
        }
    }

    // 3. Penthouse (si aplica)
    if ($tienePH && $aptosPHCant > 0) {
        $pisoPH = $pisosCant + 1;
        for ($i = 1; $i <= $aptosPHCant; $i++) {
            $sufijo = $nomenclatura === 'numeros' ? sprintf('%02d', $i) : chr(64 + $i);
            $codigo = 'PH-' . $sufijo;
            $unidades[] = [
                'piso' => $pisoPH,
                'codigo' => $codigo,
                'nombre' => 'Propietario ' . $codigo
            ];
        }
    }

    $totalUnidades = count($unidades);
    if ($totalUnidades === 0) {
        return ['success' => false, 'total_generados' => 0, 'message' => 'No se generaron unidades.'];
    }

    $alicuota = round(100.0 / $totalUnidades, 2);

    $stmtInsert = $pdo->prepare("
        INSERT INTO apartamentos (
            condominio_id, piso, codigo, tipo, propietario_nombre, telefono, email, password_hash, alicuota
        ) VALUES (
            ?, ?, ?, 'apartamento', ?, ?, ?, ?, ?
        )
        ON DUPLICATE KEY UPDATE 
            piso = VALUES(piso),
            alicuota = VALUES(alicuota)
    ");

    $generados = [];
    foreach ($unidades as $u) {
        $email = strtolower(str_replace(['-', ' '], '', $u['codigo'])) . '@condominio.com';
        $telefono = '0414-' . sprintf('%07d', mt_rand(1000000, 9999999));
        $stmtInsert->execute([
            $condominioId,
            $u['piso'],
            $u['codigo'],
            $u['nombre'],
            $telefono,
            $email,
            $passwordHash,
            $alicuota
        ]);
        $generados[] = $u['codigo'];
    }

    return [
        'success' => true,
        'total_generados' => count($generados),
        'alicuota_calculada' => $alicuota,
        'codigos' => $generados
    ];
}
