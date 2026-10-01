# Tennisschule Alex Braun

Statische Migration der bisherigen WordPress-Website von https://www.tennisschule-alexbraun.de/.

## Migrierte Seiten

- /
- /angebote/
- /trainerteam/
- /standorte/
- /tsv-feldkirchen/
- /sv-heimstetten/
- /kontakt/
- /impressum-datenschutzerklaerung/

## Medien

Die für die Website benötigten Originalbilder und Dokumente wurden aus dem WordPress-Uploads-Bestand in das Repository unter `assets/media/` übernommen.

Das Frontend lädt keine Website-Bilder mehr über WordPress oder Jetpack. Externe Links zu Shopify, Vereinswebsites und Google Maps bleiben absichtlich extern.

## Technik

Die Website läuft ohne WordPress, Astro oder TinaCMS als statisches HTML/CSS/JavaScript-Projekt.

Das geplante Eigentümer-CMS wird als eigener Admin-Bereich umgesetzt. Schreibzugriffe auf GitHub erfolgen später serverseitig über einen Fine-grained GitHub Token. Der Token darf niemals im Browser oder im Repository gespeichert werden.

## Noch vor Domain-Umschaltung

- Staging-/Preview-Deployment einrichten und visuell gegen die bisherige Seite prüfen.
- Kontakt- und Anmeldeformulare serverseitig anbinden.
- Eigentümer-CMS /admin fertigstellen.
- Erst danach DNS/Domain auf das neue Hosting umstellen.

Die bestehende WordPress-Installation kann bis zur finalen Umschaltung unverändert online bleiben.
