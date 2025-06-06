<?php
require 'lib/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: welcome");
    exit();
}

$userId = $_SESSION['user_id'] ?? null;
$id = $_GET['id'] ?? null;
if (!$userId || !$id) die("Zugriff verweigert.");

$stmt = $pdo->prepare("SELECT * FROM cards WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $userId]);
$card = $stmt->fetch();
if (!$card) die("Karte nicht gefunden.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $year = $_POST['year'];
    $artist = $_POST['artist'];
    $songlink = $_POST['songlink'];

    // Plattform bestimmen
    $platform = 'Andere';
    if (strpos($songlink, 'deezer.com/track/') !== false) {
        $platform = 'Deezer';
    } elseif (preg_match('/spotify\.com\/.+\/track\//', $songlink) || strpos($songlink, 'spotify.com/track/') !== false) {
        $platform = 'Spotify';
    } elseif (strpos($songlink, 'youtube.com/watch?v=') !== false || strpos($songlink, 'music.youtube.com/watch?v=') !== false || strpos($songlink, 'youtu.be/') !== false) {
        $platform = 'YouTube';
    }

    // Alte Bilder löschen
    if (!empty($card['image_text']) && file_exists(__DIR__ . "/images/" . $card['image_text'])) {
        unlink(__DIR__ . "/card/images/" . $card['image_text']);
    }
    if (!empty($card['image_qr']) && file_exists(__DIR__ . "/images/" . $card['image_qr'])) {
        unlink(__DIR__ . "/card/images/" . $card['image_qr']);
    }

    // === Bild + QR-Code generieren wie in create.php (du kannst das aus create.php kopieren) ===
    ob_start(); // Optional zur Fehlervermeidung bei Bildausgabe
    require 'card/create_card_logic.php'; // ausgelagerte Wiederverwendung möglich (siehe Hinweis unten)
    ob_end_clean();

    // Update in Datenbank
    $stmt = $pdo->prepare("UPDATE cards SET title = ?, year = ?, artist = ?, songlink = ?, platform = ?, image_text = ?, image_qr = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$title, $year, $artist, $songlink, $platform, $image_text, $image_qr, $id, $userId]);

    header("Location: profile.php");
    exit;
}
$title = "Karte bearbeiten";
include 'header.php';
?>
<!DOCTYPE html>
<html>
<body>
    <h2>Karte bearbeiten</h2>
    <form method="post" class="form-container" style="max-width: 400px; margin: auto;">
        <div style="margin-bottom: 10px;">
            <label for="title">Titel:</label><br>
            <input type="text" name="title" id="title" value="<?= htmlspecialchars($card['title']) ?>" required style="width: 100%;">
        </div>
        <div style="margin-bottom: 10px;">
            <label for="year">Jahr:</label><br>
            <input type="number" name="year" id="year" value="<?= htmlspecialchars($card['year']) ?>" required style="width: 100%;">
        </div>
        <div style="margin-bottom: 10px;">
            <label for="artist">Künstler:</label><br>
            <input type="text" name="artist" id="artist" value="<?= htmlspecialchars($card['artist']) ?>" required style="width: 100%;">
        </div>
        <div style="margin-bottom: 10px;">
            <label for="songlink">Songlink:</label><br>
            <input type="url" name="songlink" id="songlink" value="<?= htmlspecialchars($card['songlink']) ?>" required style="width: 100%;">
            <button type="button" onclick="fetchSongInfo()">🔍 Link prüfen</button>
        </div>
        <div style="text-align: center;">
            <button type="submit">Speichern</button>
        </div>
    </form>
    <a href="profile.php">Zurück</a>
    <script>
        async function fetchSongInfo() {
            const link = document.getElementById('songlink').value;

            if (link.includes("deezer.com/track/")) {
                const trackId = link.split("/track/")[1].split(/[?#]/)[0];
                const res = await fetch(`proxy/deezer_proxy.php?id=${trackId}`);
                const data = await res.json();
                if (data.error) return alert("Fehler: " + data.error);

                document.getElementById("title").value = data.title;
                document.getElementById("artist").value = data.artist.name;
                document.getElementById("year").value = new Date(data.release_date).getFullYear() || "";
            } 
            else if (/spotify\.com\/.+\/track\//.test(link) || link.includes("spotify.com/track/")) {
                const match = link.match(/\/track\/([a-zA-Z0-9]+)/);
                if (!match) return alert("Track-ID konnte nicht erkannt werden.");
                const trackId = match[1];
                const res = await fetch(`proxy/spotify_proxy.php?id=${trackId}`);
                const data = await res.json();
                if (data.error) return alert("Fehler: " + data.error);

                document.getElementById("title").value = data.name;
                document.getElementById("artist").value = data.artists[0].name;
                document.getElementById("year").value = data.album.release_date.split("-")[0];
            } 
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

                const res = await fetch(`proxy/youtube_proxy.php?id=${videoId}`);
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