<?php

/**
 * Google Indexing API - First Batch Load
 * 
 * This script handles the first batch of URLs (oldest 135) for the initial
 * load to Google Indexing API. It reads sitemap.xml, sorts URLs by date
 * (oldest first), selects the first 135, and logs them for processing.
 * 
 * This script is independent and should be executed manually.
 * Do NOT call from dashboard_gestion.php or generate_sitemap.php.
 */

// Configuration
define('SITEMAP_PATH', __DIR__ . '/../sitemap.xml');
define('LOG_FILE', __DIR__ . '/indexing_first_load.log');
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
echo "=== Google Indexing API - First Batch Load ===\n";
echo "Reading sitemap from: " . SITEMAP_PATH . "\n";

// Parse sitemap
$allUrls = parseSitemap(SITEMAP_PATH);
echo "Total URLs found in sitemap: " . count($allUrls) . "\n";

// Sort by date (oldest first)
sortUrlsByDate($allUrls);
echo "URLs sorted by date (oldest first)\n";

// Select first 135 URLs
$batchUrls = array_slice($allUrls, 0, BATCH_SIZE);
echo "Selected " . count($batchUrls) . " URLs for Batch " . BATCH_NUMBER . "\n\n";

// Initialize log file with header
$logHeader = "=== First Batch Load Execution - " . date('Y-m-d H:i:s') . " ===\n";
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
echo "\n=== Execution Summary ===\n";
echo "Total URLs processed: " . count($batchUrls) . "\n";
echo "Successfully logged: $successCount\n";
echo "Errors: $errorCount\n";
echo "Log file: " . LOG_FILE . "\n";

// Log summary
$summaryEntry = sprintf(
    "\n=== Summary: Total=%d | Success=%d | Errors=%d ===\n\n",
    count($batchUrls),
    $successCount,
    $errorCount
);
file_put_contents(LOG_FILE, $summaryEntry, FILE_APPEND);

echo "\nFirst batch load completed.\n";
