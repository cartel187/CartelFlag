<?php
/**
 * ＣΛＲＴΞＬ ＬＩＶΞ - Scraper Aggregator
 * Replicates the web app fetching logic for GitHub Actions / PHP Servers.
 */

header('Content-Type: text/plain; charset=utf-8');

$author = "cartel187";
$telegram = "https://t.me/cartel187";
$date = date("Y-m-d H:i:s");

// --- CONFIGURATION: Source URLs ---
$sources = [
    "FanCode" => "https://raw.githubusercontent.com/doctor-8trange/zyphx8/refs/heads/main/data/fancode.json",
    "ICC" => "https://raw.githubusercontent.com/doctor-8trange/nexphi0/refs/heads/main/data/icc.json",
    "SonyEvents" => "https://raw.githubusercontent.com/doctor-8trange/zyphora/refs/heads/main/data/sony.m3u",
    "SonyLIV" => "https://raw.githubusercontent.com/cartel187/CartelSony/refs/heads/main/SonyLiv.m3u",
    "CricHD" => "https://raw.githubusercontent.com/srhady/crichd-speical-live-event/refs/heads/main/playlist.m3u",
    "FIFA" => "https://raw.githubusercontent.com/srhady/fifaplus/refs/heads/main/fifa_live.m3u",
    "StarSports" => "https://raw.githubusercontent.com/alex4528y/m3u/refs/heads/main/jtv.m3u",
    "MasterFetch" => "https://raw.githubusercontent.com/cartel187/Cartelfetch/refs/heads/main/playlist.m3u"
];

$output = "#EXTM3U\n";
$output .= "#EXTM3U x-tvg-url=\"https://raw.githubusercontent.com/mitthu786/mitthu786/main/jio/epg.xml.gz\"\n";
$output .= "# ==========================================\n";
$output .= "#   ✨  ＣΛＲＴΞＬ ＬＩＶΞ (AUTO)  ✨   \n";
$output .= "# ==========================================\n";
$output .= "# Update: $date\n";
$output .= "# Telegram: $telegram\n";
$output .= "# Creator: $author\n\n";

function fetch($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . "?t=" . time());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36");
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

// 1. Process FanCode (JSON)
$fcData = json_decode(fetch($sources["FanCode"]), true);
if (isset($fcData['matches'])) {
    foreach ($fcData['matches'] as $match) {
        if ($match['status'] === "LIVE" && !empty($match['STREAMING_CDN']['Primary_Playback_URL'])) {
            $stream = $match['STREAMING_CDN']['Primary_Playback_URL'];
            $stream = str_replace("in-mc-plive.fancode.com", "dai-fancode.pages.dev", $stream);
            $output .= "#EXTINF:-1 tvg-id=\"{$match['match_id']}\" tvg-logo=\"{$match['image']}\" group-title=\"𝗙𝗔𝗡𝗖𝗢𝗗𝗘\" group-logo=\"https://ik.imagekit.io/yjtx9nh9y/vecteezy_fancode-app-icon-on-transparent-background_69146538.png\",{$match['title']}\n";
            $output .= "{$stream}|User-Agent=Hotstar;in.startv.hotstar\n\n";
        }
    }
}

// 2. Process ICC (JSON)
$iccData = json_decode(fetch($sources["ICC"]), true);
if (isset($iccData['live'])) {
    foreach ($iccData['live'] as $item) {
        if (isset($item['playback']['playbackUrl'])) {
            $pb = $item['playback'];
            $output .= "#EXTINF:-1 tvg-logo=\"https://ik.imagekit.io/yjtx9nh9y/62823e9932b32411608aa856.png\" group-title=\"𝗜🇨🇨 𝗧𝗩\",{$item['title']}\n";
            $output .= "#KODIPROP:inputstream.adaptive.license_type=com.clearkey.alpha\n";
            $output .= "#KODIPROP:inputstream.adaptive.license_key=" . json_encode($pb['keys']['jwk']) . "\n";
            $output .= "{$pb['playbackUrl']}\n\n";
        }
    }
}

// 3. Process Sony, CricHD, FIFA, StarSports (M3U)
$m3uSources = ["SonyEvents", "SonyLIV", "CricHD", "FIFA", "StarSports", "MasterFetch"];
foreach ($m3uSources as $srcKey) {
    $data = fetch($sources[$srcKey]);
    $lines = explode("\n", $data);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, "#EXTM3U") === 0) continue;
        if (strpos($line, "#") === 0) {
            // Force branding for external M3U sources
            if (strpos($line, "#EXTINF") === 0) {
                // Remove existing groups to overwrite with our style
                $line = preg_replace('/group-title="[^"]+"/', '', $line);
                $line = str_replace("#EXTINF:-1", "#EXTINF:-1 group-title=\"$srcKey\"", $line);
            }
            $output .= $line . "\n";
        } else {
            $output .= $line . "\n\n";
        }
    }
}

// Save to file
file_put_contents("playlist.m3u", $output);
echo "Playlist updated successfully.";
