# Tennisschule Alex Braun

Migration der bisherigen WordPress-Website auf eine schlanke PHP-Lösung für United Domains.

## Architektur

- Hosting und Domain: United Domains
- Website: PHP + HTML/CSS/JavaScript
- Inhalte: `data/site.json`
- Eigentümer-CMS: `/admin/`
- Versionierung: GitHub
- Kontaktformular: SMTP über United Domains
- Datenbank: keine
- Supabase: nicht erforderlich

## Seiten

- /
- /angebote/
- /trainerteam/
- /standorte/
- /tsv-feldkirchen/
- /sv-heimstetten/
- /kontakt/
- /impressum-datenschutzerklaerung/
- /admin/

## CMS

Der Eigentümer kann im Adminbereich Texte, Angebote, Trainer, Bilder und Standorte bearbeiten.

Beim Speichern:

1. wird `data/site.json` sofort auf dem Webspace aktualisiert;
2. wird dieselbe Änderung über einen serverseitig gespeicherten Fine-grained GitHub Token nach GitHub committed.

Bild-Uploads landen unter `assets/media/cms/` und werden ebenfalls in GitHub versioniert.

## Sicherheit

Geheimnisse werden nicht im Repository gespeichert. Auf dem Webspace wird dafür `config.local.php` aus `config.example.php` erstellt.

Benötigt:

- Admin-Passworthash
- Fine-grained GitHub Token mit Contents Read/Write nur für dieses Repository
- SMTP-Zugangsdaten

Admin-Login nutzt PHP-Sessions, CSRF-Schutz und Login-Rate-Limiting.

## Kontaktformular

Der Versand läuft direkt per SMTP über den United-Domains-Mailserver. Es ist kein externer Formulardienst erforderlich.

## Google Maps

Google Maps wird auf den Standortseiten erst nach ausdrücklicher Zustimmung geladen. Die Zustimmung wird lokal im Browser gespeichert.

## Deployment

Siehe `DEPLOYMENT-UNITED-DOMAINS.md`.

Der GitHub-Workflow `Deploy to United Domains` deployt Änderungen auf `main` automatisch in das Testverzeichnis `tennisschule_alexbraun_umstrukturierung`. Vor dem Upload werden PHP und JavaScript geprüft. Die bestehende WordPress-Seite wird dabei nicht überschrieben.

## Qualitätssicherung

GitHub Actions prüft alle PHP- und JavaScript-Dateien automatisch auf Syntaxfehler.

Die bestehende WordPress-Installation sollte erst nach vollständiger Abnahme und finaler Domain-Umschaltung entfernt werden.
