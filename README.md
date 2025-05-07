# hitster-customs
Hitster ist ein erfolgreiches Musikquiz für Zuhause.
Höre einen Song und versuche dann ihn in deinen Zeitstrahl einzusortieren.
Wer zuerst einen Zeitstrahl aus 10 Songs hat, hat gewonnen.

Leider ist man bei diesem Spiel auf die Songs begrenzt, die beiliegen.
Zwar kann man seine Sammlung mit Erweiterungen füllen,
um aber auch andere Songs verwenden zu können, gibt es Hitster Customs.

Hier kann man eigene Karten erstellen, sie runterladen, drucken, ausschneiden und los geht der Spaß!

## Functions
- [ ] Accountmanagement
    - Account erstellen (Benutzername, Email, Passwort)
    - Account verifizieren (Link über Email-Adresse)
    - In Account einloggen (Email, Passwort und Sessions)
    - Account bearbeiten (Benutzername, Profilbild, Passwort)
    - Passwort zurücksetzen (Link über Email-Adresse)
    - Account löschen
- [ ] Kartenmanagement
    - Karte erstellen (über Songlink [Spotify, Deezer, YouTube] oder Titel, Erscheinungsjahr, Interpret, Songlink manuell)
    - Karte bearbeiten (Titel, Erscheinungsjahr, Interpret)
    - Karten in Playlists organisieren
        - Playlists erstellen
        - Playlistname bearbeiten
        - Karten hinzufügen/entfernen
        - Playlist herunterladen (zip Archiv mit allen Karten als .png)
    - Karte veröffentlichen (Privat/Öffentlich)
    - Karte herunterladen (1200x600px PNG)
    - Karte löschen
- [ ] Audio Player
    - QR-Code Scanner (für normale und invertierte QR-Codes und für Mobile)
    - Audio Player (Deezer über API; YouTube über yt-dl; Metadatensuche über öffentliche Spotify API und Songsuche über Deezer)

## Technologies
- XAMPP
    - mysql -> phpmyadmin
    - Apache Web Server
- phpqrcode
- Deezer, Spotify, YouTube via API
- PHPmailer
- yt-dl
- Code in PHP, HTML und CSS

Optional:
- Hitster anschreiben wegen App Kompatibilität.

## Contributors
| Davy Kyrian | 3541361 | davy_jones444 |
