<?php
$files = glob(__DIR__ . '/*.docx');
foreach ($files as $file) {
    echo "=== " . basename($file) . " ===\n\n";
    $content = file_get_contents($file);
    // Extract readable text using simple regex on the raw XML inside docx
    // docx is a zip, but we can try to find XML text nodes
    preg_match_all('/<w:t[^>]*>([^<]+)<\/w:t>/s', $content, $matches);
    if (!empty($matches[1])) {
        echo implode(' ', $matches[1]) . "\n";
    }
    echo "\n\n";
}
