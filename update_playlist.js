// update_playlist.js
const fs = require('fs');

// CONFIGURATION
const WEB_URL = "https://cartellive.vercel.app/api?token=cartels2&format=universal";
const OUTPUT_FILE = "playlist.m3u";

async function update() {
  console.log("🚀 Starting playlist update...");
  try {
    const response = await fetch(WEB_URL, {
      headers: {
        'User-Agent': 'Cartel-Updater-Service/1.0',
        'Accept': 'text/plain'
      }
    });

    if (!response.ok) {
        throw new Error(`HTTP Error: ${response.status} - ${response.statusText}`);
    }

    const data = await response.text();
    
    // Safety check: Ensure it's a valid M3U
    if (!data.includes("#EXTM3U")) {
        throw new Error("Invalid playlist data received (Missing #EXTM3U)");
    }

    fs.writeFileSync(OUTPUT_FILE, data);
    console.log(`✅ Successfully saved to ${OUTPUT_FILE} (${(data.length / 1024).toFixed(2)} KB)`);
  } catch (err) {
    console.error("❌ Failed to fetch playlist:", err.message);
    process.exit(1);
  }
}

update();
