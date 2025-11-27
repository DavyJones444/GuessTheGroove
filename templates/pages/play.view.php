<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="wrapper">
    <h2 class="header-style">Musikquiz Player</h2>

    <form method="get" id="urlForm" style="display: none;">
        <input type="text" name="url" value="<?= htmlspecialchars($viewData['songUrl']) ?>" >
        <button type="submit">Laden</button>
    </form>

    <div class="div-style">
        <button id="scanBtn" class="button" onclick="startScanning()">Scan QR Code</button>
    </div>

    <div id="player-container"></div>
    
    </div>

<script>
    // Wir nutzen hier die PHP Variablen, die der Controller übergeben hat
    const service = "<?= $viewData['service'] ?>";
    const songUrl = "<?= $viewData['songUrl'] ?>";
    const token = "<?= $viewData['token'] ?>";
    
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
        const audio = new Audio(`/api/youtube/audio?url=${encodeURIComponent(url)}`);
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

        fetch(`/api/deezer/track?id=${trackId}`)
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
            const res = await fetch(`/api/spotify/track?id=${trackId}`);
            const data = await res.json();
            
            if (data.error) {
                loadingOverlay.style.display = 'none';
                return alert("Fehler: " + data.error);
            }

            const title = data.name;
            const artist = data.artists[0].name;

            fetch(`/api/deezer/track?q=track:"${encodeURIComponent(cleanTitle(title))}" artist:"${encodeURIComponent(artist)}"`)
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
            document.querySelector('input[name="url"]').value = code.data.trim();
            document.getElementById('urlForm').submit();
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
                    if (!match) {
                        loadingOverlay.style.display = 'none';
                        alert("Ungültiger Spotify-Link");
                    }
                    const trackId = match[1];
                    document.getElementById('spotify-embed').src = `https://open.spotify.com/embed/track/${trackId}?utm_source=generator`;
        document.getElementById('spotify-embed').style.display = 'none';
        searchDeezerFromSpotify(songUrl);
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

<?php include __DIR__ . '/../layouts/footer.php'; ?>