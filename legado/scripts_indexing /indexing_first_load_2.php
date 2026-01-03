<?php

/**
 * Google Indexing API - First Batch REAL Load (Batch 1)
 * 
 * This script handles the ACTUAL submission of the first batch of URLs (oldest 135)
 * to the Google Indexing API using URL_UPDATED notification type.
 * 
 * It reads sitemap.xml, sorts by date (oldest first), selects the first 135,
 * obtains an OAuth 2.0 token, and sends notifications one by one.
 * 
 * It logs detailed results to indexing_first_load_2.log and provides a visual summary.
 */

// Include Auth Module
require_once __DIR__ . '/indexing_api_auth.php';

// Configuration
define('SITEMAP_PATH', __DIR__ . '/../sitemap.xml');
define('LOG_FILE', __DIR__ . '/indexing_first_load_2.log');
define('BATCH_SIZE', 135);
define('BATCH_NUMBER', 1);

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

// Function to log processing result (Individual URL)
function logProcessing($url, $lastmod, $internalStatus, $httpCode, $message = '')
{
    $timestamp = date('Y-m-d H:i:s');
    $batchNum = BATCH_NUMBER;

    // Formatting: [Timestamp] Batch: X | URL: ... | Date: ... | Status: ... | HTTP: ... | Msg: ...
    $logEntry = sprintf(
        "[%s] Batch: %d | URL: %s | Date: %s | Status: %s | HTTP: %s | Msg: %s\n",
        $timestamp,
        $batchNum,
        $url,
        $lastmod,
        $internalStatus,
        $httpCode ?: 'N/A',
        str_replace(["\r", "\n"], " ", $message)
    );

    file_put_contents(LOG_FILE, $logEntry, FILE_APPEND);
}

/**
 * Submit URL to Google Indexing API
 */
function submitToIndexingAPI($url, $accessToken)
{
    $endpoint = 'https://indexing.googleapis.com/v3/urlNotifications:publish';

    $payload = [
        'url' => $url,
        'type' => 'URL_UPDATED'
    ];

    $jsonPayload = json_encode($payload);

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken,
        'Content-Length: ' . strlen($jsonPayload)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return [
            'success' => false,
            'http_code' => null,
            'message' => 'Connection Error: ' . $curlError
        ];
    }

    // Status 200 is success
    $success = ($httpCode == 200);

    $responseData = json_decode($response, true);
    $msg = 'OK';

    if (!$success) {
        if (isset($responseData['error']['message'])) {
            $msg = $responseData['error']['message'];
        } else {
            $msg = "HTTP Status $httpCode";
        }
    }

    return [
        'success' => $success,
        'http_code' => $httpCode,
        'message' => $msg
    ];
}


// --- MAIN EXECUTION ---

echo "<pre>"; // Use pre for browser output formatting parity with console if run in browser
echo "=== Google Indexing API - First Batch REAL Load (Batch 1) ===\n";
echo "Reading sitemap from: " . SITEMAP_PATH . "\n";

// 1. Get Authentication Token
echo "Authenticating with Google Service Account...\n";
$authError = null;
$accessToken = getIndexingAPIToken(null, $authError);

if (!$accessToken) {
    echo "❌ FATAL ERROR: Authentication Failed.\n";
    echo "Reason: " . $authError . "\n";
    die("Script execution stopped due to authentication failure.\n");
}
echo "✅ Authentication successful. Token obtained.\n\n";


// 2. Parse and Select URLs
$allUrls = parseSitemap(SITEMAP_PATH);
echo "Total URLs found in sitemap: " . count($allUrls) . "\n";

sortUrlsByDate($allUrls);
echo "URLs sorted by date (oldest first)\n";

// Select first 135
$batchUrls = array_slice($allUrls, 0, BATCH_SIZE);
$totalInBatch = count($batchUrls);

echo "\n--- BATCH 1 INFORMATION ---\n";
echo "Selected FIRST " . $totalInBatch . " oldest URLs for processing.\n";

if ($totalInBatch > 0) {
    $firstUrl = $batchUrls[0];
    $lastUrl = $batchUrls[$totalInBatch - 1];
    echo "First URL: " . $firstUrl['url'] . " (" . $firstUrl['lastmod'] . ")\n";
    echo "Last URL:  " . $lastUrl['url'] . " (" . $lastUrl['lastmod'] . ")\n";
}
echo "----------------------------\n\n";


// 3. Initialize Log
$logHeader = "=== First Batch REAL Load Execution - " . date('Y-m-d H:i:s') . " ===\n";
$logHeader .= "Target: First $totalInBatch URLs (Oldest)\n";
$logHeader .= "Action: POST URL_UPDATED\n\n";
file_put_contents(LOG_FILE, $logHeader, FILE_APPEND);


// 4. Process Batch
$successCount = 0;
$errorCount = 0;

foreach ($batchUrls as $index => $urlData) {
    $url = $urlData['url'];
    $lastmod = $urlData['lastmod'];

    // Display progress
    echo sprintf(
        "[%d/%d] Processing: %s (Date: %s) ... ",
        $index + 1,
        $totalInBatch,
        $url,
        $lastmod
    );

    // Flush output buffer to show progress in browser/console immediately
    if (ob_get_level() > 0) ob_flush();
    flush();

    // Call API
    $result = submitToIndexingAPI($url, $accessToken);

    // Log internal status
    $internalStatus = $result['success'] ? 'SUCCESS' : 'ERROR';
    $logMsg = $result['message'];

    // Write to log file
    logProcessing($url, $lastmod, $internalStatus, $result['http_code'], $logMsg);

    // Update counters and display result
    if ($result['success']) {
        $successCount++;
        echo "✅ OK (200)\n";
    } else {
        $errorCount++;
        echo "❌ ERROR (" . $result['http_code'] . "): " . $logMsg . "\n";

        // Handling Quota errors (429) - Optional: break loop or just log
        if ($result['http_code'] == 429) {
            echo "⚠️ QUOTA EXCEEDED. Stopping execution to prevent further errors.\n";
            break;
        }
    }
}


// 5. Final Summary (Visual & Log)

echo "\n";
echo "=============================================================\n";
echo "  RESUMEN DE EJECUCIÓN - PRIMER LOTE (BATCH 1 - REAL)\n";
echo "=============================================================\n\n";

// Datos Generales
echo "📋 DATOS GENERALES\n";
echo "   Script ejecutado: indexing_first_load_2.php\n";
echo "   Sitemap procesado: " . SITEMAP_PATH . "\n";
echo "   Total URLs en sitemap: " . count($allUrls) . "\n";
echo "   URLs procesadas en Batch 1: " . $totalInBatch . "\n\n";

// Resultados
echo "✅ RESULTADOS\n";
echo "   Procesadas: " . ($index + 1) . "/$totalInBatch\n"; // In case defined break
echo "   Éxitos (200 OK): $successCount\n";
echo "   Errores: $errorCount\n";
echo "   Archivo de log: " . LOG_FILE . "\n\n";

// Rango de Fechas
if ($totalInBatch > 0) {
    $firstUrl = $batchUrls[0];
    $lastUrl = $batchUrls[$totalInBatch - 1]; // Use original batch end for range logic logic, or actually processed?
    // Let's us the actually processed range if we broke early? No, let's keep it simple based on the batch definition as requested.

    echo "📅 RANGO DE FECHAS SELECCIONADO\n";
    echo "   URL más antigua: " . $firstUrl['lastmod'] . "\n";
    echo "   URL más reciente: " . $lastUrl['lastmod'] . "\n\n";
}

echo "=============================================================\n";
if ($errorCount == 0 && $successCount == $totalInBatch) {
    echo "✅ EJECUCIÓN EXITOSA COMPLETADA\n";
} elseif ($successCount > 0) {
    echo "⚠️  EJECUCIÓN COMPLETADA CON ERRORES PARCIALES\n";
} else {
    echo "❌ EJECUCIÓN FALLIDA (TODOS ERRORES)\n";
}
echo "=============================================================\n\n";
echo "</pre>";

// Write Log Summary
$summaryEntry = sprintf(
    "\n=== Summary: TotalBatch=%d | Processed=%d | Success=%d | Errors=%d ===\n",
    $totalInBatch,
    ($index + 1),
    $successCount,
    $errorCount
);

if ($totalInBatch > 0) {
    $firstUrl = $batchUrls[0];
    $lastUrl = $batchUrls[$totalInBatch - 1];
    $summaryEntry .= "Date Range: " . $firstUrl['lastmod'] . " to " . $lastUrl['lastmod'] . "\n";
}
$summaryEntry .= "\n";
file_put_contents(LOG_FILE, $summaryEntry, FILE_APPEND);
