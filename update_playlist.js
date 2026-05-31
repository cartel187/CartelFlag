const fs = require('fs');
const https = require('https');

// --- Configuration ---
const APP_URL = 'https://cartellive.vercel.app/api?token=cartels2&format=universal';
const OUTPUT_FILE = 'playlist.m3u';
const SECRET_HEADER = 'workflow-sync-bot'; // Must match the one added to index.ts

console.log('🔄 Starting Playlist Sync...');

const options = {
    headers: {
        'User-Agent': 'Cartel-Sync-Bot/1.0',
        'X-Cartel-Secret': SECRET_HEADER
    }
};

https.get(APP_URL, options, (res) => {
    let data = '';

    if (res.statusCode !== 200) {
        console.error(`❌ Error: App returned status ${res.statusCode}`);
        process.exit(1);
    }

    res.on('data', (chunk) => { data += chunk; });

    res.on('end', () => {
        if (data.includes('#EXTM3U')) {
            fs.writeFileSync(OUTPUT_FILE, data);
            console.log(`✅ Success! Playlist saved to ${OUTPUT_FILE}`);
            console.log(`📊 Statistics: ${data.split('#EXTINF').length - 1} Channels captured.`);
        } else {
            console.error('❌ Error: Received invalid M3U data.');
            process.exit(1);
        }
    });

}).on('error', (err) => {
    console.error('❌ Fetch Error:', err.message);
    process.exit(1);
});
