<?php
// Diese Datei nach config.local.php kopieren und NUR auf dem United-Domains-Webspace befüllen.
// config.local.php ist über .gitignore vom Git-Repository ausgeschlossen.
return [
    // Erzeugen z.B. mit: php -r "echo password_hash('DEIN-PASSWORT', PASSWORD_DEFAULT), PHP_EOL;"
    'admin_password_hash' => 'HIER_PASSWORTHASH_EINTRAGEN',

    'github' => [
        'token' => 'github_pat_HIER_FINE_GRAINED_TOKEN',
        'owner' => 'SebMut',
        'repo' => 'tennisschule_alexbraun',
        'branch' => 'main',
    ],

    'mapbox' => [
        'token' => 'HIER_MAPBOX_PUBLIC_TOKEN',
    ],

    'smtp' => [
        'host' => 'smtps.udag.de',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'info@tennisschule-alexbraun.de',
        'password' => 'HIER_EMAIL_PASSWORT',
        'from' => 'info@tennisschule-alexbraun.de',
        'from_name' => 'Tennisschule Alex Braun',
        'to' => 'info@tennisschule-alexbraun.de',
    ],
];
