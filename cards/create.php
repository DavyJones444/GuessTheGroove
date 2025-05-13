<?php
// CORS-Header hinzufügen
header("Access-Control-Allow-Origin: *");  // Erlaubt Anfragen von allen Domains
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");  // Erlaubt diese HTTP-Methoden
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");  // Erlaubt bestimmte Header

// Falls es sich um eine OPTIONS-Anfrage handelt (Preflight), sofort antworten und keine weiteren Ausgaben
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

require __DIR__ . '/../lib/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: welcome");
    exit();
}

$userId = $_SESSION['user_id'];

$title = "Karte erstellen";
?>

<?php
$username = '';
$profilePic = 'default_profile.png';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT name, profile_pic FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $username = htmlspecialchars($user['name']);
        if (!empty($user['profile_pic'])) {
            $profilePic = htmlspecialchars($user['profile_pic']);
        }
    }
}
include 'header.php';
?>

<!DOCTYPE html>
<html lang="de">
<body>
    <form method="post" accept-charset="UTF-8" action="../create_card_logic.php" class="form-container" style="max-width: 400px; margin: auto;">
        <h2 class="header-style">Neue Karte erstellen</h2>
        <div style="margin-bottom: 10px;">
            <label for="title">Titel:</label><br>
            <input type="text" name="title" id="title" required style="width: 100%;">
        </div>
        <div style="margin-bottom: 10px;">
            <label for="year">Jahr:</label><br>
            <input type="number" name="year" id="year" required style="width: 100%;">
        </div>
        <div style="margin-bottom: 10px;">
            <label for="artist">Künstler:</label><br>
            <input type="text" name="artist" id="artist" required style="width: 100%;">
        </div>
        <div style="margin-bottom: 10px;">
            <label for="songlink">Songlink:</label><br>
            <input type="url" name="songlink" id="songlink" required style="width: 100%;">
            <button type="button" onclick="fetchSongInfo()">🔍 Link prüfen</button>
        </div>
        <div style="text-align: center;">
            <button type="submit">Erstellen</button>
        </div>
        <div style="margin-bottom: 10px;">
            <label for="playlistlink">Playlist-Link (optional):</label><br>
            <input type="url" name="playlistlink" id="playlistlink" style="width: 100%;">
            <button type="button" onclick="fetchPlaylistInfo()">🎵 Playlist laden</button>
        </div>
    </form>

    <div id="track-forms"></div>

    <script>
        async function fetchSongInfo() {
            const link = document.getElementById('songlink').value;

            // Überprüfen, ob es sich um einen Deezer-Shortlink handelt
            if (link.includes("dzr.page.link/")) {
                // Deezer Shortlink Umleitung verfolgen
                const res = await fetch(link, { method: 'HEAD' });
                const finalUrl = res.url;

                if (finalUrl.includes("deezer.com/track/")) {
                    const trackId = finalUrl.split("/track/")[1].split(/[?#]/)[0];
                    const resTrack = await fetch(`deezer_proxy.php?id=${trackId}`);
                    const data = await resTrack.json();
                    if (data.error) return alert("Fehler: " + data.error);

                    document.getElementById("title").value = data.title;
                    document.getElementById("artist").value = data.artist.name;
                    document.getElementById("year").value = new Date(data.release_date).getFullYear() || "";
                } else if (/deezer\.com\/.+\/track\//.test(finalUrl) || finalUrl.includes("deezer.com/track/")) {
                    const match = finalUrl.match(/\/track\/(\d+)/);
                    if (!match) return alert("Track-ID konnte nicht erkannt werden.");
                    const trackId = match[1];
                    const res = await fetch(`../deezer_proxy.php?id=${trackId}`);
                    const data = await res.json();
                    if (data.error) return alert("Fehler: " + data.error);

                    document.getElementById("title").value = data.title;
                    document.getElementById("artist").value = data.artist.name;
                    document.getElementById("year").value = new Date(data.release_date).getFullYear() || "";
                } else {
                    alert("Dieser Deezer-Link führt nicht zu einem Track.");
                }
            } 
            // Überprüfen, ob es sich um einen normalen Deezer-Link handelt
            else if (/deezer\.com\/.+\/track\//.test(link) || link.includes("deezer.com/track/")) {
                const match = link.match(/\/track\/(\d+)/);
                if (!match) return alert("Track-ID konnte nicht erkannt werden.");
                const trackId = match[1];
                const res = await fetch(`../deezer_proxy.php?id=${trackId}`);
                const data = await res.json();
                if (data.error) return alert("Fehler: " + data.error);

                document.getElementById("title").value = data.title;
                document.getElementById("artist").value = data.artist.name;
                document.getElementById("year").value = new Date(data.release_date).getFullYear() || "";
            }
            // Spotify-Links behandeln
            else if (/spotify\.com\/.+\/track\//.test(link) || link.includes("spotify.com/track/")) {
                const match = link.match(/\/track\/([a-zA-Z0-9]+)/);
                if (!match) return alert("Track-ID konnte nicht erkannt werden.");
                const trackId = match[1];
                const res = await fetch(`../spotify_proxy.php?id=${trackId}`);
                const data = await res.json();
                if (data.error) return alert("Fehler: " + data.error);

                document.getElementById("title").value = data.name;
                document.getElementById("artist").value = data.artists[0].name;
                document.getElementById("year").value = data.album.release_date.split("-")[0];
            } 
            // YouTube-Links behandeln
            else if (link.includes("youtube.com/watch?v=") || link.includes("youtu.be/")) {
                let videoId = null;

                try {
                    const url = new URL(link);
                    if (url.hostname === "youtu.be") {
                        videoId = url.pathname.substring(1);
                    } else {
                        videoId = url.searchParams.get("v");
                    }
                } catch (e) {
                    return alert("Ungültiger YouTube-Link.");
                }

                if (!videoId) return alert("Video-ID konnte nicht erkannt werden.");

                const res = await fetch(`../youtube_proxy.php?id=${videoId}`);
                const data = await res.json();
                if (data.error) return alert("Fehler: " + data.error);

                document.getElementById("title").value = data.title;
                document.getElementById("artist").value = data.artist;
                document.getElementById("year").value = data.year;
            } else {
                alert("Ungültiger Link. Bitte einen Deezer-, YouTube- oder Spotify-Link einfügen oder die Daten manuell eintragen.");
            }
        }

        async function fetchPlaylistInfo() {
            const link = document.getElementById('playlistlink').value;
            if (!link) return alert("Bitte einen Playlist-Link einfügen.");

            let platform = null, playlistId = null;

            if (link.includes("spotify.com/playlist/")) {
                platform = "spotify";
                playlistId = link.match(/playlist\/([a-zA-Z0-9]+)/)[1];
            } else if (/deezer\.com\/(?:[a-z]{2}\/)?playlist\/\d+/.test(link)) {
                platform = "deezer";
                const match = link.match(/playlist\/(\d+)/);
                playlistId = match ? match[1] : null;
                if (!playlistId) return alert("Playlist-ID konnte nicht erkannt werden.");
            } else {
                alert("Nur Spotify- und Deezer-Playlisten werden derzeit unterstützt.");
                return;
            }

            const res = await fetch(`../${platform}_playlist_proxy.php?id=${playlistId}`);
            const data = await res.json();

            if (data.error) return alert("Fehler: " + data.error);

            const trackContainer = document.getElementById("track-forms");
            const tracks = platform === "spotify" ? data.tracks.items.map(item => item.track) : data.tracks.data;

            let formHTML = `<form method="post" action="../create_card_logic.php"><input type="hidden" name="batch" value="1">`;

            tracks.forEach((track, index) => {
                const title = track.title || track.name;
                const artist = platform === "spotify"
                    ? track.artists.map(a => a.name).join(", ")
                    : track.artist.name;
                let year = "";
                if (platform === "spotify") {
                    year = (track.album.release_date || "").split("-")[0];
                } else if (platform === "deezer") {
                    // Deezer liefert `release_date` oft auf Track-Ebene
                    year = (track.release_date || "").split("-")[0];
                }
                const songlink = platform === "spotify" ? track.external_urls.spotify : track.link;

                formHTML += `
                    <div class="track-card" style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
                        <h3>Track ${index + 1}</h3>
                        <input type="hidden" name="tracks[${index}][title]" value="${title}">
                        <input type="hidden" name="tracks[${index}][artist]" value="${artist}">
                        <input type="hidden" name="tracks[${index}][year]" value="${year}">
                        <input type="hidden" name="tracks[${index}][songlink]" value="${songlink}">
                        <strong>${title}</strong><br>
                        Künstler: ${artist}<br>
                        Jahr: ${year}<br>
                        <a href="${songlink}" target="_blank">🎧 Link</a>
                    </div>
                `;
            });

            formHTML += `<button type="submit">🎴 Alle Karten aus Playlist erstellen (${tracks.length})</button></form>`;
            trackContainer.innerHTML = formHTML;
        }



    </script>
</body>
</html>

<?php include 'footer.php'; ?>
