<?php 
$card = $viewData['card'];
include __DIR__ . '/../layouts/header.php'; 
?>

<div class="wrapper">
    <h2 class="header-style">Karte bearbeiten</h2>
    
    <form method="post" action="/cards/update?id=<?= $card['id'] ?>" class="form-container" style="max-width: 400px; margin: auto;">
        
        <div style="margin-bottom: 10px; width: 100%;">
            <label for="title">Titel:</label><br>
            <input type="text" name="title" id="title" value="<?= htmlspecialchars($card['title']) ?>" required style="width: 100%;">
        </div>
        
        <div style="margin-bottom: 10px; width: 100%;">
            <label for="year">Jahr:</label><br>
            <input type="number" name="year" id="year" value="<?= htmlspecialchars($card['year']) ?>" required style="width: 100%;">
        </div>
        
        <div style="margin-bottom: 10px; width: 100%;">
            <label for="artist">Künstler:</label><br>
            <input type="text" name="artist" id="artist" value="<?= htmlspecialchars($card['artist']) ?>" required style="width: 100%;">
        </div>
        
        <div style="margin-bottom: 10px; width: 100%;">
            <label for="songlink">Songlink:</label><br>
            <input type="url" name="songlink" id="songlink" value="<?= htmlspecialchars($card['songlink']) ?>" required style="width: 100%;">
            <button type="button" class="button" onclick="fetchSongInfo()" style="margin-top: 5px; width: 100%;">🔍 Link prüfen & Daten laden</button>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <button type="submit" class="button">Speichern & Aktualisieren</button>
        </div>
    </form>
    
    <div style="text-align:center; margin-top: 20px;">
        <a href="/profile">Abbrechen und zurück</a>
    </div>
</div>

<script>
    async function fetchSongInfo() {
        const link = document.getElementById('songlink').value;

        // --- DEEZER ---
        if (link.includes("deezer.com/track/")) {
            try {
                const trackId = link.split("/track/")[1].split(/[?#]/)[0];
                const res = await fetch(`/api/deezer/track?id=${trackId}`);
                const data = await res.json();
                
                if (data.error) return alert("Fehler: " + data.error);

                document.getElementById("title").value = data.title;
                document.getElementById("artist").value = data.artist.name;
                document.getElementById("year").value = new Date(data.release_date).getFullYear() || "";
            } catch (e) { alert("Fehler beim Abrufen von Deezer."); }
        } 
        // --- SPOTIFY ---
        else if (/spotify\.com\/.+\/track\//.test(link) || link.includes("spotify.com/track/")) {
            try {
                const match = link.match(/\/track\/([a-zA-Z0-9]+)/);
                if (!match) return alert("Track-ID konnte nicht erkannt werden.");
                
                const trackId = match[1];
                const res = await fetch(`/api/spotify/track?id=${trackId}`);
                const data = await res.json();
                
                if (data.error) return alert("Fehler: " + data.error);

                document.getElementById("title").value = data.name;
                document.getElementById("artist").value = data.artists[0].name;
                document.getElementById("year").value = data.album.release_date.split("-")[0];
            } catch (e) { alert("Fehler beim Abrufen von Spotify."); }
        } 
        // --- YOUTUBE ---
        else if (link.includes("youtube.com/watch?v=") || link.includes("youtu.be/")) {
            let videoId = null;
            try {
                const url = new URL(link);
                if (url.hostname === "youtu.be") {
                    videoId = url.pathname.substring(1);
                } else {
                    videoId = url.searchParams.get("v");
                }
            } catch (e) { return alert("Ungültiger YouTube-Link."); }

            if (!videoId) return alert("Video-ID konnte nicht erkannt werden.");

            try {
                const res = await fetch(`/api/youtube/info?id=${videoId}`);
                const data = await res.json();
                
                if (data.error) return alert("Fehler: " + data.error);

                document.getElementById("title").value = data.title;
                document.getElementById("artist").value = data.artist;
                document.getElementById("year").value = data.year;
            } catch (e) { alert("Fehler beim Abrufen von YouTube."); }
        } else {
            alert("Ungültiger Link. Bitte einen Deezer-, YouTube- oder Spotify-Link einfügen.");
        }
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>