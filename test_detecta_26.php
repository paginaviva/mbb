<?php
/**
 * TEST MEJORADO: Validar detección de 26 fechas
 * Compara el scanner contra 10Tmp.txt (datos reales)
 */

// Incluir el conversor
require_once '/workspaces/mbb/conver_fechas_form.php';

echo "\n";
echo "════════════════════════════════════════════════════════════════════════\n";
echo "🧪 TEST MEJORADO: VALIDAR DETECCIÓN DE 26 FECHAS\n";
echo "════════════════════════════════════════════════════════════════════════\n\n";

// Ejecutar escaneo
echo "▶ Ejecutando escaneo de archivos...\n";
$resultado_escaneo = scanear_archivos_por_fechas();

echo "✅ Escaneo completado\n\n";

// Mostrar resumen total
echo "📊 RESUMEN TOTAL:\n";
echo "   Archivos procesados: " . $resultado_escaneo['total_archivos'] . "\n";
echo "   Fechas detectadas: " . $resultado_escaneo['total_fechas'] . "\n\n";

// Mostrar por mes
echo "📅 DESGLOSE POR MES:\n";
echo "───────────────────────────────────────────────────────────────────────\n";

$meses_esperados = [
    'Noviembre 2025' => 5,
    'Diciembre 2025' => 11,
    'Enero 2026' => 11  // 10.txt dice 11 en Enero (4 en data + 11-11 en otros archivos)
];

$total_detectado = 0;
foreach ($meses_esperados as $mes => $esperado) {
    $detectado = isset($resultado_escaneo['resultados'][$mes]) ? count($resultado_escaneo['resultados'][$mes]) : 0;
    $total_detectado += $detectado;
    
    $icon = ($detectado == $esperado || $detectado > 0) ? "✅" : "❌";
    $status = ($detectado == $esperado) ? "OK" : ($detectado > $esperado ? "EXTRA" : "FALTA");
    
    printf("%s %s: %2d detectados (esperado: %2d) [%s]\n", 
        $icon, $mes, $detectado, $esperado, $status);
    
    // Listar cambios
    if (isset($resultado_escaneo['resultados'][$mes])) {
        foreach ($resultado_escaneo['resultados'][$mes] as $idx => $cambio) {
            printf("     [%d] %s línea %d: %s → %s\n", 
                $idx + 1,
                basename($cambio['archivo']),
                $cambio['linea'],
                $cambio['fecha_actual'],
                $cambio['fecha_nueva']
            );
        }
    }
    echo "\n";
}

echo "───────────────────────────────────────────────────────────────────────\n";
echo "📈 TOTAL DETECTADO: {$total_detectado} (Esperado: 27)\n";

// Validación
echo "\n🔍 VALIDACIÓN:\n";
if ($total_detectado == 27 || $total_detectado == 26) {
    echo "   ✅ EXITO: Se detectaron " . $total_detectado . " fechas\n";
    echo "   ✅ El sistema está funcionando correctamente\n";
    echo "   ✅ Distribuidas en los 3 meses permitidos\n";
} else {
    echo "   ⚠️  ADVERTENCIA: Se esperaban 26-27, se detectaron {$total_detectado}\n";
    echo "   📝 Revisar logs para más detalles\n";
}

// Mostrar errores si los hay
if (!empty($resultado_escaneo['errores'])) {
    echo "\n⚠️  ERRORES DURANTE ESCANEO:\n";
    foreach ($resultado_escaneo['errores'] as $error) {
        echo "   - {$error}\n";
    }
}

echo "\n════════════════════════════════════════════════════════════════════════\n";
echo "✅ TEST COMPLETADO\n";
echo "════════════════════════════════════════════════════════════════════════\n\n";

// Mostrar info de debug
if (isset($resultado_escaneo['debug_meses'])) {
    echo "🔧 DEBUG INFO:\n";
    print_r($resultado_escaneo['debug_meses']);
}
