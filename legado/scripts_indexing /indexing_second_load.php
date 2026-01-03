<?php

/**
 * Google Indexing API - Second Batch Load
 * 
 * This script handles the second batch of URLs (remaining after first 135) 
 * for the initial load to Google Indexing API. It reads sitemap.xml, sorts 
 * URLs by date (oldest first), selects URLs from position 136 onwards, and 
 * logs them for processing.
 * 
 * This script is independent and should be executed manually.
 * Do NOT call from dashboard_gestion.php or generate_sitemap.php.
 */

// Configuration
define('SITEMAP_PATH', __DIR__ . '/../sitemap.xml');
define('LOG_FILE', __DIR__ . '/indexing_second_load.log');
define('FIRST_BATCH_SIZE', 135);
define('BATCH_NUMBER', 2);

// Function to parse sitemap.xml and extract URLs with dates
function parseSitemap($sitemapPath)
{
    if (!file_exists($sitemapPath)) {
        die("ERROR: Sitemap file not found at: $sitemapPath\n");
    }

    $xml = simplexml_load_file($sitemapPath);
    if ($xml === false) {
        die("ERROR: Failed to parse sitemap XML\n");
    }

    $urls = [];

    foreach ($xml->url as $urlNode) {
        $loc = (string)$urlNode->loc;
        $lastmod = isset($urlNode->lastmod) ? (string)$urlNode->lastmod : '1970-01-01';

        $urls[] = [
            'url' => $loc,
            'lastmod' => $lastmod
        ];
    }

    return $urls;
}

// Function to sort URLs by date (oldest first)
function sortUrlsByDate(&$urls)
{
    usort($urls, function ($a, $b) {
        return strcmp($a['lastmod'], $b['lastmod']);
    });
}

// Function to log processing result
function logProcessing($url, $lastmod, $status, $message = '')
{
    $timestamp = date('Y-m-d H:i:s');
    $batchNum = BATCH_NUMBER;

    $logEntry = sprintf(
        "[%s] Batch: %d | URL: %s | Date: %s | Status: %s | Message: %s\n",
        $timestamp,
        $batchNum,
        $url,
        $lastmod,
        $status,
        $message
    );

    file_put_contents(LOG_FILE, $logEntry, FILE_APPEND);
}

// Main execution
echo "=== Google Indexing API - Second Batch Load ===\n";
echo "Reading sitemap from: " . SITEMAP_PATH . "\n";

// Parse sitemap
$allUrls = parseSitemap(SITEMAP_PATH);
echo "Total URLs found in sitemap: " . count($allUrls) . "\n";

// Sort by date (oldest first)
sortUrlsByDate($allUrls);
echo "URLs sorted by date (oldest first)\n";

// Select remaining URLs (from position 136 onwards)
$batchUrls = array_slice($allUrls, FIRST_BATCH_SIZE);
$totalRemaining = count($batchUrls);

echo "\n--- BATCH 2 INFORMATION ---\n";
echo "Skipped first " . FIRST_BATCH_SIZE . " URLs (already processed in Batch 1)\n";
echo "Selected " . $totalRemaining . " URLs for Batch " . BATCH_NUMBER . "\n";

// Show first URL of this batch for verification
if ($totalRemaining > 0) {
    $firstUrl = $batchUrls[0];
    echo "Starting from URL #136: " . $firstUrl['url'] . " (Date: " . $firstUrl['lastmod'] . ")\n";
}
echo "----------------------------\n\n";

// Initialize log file with header
$logHeader = "=== Second Batch Load Execution - " . date('Y-m-d H:i:s') . " ===\n";
$logHeader .= "Starting from position 136 in sitemap (after first 135 URLs)\n";
$logHeader .= "Total URLs in this batch: " . $totalRemaining . "\n\n";
file_put_contents(LOG_FILE, $logHeader, FILE_APPEND);

// Process each URL (placeholder for actual API call)
$successCount = 0;
$errorCount = 0;

foreach ($batchUrls as $index => $urlData) {
    $url = $urlData['url'];
    $lastmod = $urlData['lastmod'];

    echo sprintf(
        "[%d/%d] Processing: %s (Date: %s)\n",
        $index + 1,
        count($batchUrls),
        $url,
        $lastmod
    );

    // Placeholder for actual Google Indexing API call
    // In future implementation, this is where the API call will be made
    // For now, we just log the URL as "SELECTED_FOR_PROCESSING"

    try {
        // Simulate successful selection
        logProcessing($url, $lastmod, 'SELECTED_FOR_PROCESSING', 'URL queued for initial load');
        $successCount++;
    } catch (Exception $e) {
        logProcessing($url, $lastmod, 'ERROR', $e->getMessage());
        $errorCount++;
    }
}

// Summary
echo "\n";
echo "=============================================================\n";
echo "  RESUMEN DE EJECUCIÓN - SEGUNDO LOTE (BATCH 2)\n";
echo "=============================================================\n\n";

// Datos Generales
echo "📋 DATOS GENERALES\n";
echo "   Script ejecutado: indexing_second_load.php\n";
echo "   Sitemap procesado: " . SITEMAP_PATH . "\n";
echo "   Total URLs en sitemap: " . count($allUrls) . "\n";
echo "   URLs seleccionadas para Batch 2: " . count($batchUrls) . " (continuación después de las primeras 135)\n\n";

// Resultados
echo "✅ RESULTADOS\n";
echo "   URLs procesadas: $successCount/" . count($batchUrls) . "\n";
echo "   Registradas exitosamente: $successCount\n";
echo "   Errores: $errorCount\n";
echo "   Archivo de log: " . LOG_FILE . "\n\n";

// Rango de fechas
if (count($batchUrls) > 0) {
    $firstUrl = $batchUrls[0];
    $lastUrl = $batchUrls[count($batchUrls) - 1];

    echo "📅 RANGO DE FECHAS PROCESADAS\n";
    echo "   URL más antigua del lote: " . $firstUrl['lastmod'] . "\n";
    echo "   (" . basename($firstUrl['url']) . ")\n";
    echo "   URL más reciente del lote: " . $lastUrl['lastmod'] . "\n";
    echo "   (" . basename($lastUrl['url']) . ")\n\n";
}

// Observaciones
echo "📌 OBSERVACIONES IMPORTANTES\n";
echo "   ✓ Ordenamiento correcto: Las URLs se procesaron en orden cronológico\n";
echo "     ascendente (de más antigua a más reciente).\n";
echo "   ✓ Continuidad: Este lote comienza desde la posición 136, inmediatamente\n";
echo "     después del último elemento del Batch 1.\n";
echo "   ✓ Estado del proceso: Todas las URLs fueron marcadas como\n";
echo "     SELECTED_FOR_PROCESSING en el log, listas para cuando se implemente\n";
echo "     la llamada real a la API de Google Indexing.\n\n";

echo "=============================================================\n";
if ($errorCount == 0) {
    echo "✅ EJECUCIÓN EXITOSA COMPLETADA\n";
} else {
    echo "⚠️  EJECUCIÓN COMPLETADA CON ERRORES\n";
}
echo "=============================================================\n\n";

// Log summary
$summaryEntry = sprintf(
    "\n=== Summary: Total=%d | Success=%d | Errors=%d ===\n",
    count($batchUrls),
    $successCount,
    $errorCount
);

if (count($batchUrls) > 0) {
    $firstUrl = $batchUrls[0];
    $lastUrl = $batchUrls[count($batchUrls) - 1];
    $summaryEntry .= "Date Range: " . $firstUrl['lastmod'] . " to " . $lastUrl['lastmod'] . "\n";
}

$summaryEntry .= "\n";
file_put_contents(LOG_FILE, $summaryEntry, FILE_APPEND);

echo "Second batch load completed.\n";
