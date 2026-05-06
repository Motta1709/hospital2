<?php
$files = glob(__DIR__ . '/*.docx');
foreach ($files as $file) {
    echo "=== " . basename($file) . " ===\n\n";
    $zip = new ZipArchive();
    if ($zip->open($file) === true) {
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $nodes = $xpath->query('//w:p');
        foreach ($nodes as $n) {
            $text = '';
            $tNodes = $xpath->query('.//w:t', $n);
            foreach ($tNodes as $t) {
                $text .= $t->textContent;
            }
            if (trim($text)) {
                echo $text . "\n";
            }
        }
    }
    echo "\n\n";
}

