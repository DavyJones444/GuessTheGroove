<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="wrapper">
    <h2 class="header-style">Musikquiz Player</h2>
    <script src="<?= ROOT_URL ?>js/jsQR.js"></script>
    <form method="get" id="urlForm" style="display: none;">
        <input type="text" name="url" value="<?= htmlspecialchars($viewData['songUrl']) ?>" >
        <button type="submit">Laden</button>
    </form>

    <div class="div-style">
        <button id="scanBtn" class="button" onclick="startScanning()">Scan QR Code</button>
    </div>

    <div id="qr-overlay" style="display: none;">
        <div>
            <p>QR-Code scannen...</p>
            <video id="qr-video" autoplay playsinline></video>
            <canvas id="qr-canvas" style="display:none;"></canvas>
            <button id="cancelBtn" onclick="cancelScanning()">Abbrechen</button>
        </div>
    </div>

    <div id="player-container"></div>

    <div class="div-style" id="spotify-button-div" style="margin-top:20px; display:none;">
        <div style="margin-top: 10px;">
            <button id="experimentalBtn" class="button">❌ Bei Fehler hier klicken</button>
        </div>
    </div>

    <div class="div-style" id="spotify-embed-div" style="margin-top:20px; display:none;">
        <iframe id="spotify-embed" style="border-radius:12px" 
                src="" 
                width="400px" 
                height="100" 
                frameborder="0" 
                allowtransparency="true" 
                allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture">
        </iframe>
    </div>

    <div class="div-style">
        <button id="startStopBtn" class="button" style="display:none;">▶️ Start</button>
    </div>

    <div id="loading-overlay" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center;">
        <div class="spinner" style="border: 4px solid #f3f3f3; border-top: 4px solid #4CAF50; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite;"></div>
    </div>

    <style>
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>

</div>

<script src="<?= ROOT_URL ?>js/jsQR.js"></script>
<script>
    // PHP Variablen
    const service = "<?= $viewData['service'] ?>";
    const songUrl = "<?= $viewData['songUrl'] ?>";
    const token = "<?= $viewData['token'] ?>";
    
    // UI Elemente
    const btn = document.getElementById('startStopBtn');
    const loadingOverlay = document.getElementById('loading-overlay');
    let isPlaying = false;

    // --- SCANNER SETUP ---
    let videoElement = null;
    let canvasElement = null;
    let canvasContext = null;
    let scanActive = false;
    let videoStream = null;

    async function startScanning() {
        try {
            document.getElementById('qr-overlay').style.display = 'flex';
            videoElement = document.getElementById('qr-video');
            canvasElement = document.getElementById('qr-canvas');
            canvasContext = canvasElement.getContext('2d');

            // Kamera starten (Rückkamera bevorzugt)
            const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } });
            videoStream = stream;
            videoElement.srcObject = stream;
            
            // Wichtig für iOS:
            videoElement.setAttribute("playsinline", true); 

            videoElement.onloadedmetadata = () => {
                videoElement.play();
                canvasElement.width = videoElement.videoWidth;
                canvasElement.height = videoElement.videoHeight;
                scanActive = true;
                requestAnimationFrame(tick);
            };
        } catch (err) {
            alert("Kamera-Fehler: " + err.message);
            cancelScanning();
        }
    }

    function tick() {
        if (!scanActive) return;

        if (videoElement.readyState === videoElement.HAVE_ENOUGH_DATA) {
            canvasContext.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);
            const imageData = canvasContext.getImageData(0, 0, canvasElement.width, canvasElement.height);
            
            if (typeof jsQR === 'undefined') return; // Sicherheitscheck

            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: "attemptBoth"
            });

            if (code && code.data && code.data.trim() !== "") {
                const scannedData = code.data.trim();
                console.log("Gescannter Code:", scannedData);

                // --- HIER IST DIE KORRIGIERTE LOGIK ---
                let extractedId = null;

                // 1. Suche nach "id=123" (fängt play.php?id=123)
                const paramMatch = scannedData.match(/[?&]id=(\d+)/);
                
                // 2. Suche nach "/123" am Ende (fängt gtg.luda-vision.de/123)
                // Wir prüfen explizit auf DEINE Domain, um Verwechslungen zu vermeiden
                const isMyDomain = scannedData.includes('gtg.luda-vision.de') || scannedData.includes('localhost');
                const pathMatch = scannedData.match(/\/(\d+)(\/|$)/); // Zahl gefolgt von Slash oder Ende

                if (paramMatch) {
                    extractedId = paramMatch[1];
                } else if (isMyDomain && pathMatch) {
                    extractedId = pathMatch[1];
                } else if (/^\d+$/.test(scannedData)) {
                    extractedId = scannedData; // Einfach nur eine Zahl gescannt
                }

                if (extractedId) {
                    stopScanning();
                    // WICHTIG: Wir leiten LOKAL weiter, egal was im QR Code stand!
                    // window.location.search ersetzt nur den "?..." Teil der URL
                    window.location.url = "https://gtg.luda-vision.de/" + extractedId;
                    //window.location.search = '?id=' + extractedId;
                    return;
                }

                // 3. Fallback: Externe Dienste (Spotify/Deezer/YouTube Links)
                // Nur wenn KEINE ID gefunden wurde und es NICHT unsere Domain ist
                if (scannedData.includes('http') && !isMyDomain) {
                    stopScanning();
                    document.querySelector('input[name="url"]').value = scannedData;
                    document.getElementById('urlForm').submit();
                    return;
                }
                // --- LOGIK ENDE ---
            }
        }
        requestAnimationFrame(tick);
    }

    function stopScanning() {
        scanActive = false;
        const overlay = document.getElementById('qr-overlay');
        if (overlay) overlay.style.display = 'none';
        
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
        }
    }

    function cancelScanning() {
        stopScanning();
    }

    // --- PLAYER LOGIK ---

    function toggleButton() {
        isPlaying = !isPlaying;
        btn.textContent = isPlaying ? '⏹️ Stopp' : '▶️ Start';
    }

    // Helfer für Titel-Bereinigung
    function cleanTitle(title) {
        return title.replace(/\s*\([^)]*\)/g, '').trim();
    }

    function loadYouTube(url) {
        const container = document.getElementById('player-container');
        if(loadingOverlay) loadingOverlay.style.display = 'flex';
        container.innerHTML = '';
        if(btn) btn.style.display = 'none';

        const audio = new Audio(`/api/youtube/audio?url=${encodeURIComponent(url)}`);
        audio.id = "ytAudio";
        
        audio.addEventListener('canplaythrough', () => {
            if(loadingOverlay) loadingOverlay.style.display = 'none';
            container.appendChild(audio);
            if(btn) {
                btn.style.display = 'inline-block';
                btn.textContent = '▶️ Start';
                btn.onclick = () => {
                    if (audio.paused) { audio.play(); btn.textContent = '⏸️ Stop'; } 
                    else { audio.pause(); audio.currentTime = 0; btn.textContent = '▶️ Start'; }
                };
            }
            audio.addEventListener('ended', () => { if(btn) btn.textContent = '▶️ Start'; });
        });

        audio.addEventListener('error', () => {
            if(loadingOverlay) loadingOverlay.style.display = 'none';
            alert("Fehler beim Laden des Audios.");
        });
    }

    function loadDeezer(url) {
        const match = url.match(/track\/(\d+)/);
        if (!match) return alert("Ungültiger Deezer-Link");
        
        if(loadingOverlay) loadingOverlay.style.display = 'flex';

        fetch(`/api/deezer/track?id=${match[1]}`)
            .then(res => res.json())
            .then(data => {
                if(loadingOverlay) loadingOverlay.style.display = 'none';
                if (!data.preview) return alert("Keine Vorschau verfügbar.");

                const container = document.getElementById('player-container');
                container.innerHTML = `<audio id="deezerAudio" src="${data.preview}" preload="auto"></audio>`;
                const audio = document.getElementById('deezerAudio');
                
                if(btn) {
                    btn.style.display = 'inline-block';
                    btn.textContent = '▶️ Start';
                    btn.onclick = () => {
                        if (audio.paused) { audio.play(); btn.textContent = '⏸️ Stop'; } 
                        else { audio.pause(); audio.currentTime = 0; btn.textContent = '▶️ Start'; }
                    };
                }
                audio.addEventListener('ended', () => { if(btn) btn.textContent = '▶️ Start'; });
            })
            .catch(err => {
                if(loadingOverlay) loadingOverlay.style.display = 'none';
                alert("Fehler bei Deezer.");
            });
    }

    async function searchDeezerFromSpotify(spotifyUrl) {
        if(loadingOverlay) loadingOverlay.style.display = 'flex';
        
        const match = spotifyUrl.match(/track\/([a-zA-Z0-9]+)/);
        if (!match) {
            if(loadingOverlay) loadingOverlay.style.display = 'none';
            return alert("Ungültiger Spotify-Link");
        }

        try {
            const res = await fetch(`/api/spotify/track?id=${match[1]}`);
            const data = await res.json();
            
            if (data.error) throw new Error(data.error);

            const title = data.name;
            const artist = data.artists[0].name;

            // Suche bei Deezer
            fetch(`/api/deezer/search?q=track:"${encodeURIComponent(cleanTitle(title))}" artist:"${encodeURIComponent(artist)}"`)
                .then(res => res.json())
                .then(searchData => {
                    if (searchData.data && searchData.data.length > 0) {
                        // Einfache Logik: Nimm den ersten Treffer oder suche genauer
                        const track = searchData.data[0]; 
                        loadDeezer(`https://www.deezer.com/track/${track.id}`);
                    } else {
                        alert("Song nicht auf Deezer gefunden.");
                        if(loadingOverlay) loadingOverlay.style.display = 'none';
                    }
                });
        } catch (err) {
            alert("Spotify Fehler: " + err.message);
            if(loadingOverlay) loadingOverlay.style.display = 'none';
        }
    }

    // --- INITIALISIERUNG ---
    if (songUrl) {
        if(btn) btn.style.display = 'inline-block';
    }

    if (service === 'spotify') {
        const uiDivs = ['spotify-button-div', 'spotify-text-div', 'spotify-embed-div'];
        uiDivs.forEach(id => {
            const el = document.getElementById(id);
            if(el) el.style.display = 'flex';
        });
        
        const embed = document.getElementById('spotify-embed');
        const match = songUrl.match(/track\/([a-zA-Z0-9]+)/);
        if (match && embed) {
            embed.src = `https://open.spotify.com/embed/track/${match[1]}?utm_source=generator`;
            embed.style.display = 'none'; // Standardmäßig verstecken
            searchDeezerFromSpotify(songUrl);
        }
    } else if (service === 'youtube') {
        loadYouTube(songUrl);
    } else if (service === 'deezer') {
        loadDeezer(songUrl);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const expBtn = document.getElementById('experimentalBtn');
        const spotifyEmbed = document.getElementById('spotify-embed');
        
        if (expBtn) {
            expBtn.addEventListener('click', () => {
                if (expBtn.textContent.includes("Nicht Experimenteller")) {
                    if(spotifyEmbed) spotifyEmbed.style.display = 'flex';
                    if(btn) btn.style.display = 'none';
                    expBtn.textContent = "🔬 Experimenteller Modus (Deezer-Vorschau)";
                } else {
                    if(spotifyEmbed) spotifyEmbed.style.display = 'none';
                    if(btn) btn.style.display = 'inline-block';
                    expBtn.textContent = "❌ Nicht Experimenteller Modus";
                    searchDeezerFromSpotify(songUrl);
                }
            });
        }
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>