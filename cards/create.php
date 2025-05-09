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
    <form method="post" action="../create_card_logic.php" class="form-container" style="max-width: 400px; margin: auto;">
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
    </form>

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
    </script>
</body>
</html>

<?php include 'footer.php'; ?>
