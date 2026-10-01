# Deployment auf United Domains

Die Website benötigt **kein Supabase, keine externe Datenbank und kein Cloudflare**.

## Zielarchitektur

- Domain und Webspace: United Domains
- Website: PHP + HTML/CSS/JavaScript
- Inhalte: `data/site.json`
- Eigentümer-CMS: `/admin/`
- Versionshistorie: GitHub
- E-Mail: SMTP über United Domains
- Geheimnisse: ausschließlich `config.local.php` auf dem Webspace bzw. GitHub Actions Secrets

## 1. Testverzeichnis bei United Domains anlegen

Nicht direkt die bestehende WordPress-Installation überschreiben.

Empfohlen ist zunächst ein eigenes Verzeichnis, z. B.:

`tennisschule_alexbraun_umstrukturierung`

Dorthin wird diese GitHub-Version deployed. Die Testdomain ist dafür **https://test.tennisschule-alexbraun.de**. Auf dieser Subdomain setzt die Website automatisch `noindex,nofollow,noarchive`, damit die Testversion nicht von Suchmaschinen indexiert wird.

## 2. GitHub Actions Secrets für den SFTP-Deploy

Im Repository unter Settings → Secrets and variables → Actions folgende Secrets anlegen:

- `UD_SFTP_HOST` – SFTP-Host von United Domains
- `UD_SFTP_USER` – SFTP-Benutzer
- `UD_SFTP_PASSWORD` – SFTP-Passwort
Das Zielverzeichnis ist bereits fest auf `tennisschule_alexbraun_umstrukturierung` eingestellt.

Der Workflow **Deploy to United Domains** läuft bewusst nur manuell. Dadurch kann er die bestehende WordPress-Seite nicht versehentlich ersetzen.

## 3. Secrets für die Server-Konfiguration

Der Deploy-Workflow erzeugt `config.local.php` automatisch und lädt sie auf den United-Domains-Webspace. Die Datei wird nicht in Git committed.

Zusätzlich zu den drei SFTP-Secrets werden folgende GitHub Actions Secrets benötigt:

- `TS_ADMIN_PASSWORD` – das gewünschte Passwort für `/admin/`
- `TS_GITHUB_TOKEN` – Fine-grained GitHub Token
- `TS_SMTP_PASSWORD` – Passwort des Postfachs `info@tennisschule-alexbraun.de`

Der SMTP-Benutzer ist bereits auf `info@tennisschule-alexbraun.de` eingestellt.

### Benötigte SFTP-Secrets

- `UD_SFTP_HOST`
- `UD_SFTP_USER`
- `UD_SFTP_PASSWORD`

### Fine-grained GitHub Token

Repository-Zugriff nur auf:

`SebMut/tennisschule_alexbraun`

Benötigte Repository-Berechtigung:

- Contents: Read and write

Weitere Rechte sind für das CMS nicht erforderlich.

## 4. Admin-Passwort

In `config.local.php` wird kein Klartextpasswort gespeichert, sondern ein PHP-Passworthash.

Beispiel zum Erzeugen:

`php -r "echo password_hash('DEIN-PASSWORT', PASSWORD_DEFAULT), PHP_EOL;"`

Den ausgegebenen Hash in `admin_password_hash` eintragen.

## 5. E-Mail

Das Kontaktformular sendet direkt über SMTP. Voreingestellt ist:

- Host: `smtps.udag.de`
- Port: `587`
- Verschlüsselung: `tls`
- Absender/Empfänger: `info@tennisschule-alexbraun.de`

Benutzername und Passwort kommen ausschließlich in `config.local.php`.

## 6. CMS

Nach der Konfiguration ist das CMS erreichbar unter:

`https://www.tennisschule-alexbraun.de/admin/`

Der Eigentümer kann dort bearbeiten:

- Hero-Texte und Hero-Bild
- Über-uns-Inhalte
- Angebote und Bilder
- Trainer und Trainerbilder
- Standorte und Links

Beim Speichern passiert beides:

1. `data/site.json` wird sofort auf dem United-Domains-Webspace aktualisiert.
2. Dieselbe Änderung wird als Commit in GitHub versioniert.

Bilder werden in `assets/media/cms/` gespeichert und ebenfalls nach GitHub committed.

## 7. Vor der finalen Umschaltung testen

- Startseite Desktop/Mobil
- alle Unterseiten
- `/admin/` Login
- Textänderung + GitHub Commit
- Bild-Upload + GitHub Commit
- Kontaktformular und Antwortadresse
- HTTPS
- Google-Maps/Datenschutz
- Impressum und Datenschutzerklärung fachlich prüfen

Erst danach in United Domains die Domain `www.tennisschule-alexbraun.de` auf das neue Verzeichnis zeigen lassen.

Die alte WordPress-Installation sollte erst gelöscht werden, wenn die neue Website vollständig abgenommen und ein Backup vorhanden ist.


## Testdeployment ohne Admin/SMTP

Für das erste Staging-Deployment sind `TS_ADMIN_PASSWORD` und `TS_SMTP_PASSWORD` optional.

Fehlen sie:
- die öffentliche Website funktioniert normal;
- `/admin/` ist noch nicht nutzbar;
- das Kontaktformular kann noch keine E-Mails versenden.

Sobald die Secrets später ergänzt wurden, genügt ein erneuter manueller Deploy.
