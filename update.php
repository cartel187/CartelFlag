<?php
/**
 * CartelFetch - Multi-Source & Combined M3U Fetcher
 */

// CONFIGURATION: Add your playlists here
$sources = [
    "stalk.m3u" => "https://saarstalk.cartel187.workers.dev/playlist.m3u8",
    "playlist2.m3u" => "https://ais-dev-mjkihhup7zbs3p6zkrdvkj-313480061426.asia-southeast1.run.app/streamflex.m3u",
    "playlist3.m3u" => "https://play.ksrtech.fun/playlist.php?token=fb5198ff3896583e4c7d92aee27400fa",
];

$combinedFileName = "all_combined.m3u";
$combinedContent = "#EXTM3U\n"; // Start the combined file with the required header

foreach ($sources as $fileName => $url) {
    echo "[*] Fetching: $fileName...\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 100);
    curl_setopt($ch, CURLOPT_ENCODING, ""); // Support GZIP

    // Stealth Headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: OTTNavigator/1.7.1.2 (Linux; Android 11) ExoPlayerLib/2.14.2',
        'Accept: */*',
        'Connection: keep-alive'
    ]);

    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && stripos($content, '#EXTINF') !== false) {
        // 1. Save the individual file
        file_put_contents($fileName, $content);
        echo "[+] Successfully saved $fileName\n";

        // 2. Process for combined file
        // We strip the #EXTM3U header from individual files so the combined file only has one header at the top
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $line = trim($line);
            // Skip the header line and empty lines
            if (empty($line) || stripos($line, '#EXTM3U') !== false) {
                continue;
            }
            $combinedContent .= $line . "\n";
        }
    } else {
        echo "[-] Failed to fetch $fileName (HTTP $httpCode or No Channels Found)\n";
    }
}

// Save the combined master list
if (strlen($combinedContent) > 20) {
    file_put_contents($combinedFileName, $combinedContent);
    echo "[!] All playlists merged into $combinedFileName\n";
} else {
    echo "[-] Merge failed: No valid content was fetched.\n";
}
?>
