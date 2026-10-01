# Tennisschule Alex Braun

Statische Neuimplementierung der bisherigen WordPress-Website von https://www.tennisschule-alexbraun.de/.

## Enthaltene Seiten

- /
- /angebote/
- /trainerteam/
- /standorte/
- /tsv-feldkirchen/
- /sv-heimstetten/
- /kontakt/
- /impressum-datenschutzerklaerung/

## Aktueller Migrationsstand

Die öffentliche Seitenstruktur, Texte, Navigation, Trainer, Angebote, Standorte und rechtlichen Inhalte sind als statische HTML/CSS/JS-Version umgesetzt.

Die vorhandenen Bilder werden in dieser Migrationsstufe direkt aus dem bisherigen WordPress-/Jetpack-Bildbestand geladen. Vor der Abschaltung des WordPress-Hostings müssen diese Medien in das neue Hosting übernommen werden.

Das Kontaktformular ist visuell vorhanden. Die endgültige serverseitige Formularzustellung wird beim Cloudflare-Deployment eingerichtet.

Das geplante Eigentümer-CMS wird ohne Astro/TinaCMS umgesetzt. Der Fine-grained GitHub Token darf nicht im Browser oder Repository gespeichert werden und wird später als serverseitiges Secret hinterlegt.
