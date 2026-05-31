<?php
/**
 * Cartel Playlist Fetcher
 * Bypasses basic security blocks using custom headers
 */

$url = "https://cartellive.vercel.app/api?token=cartels2&format=universal";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

// Use a real browser User-Agent to bypass security/bot gates
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

// Add standard headers
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: text/plain, */*',
    'Accept-Language: en-US,en;q=0.9',
    'Origin: https://cartellive.vercel.app',
    'Referer: https://cartellive.vercel.app/'
]);

$content = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if(curl_errno($ch)) {
    echo 'Error: ' . curl_error($ch);
    exit(1);
}

curl_close($ch);

if ($httpCode !== 200 || empty($content) || strpos($content, '#EXTM3U') === false) {
    echo "Fail: Received response code $httpCode or invalid M3U content.\n";
    exit(1);
}

// Clean up and format
$date = date('Y-m-d H:i:s');
$header = "#EXTM3U\n# Last Updated: $date\n# Source: Cartel Web\n\n";

// Remove existing header from source to avoid double headers
$content = preg_replace('/^#EXTM3U\R*/i', '', $content);
$finalPlaylist = $header . trim($content);

if (file_put_contents("playlist.m3u", $finalPlaylist)) {
    echo "Success: playlist.m3u updated at $date\n";
} else {
    echo "Error: Failed to write to file.\n";
    exit(1);
}
?>
