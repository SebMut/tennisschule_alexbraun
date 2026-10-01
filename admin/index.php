<?php
declare(strict_types=1);
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Website bearbeiten – Tennisschule Alex Braun</title>
<link rel="stylesheet" href="/admin/admin.css">
</head>
<body>
<header class="admin-header">
  <div><strong>Tennisschule Alex Braun</strong><span>Website bearbeiten</span></div>
  <div class="admin-actions">
    <a href="/" target="_blank" rel="noopener">Website öffnen</a>
    <button id="logoutBtn" class="secondary" hidden>Abmelden</button>
  </div>
</header>

<main class="admin-shell">
  <section id="loginPanel" class="panel login-panel">
    <h1>Anmelden</h1>
    <p>Mit dem Admin-Passwort der Tennisschule anmelden.</p>
    <form id="loginForm">
      <label>Passwort<input id="password" type="password" autocomplete="current-password" required></label>
      <button type="submit">Anmelden</button>
      <p id="loginMessage" class="message"></p>
    </form>
  </section>

  <section id="editorPanel" hidden>
    <div class="admin-sticky-controls">
      <div class="editor-top">
        <div><h1>Inhalte bearbeiten</h1><p>Änderungen werden sofort auf dem Webspace gespeichert und zusätzlich in GitHub versioniert.</p></div>
        <button id="saveBtn">Änderungen speichern</button>
      </div>
      <nav class="admin-menu" id="adminMenu" aria-label="Bereiche">
        <button type="button" class="admin-menu-item active" data-admin-tab="allgemein">Allgemein</button>
        <button type="button" class="admin-menu-item" data-admin-tab="home">Home</button>
        <button type="button" class="admin-menu-item" data-admin-tab="angebote">Angebote</button>
        <button type="button" class="admin-menu-item" data-admin-tab="trainerteam">Trainerteam</button>
        <button type="button" class="admin-menu-item" data-admin-tab="standorte">Standorte</button>
        <button type="button" class="admin-menu-item" data-admin-tab="kontakt">Kontakt</button>
        <button type="button" class="admin-menu-item" data-admin-tab="weitere">Weitere Seiten</button>
        <button type="button" class="admin-menu-item stats-tab" data-admin-tab="statistik">Statistik</button>
      </nav>
      <div id="saveMessage" class="message"></div>
    </div>
    <div id="editor"></div>
  </section>
</main>
<script src="/admin/admin.js"></script>
</body>
</html>
