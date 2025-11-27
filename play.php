<?php
require __DIR__ . '/lib/db.php';
session_start();

// Initialisierung
$songUrl = '';

// 1. NEUE LOGIK: Prüfen, ob eine ID übergeben wurde
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $cardId = $_GET['id'];
    
    // ANPASSEN: Hier musst du deinen Tabellennamen und Spaltennamen eintragen
    // Ich gehe davon aus, dass $pdo in 'lib/db.php' definiert wurde.
    // Beispiel: Tabelle heißt 'cards', Spalte mit dem Link heißt 'song_url'
    try {
        $stmt = $pdo->prepare("SELECT songlink FROM cards WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $cardId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $songUrl = $result['songlink'];
        } else {
            // Optional: Fehlermeldung, wenn ID nicht gefunden wurde
            echo "<script>alert('Karte mit ID $cardId nicht gefunden.');</script>";
        }
    } catch (PDOException $e) {
        die("Datenbankfehler: " . $e->getMessage());
    }
} else {
    // Fallback: Wenn keine ID, schaue ob direkt eine URL übergeben wurde (alte Methode)
    $songUrl = $_GET['url'] ?? '';
}

// --- Ab hier läuft alles fast wie bisher, da $songUrl nun gefüllt ist ---

    // Hitster-Link erkennen (mit beliebiger Länderkennung)
    if (preg_match('/hitstergame\.com\/[a-z]{2}(-[a-z]{2})?\/\d{5}/i', $songUrl)) {
        // Verhindere weiteres Rendering
        $hitsterId = preg_replace('/^.*\/(\d{5})$/', '$1', $songUrl);
        $hitsterLang = preg_replace('/^.*hitstergame\.com\/([a-z]{2}(?:-[a-z]{2})?)\/\d{5}$/i', '$1', $songUrl);
        $hitsterLang = htmlspecialchars($hitsterLang);
        $hitsterId = htmlspecialchars($hitsterId);

        echo <<<HTML
        <!DOCTYPE html>
        <html lang="de">
        <head>
            <meta charset="UTF-8">
            <title>Hitster-Link erkannt</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body {
                    font-family: sans-serif;
                    text-align: center;
                    padding: 40px;
                }
                .modal {
                    background: #fff;
                    padding: 30px;
                    border-radius: 12px;
                    box-shadow: 0 0 20px rgba(0,0,0,0.2);
                    display: inline-block;
                }
                button {
                    margin-top: 20px;
                    padding: 10px 20px;
                    background-color: #4CAF50;
                    color: white;
                    border: none;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 16px;
                }
            </style>
        </head>
        <body>
            <div class="modal">
                <h2>Hitster-Link erkannt</h2>
                <p>Dieser Link gehört zur Hitster-App.<br>Bitte öffne ihn in der App.</p>
                <button onclick="reloadPage()">Okay</button>
            </div>

            <script>
                function reloadPage() {
                    window.location.href = window.location.pathname;
                }

                function openHitsterAppOrRedirect() {
                    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
                    if (!isMobile) return;

                    const deeplink = 'hitster://$hitsterLang/$hitsterId';
                    // Hier ggf. die IP anpassen oder dynamisch machen
                    const fallbackUrl = window.location.href.split('?')[0]; 
                    const now = Date.now();

                    // Wenn App nicht geöffnet → redirect zur Webseite
                    const timeout = setTimeout(() => {
                        if (Date.now() - now < 2000) {
                            window.location.href = fallbackUrl;
                        }
                    }, 1500);

                    window.location.href = deeplink;
                }

                openHitsterAppOrRedirect();
            </script>
        </body>
        </html>
        HTML;
            exit;
    }

$service = '';
$token = $_SESSION['spotify_token'] ?? null;

// Dienste erkennen
if (strpos($songUrl, 'youtube.com') !== false || strpos($songUrl, 'youtu.be') !== false) {
    $service = 'youtube';
} elseif (strpos($songUrl, 'spotify.com') !== false || strpos($songUrl, 'spotify.com') !== false) {
    // Habe spotify.com hinzugefügt, falls der Link direkt aus der DB kommt und nicht via Proxy
    $service = 'spotify';
} elseif (strpos($songUrl, 'deezer.com') !== false) {
    $service = 'deezer';
}

$title = "Spielen";

include 'header.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.js"></script>
    <style>
        #startStopBtn {
            font-size: 24px;
            padding: 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        #qr-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 100;
            justify-content: center;
            align-items: center;
        }

        #qr-overlay div {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: white;
        }

        #cancelBtn {
            margin-top: 20px;
            padding: 10px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        video {
            width: 100%;
            max-width: 400px;
        }

        @media (max-width: 600px) {
            body { font-size: 14px; }
        }

        #loading-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .spinner {
            border: 10px solid #f3f3f3;
            border-top: 10px solid #4CAF50;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

    </style>
</head>
<body>
    <div class="wrapper">
        <h2 class="header-style">Musikquiz Player</h2>
        
        <form method="get" id="urlForm" style="display: none;">
            <input type="text" name="url" value="<?= htmlspecialchars($songUrl) ?>" placeholder="YouTube, Spotify oder Deezer Link" size="60">
            <button type="submit">Laden</button>
        </form>

        <div id="loading-overlay" style="display:none;">
            <div class="spinner"></div>
        </div>

        <div class="div-style">
            <button id="scanBtn" class="button" onclick="startScanning()">Scan QR Code</button>
        </div>

        <div id="qr-overlay">
            <div>
            <p>QR-Code scannen...</p>
            <video id="qr-video" autoplay playsinline></video>
            <canvas id="qr-canvas" style="display:none;"></canvas>
            <button id="cancelBtn" onclick="cancelScanning()">Abbrechen</button>
            </div>
        </div>

        <div id="player-container">
        </div>
        <div class="div-style" id="spotify-text-div" style="margin-top:20px; display:none;">
            Der Player nutzt standardmäßig den Experimentellen Modus. <br>
            Dabei werden Titel und Interpret aus Spotify gelesen und bei Deezer gesucht.<br>
            Dadurch ist ein Abspielen ohne Titelanzeige möglich.<br>
            Nutze bei Fehlern den Nicht Experimentellen Modus.
        </div>
        <div class="div-style" id="spotify-button-div" style="margin-top:20px; display:none;">
            <div style="margin-top: 10px;">
                <button id="experimentalBtn" class="button"
                >❌ Nicht Experimenteller Modus</button>
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
            <button id="startStopBtn" style="display:none;">▶️ Start</button>
        </div>
    </div>

    <script>
        const service = "<?= $service ?>";
        const songUrl = "<?= $songUrl ?>";
        const token = "<?= $token ?>";
        const btn = document.getElementById('startStopBtn');
        let isPlaying = false;
        let player, deviceId;

        function toggleButton() {
            isPlaying = !isPlaying;
            btn.textContent = isPlaying ? '⏹️ Stopp' : '▶️ Start';
        }

        function loadYouTube(url) {
            const container = document.getElementById('player-container');
            const btn = document.getElementById('startStopBtn');
            const loadingOverlay = document.getElementById('loading-overlay');

            // Zeige Ladeanimation
            loadingOverlay.style.display = 'flex';

            // Setze den Player zurück
            container.innerHTML = '';
            btn.style.display = 'none';

            // Neues Audio-Element laden
            const audio = new Audio(`proxy/youtube_audio_proxy.php?url=${encodeURIComponent(url)}`);
            audio.id = "ytAudio";
            audio.preload = "auto";

            // Wenn bereit: UI anzeigen
            audio.addEventListener('canplaythrough', () => {
                loadingOverlay.style.display = 'none';
                container.innerHTML = '';
                container.appendChild(audio);

                btn.style.display = 'inline-block';
                btn.textContent = '▶️ Start';

                btn.onclick = () => {
                if (audio.paused) {
                    audio.play();
                    btn.textContent = '⏸️ Stop';
                } else {
                    audio.pause();
                    audio.currentTime = 0;
                    btn.textContent = '▶️ Start';
                }
                };

                audio.addEventListener('ended', () => {
                btn.textContent = '▶️ Start';
                });
            });

            // Fehlerbehandlung
            audio.addEventListener('error', () => {
                loadingOverlay.style.display = 'none';
                alert("Fehler beim Laden des Audios.");
            });
        }

        function loadDeezer(url, showLoading = false) {
            const match = url.match(/track\/(\d+)/);
            if (!match) return alert("Ungültiger Deezer-Link");

            const trackId = match[1];
            const container = document.getElementById('player-container');
            const btn = document.getElementById('startStopBtn');
            const loadingOverlay = document.getElementById('loading-overlay');

            if (showLoading) loadingOverlay.style.display = 'flex';

            fetch(`proxy/deezer_proxy.php?id=${trackId}`)
                .then(res => res.json())
                .then(data => {
                    if (showLoading) loadingOverlay.style.display = 'none';

                    if (!data.preview) {
                        alert("Keine Vorschau verfügbar.");
                        return;
                    }

                    container.innerHTML = `<audio id="deezerAudio" src="${data.preview}" preload="auto"></audio>`;
                    const audio = document.getElementById('deezerAudio');
                    btn.style.display = 'inline-block';
                    btn.textContent = '▶️ Start';

                    btn.onclick = () => {
                        if (audio.paused) {
                            audio.play();
                            btn.textContent = '⏸️ Stop';
                        } else {
                            audio.pause();
                            audio.currentTime = 0;
                            btn.textContent = '▶️ Start';
                        }
                    };

                    audio.addEventListener('ended', () => {
                        btn.textContent = '▶️ Start';
                    });
                })
                .catch(err => {
                    if (showLoading) loadingOverlay.style.display = 'none';
                    console.error("Fehler bei Deezer:", err);
                    alert("Fehler beim Laden der Deezer-Vorschau.");
                });
        }


        function cleanTitle(title) {
            // Entfernt alles innerhalb von Klammern und die Klammern selbst
            return title.replace(/\s*\([^)]*\)/g, '').trim();
        }


        // Experimenteller Modus: Spotify-Link analysieren und Deezer-Song abspielen
        async function searchDeezerFromSpotify(spotifyUrl) {
            const loadingOverlay = document.getElementById('loading-overlay');
            loadingOverlay.style.display = 'flex';

            const match = spotifyUrl.match(/track\/([a-zA-Z0-9]+)/);
            if (!match) {
                loadingOverlay.style.display = 'none';
                return alert("Ungültiger Spotify-Link");
            }
            const trackId = match[1];
            
            try {
                const res = await fetch(`proxy/spotify_proxy.php?id=${trackId}`);
                const data = await res.json();
                
                if (data.error) {
                    loadingOverlay.style.display = 'none';
                    return alert("Fehler: " + data.error);
                }

                const title = data.name;
                const artist = data.artists[0].name;

                fetch(`proxy/deezer_search_proxy.php?q=track:"${encodeURIComponent(cleanTitle(title))}" artist:"${encodeURIComponent(artist)}"`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.data && data.data.length > 0) {
                            let foundTrack = null;
                            for (let i = 0; i < data.data.length; i++) {
                                const deezerTrack = data.data[i];
                                if (!(deezerTrack.title.toLowerCase() === cleanTitle(title).toLowerCase())) {
                                    if (deezerTrack.title.toLowerCase().includes(cleanTitle(title).toLowerCase())) {
                                        foundTrack = deezerTrack;
                                        break;
                                    }
                                } else {
                                    foundTrack = deezerTrack;
                                    break;
                                }
                            }

                            if (foundTrack) {
                                console.log("Gefundener Deezer-Track:", foundTrack);
                                loadDeezer(`https://www.deezer.com/track/${foundTrack.id}`, true);
                            } else {
                                alert("Kein exakter Treffer gefunden.");
                                loadingOverlay.style.display = 'none';
                            }
                        } else {
                            alert("Song nicht auf Deezer gefunden.");
                            loadingOverlay.style.display = 'none';
                        }
                    })
                    .catch(err => {
                        console.error("Fehler bei Deezer Suche:", err);
                        alert("Fehler bei der Deezer-Suche.");
                        loadingOverlay.style.display = 'none';
                    });

            } catch (err) {
                alert("Fehler beim Abrufen der Spotify-Daten.");
                loadingOverlay.style.display = 'none';
            }
        }


        // QR-Scanner mit jsQR
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

                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } });
                videoStream = stream;
                videoElement.srcObject = stream;

                videoElement.onloadedmetadata = () => {
                canvasElement.width = videoElement.videoWidth;
                canvasElement.height = videoElement.videoHeight;
                scanActive = true;
                requestAnimationFrame(tick);
                };
            } catch (err) {
                alert("Kamera-Zugriff verweigert oder fehlgeschlagen: " + err.message);
                cancelScanning();
            }
        }


        function tick() {
            if (!scanActive) return;

            if (videoElement.readyState === videoElement.HAVE_ENOUGH_DATA) {
                canvasContext.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);
                const imageData = canvasContext.getImageData(0, 0, canvasElement.width, canvasElement.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "invertFirst"
                });

                if (code && code.data && code.data.trim() !== "") {
                    stopScanning();
                    const scannedData = code.data.trim();
                    
                    if (scannedData.includes('gtg.luda-vision.de')) {
                        // Prüfen, ob Protokoll fehlt und ergänzen
                        if (!scannedData.startsWith('http://') && !scannedData.startsWith('https://')) {
                            scannedData = 'https://' + scannedData;
                        }
                        // Direkt dorthin navigieren (Browser/Server macht den Rest via .htaccess)
                        window.location.href = scannedData;
                        return;
                    }
                    // Prüfen ob es eine URL mit ID ist
                    // Wir suchen nach "play.php?id=" oder einfach "?id="
                    if (scannedData.includes('play.php?id=') || scannedData.includes('?id=') || scannedData.includes('play?id=')) {
                        // Wenn es eine vollständige URL ist, leiten wir direkt weiter
                        if (scannedData.startsWith('http')) {
                            window.location.href = scannedData;
                        } else {
                            // Falls der QR Code nur "play.php?id=XX" enthält (ohne https://...)
                            window.location.href = scannedData;
                        }
                    } else {
                        // Alte Logik (Falls QR Code direkt einen Songlink enthält)
                        document.querySelector('input[name="url"]').value = scannedData;
                        document.getElementById('urlForm').submit();
                    }
                    return;
                }
            }

            requestAnimationFrame(tick);
        }

        function stopScanning() {
            scanActive = false;
            document.getElementById('qr-overlay').style.display = 'none';
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
        }

        function cancelScanning() {
            stopScanning();
        }

        if (songUrl) {
            btn.style.display = 'inline-block';
        }

        if (service === 'spotify') {
            document.getElementById('spotify-button-div').style.display = 'flex';
            document.getElementById('spotify-text-div').style.display = 'flex';
            document.getElementById('spotify-embed-div').style.display = 'flex';
            document.getElementById('spotify-embed').style.display = 'flex';
            const match = songUrl.match(/track\/([a-zA-Z0-9]+)/);
            
            if (match) {
                const trackId = match[1];
                document.getElementById('spotify-embed').src = `https://open.spotify.com/embed/track/${trackId}?utm_source=generator`;
                // Standardmäßig verstecken für experimentellen Modus
                document.getElementById('spotify-embed').style.display = 'none';
                searchDeezerFromSpotify(songUrl);
            } else {
                 loadingOverlay.style.display = 'none';
                 alert("Ungültiger Spotify-Link");
            }
            
        } else if (service === 'youtube') loadYouTube(songUrl);
        else if (service === 'deezer') loadDeezer(songUrl);

        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('experimentalBtn');
            const spotifyEmbed = document.getElementById('spotify-embed');
            const playBtn = document.getElementById('startStopBtn');

            if (btn) {
                btn.addEventListener('click', () => {
                    // Wenn der Modus aktiviert ist, verstecke das Embed und ändere die Schaltfläche
                    if (btn.textContent === "❌ Nicht Experimenteller Modus") {
                        // Wenn der Button zurückgesetzt wird, zeige das Embed wieder und ändere die Schaltfläche zurück
                        spotifyEmbed.style.display = 'flex'; // Zeige das Embed wieder
                        playBtn.style.display = 'none';
                        btn.textContent = "🔬 Experimenteller Modus (Deezer-Vorschau)"; // Ursprünglicher Buttontext
                    } else {
                        spotifyEmbed.style.display = 'none'; // Verstecke das Embed
                        btn.textContent = "❌ Nicht Experimenteller Modus"; // Ändere den Text des Buttons
                        playBtn.style.display = 'inline-block';
                        searchDeezerFromSpotify(songUrl);
                    }
                });
            }
        });

    </script>
</body>
</html>
<?php include 'footer.php'; ?>