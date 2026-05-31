<?php
// Configuration
$sourceUrl = "https://cartellive.vercel.app/api?token=cartels2&format=universal";
$outputFile = "playlist.m3u";

echo "Starting Update Process...\n";
echo "Fetching from: " . $sourceUrl . "\n";

// Use a real Browser User-Agent to avoid being blocked
$options = [
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n"
    ]
];

$context = stream_context_create($options);
$content = file_get_contents($sourceUrl, false, $context);

if ($content === false) {
    die("Error: Could not fetch content from source URL.\n");
}

// Basic validation to ensure we got an M3U file
if (strpos($content, "#EXTM3U") === false) {
    die("Error: The fetched content does not appear to be a valid M3U playlist.\n");
}

// Save to file
if (file_put_contents($outputFile, $content) !== false) {
    echo "Success: Playlist updated and saved to " . $outputFile . "\n";
} else {
    die("Error: Could not write to " . $outputFile . "\n");
}
?>
