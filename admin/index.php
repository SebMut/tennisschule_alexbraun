<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/staging-access.php';
staging_access_require(false);
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Website bearbeiten – Tennisschule Alex Braun</title>
<link rel="stylesheet" href="/admin/admin.css">
<style>
@media (max-width:760px){
  body.admin-editing{overflow:auto!important}
  body.admin-editing .admin-shell{padding-top:0!important}
  #editorPanel{display:block!important}
  #editorPanel[hidden]{display:none!important}
  .content-audit-wrap{position:relative!important;inset:auto!important;margin:0 0 10px!important;z-index:1!important}
  .admin-sticky-controls{position:relative!important;top:auto!important;inset:auto!important;z-index:1!important;margin:0 0 10px!important;padding:0!important;background:transparent!important}
  .admin-sticky-controls:before{display:none!important}
  .editor-top{position:relative!important;display:grid!important;grid-template-columns:minmax(0,1fr) auto!important;gap:8px!important;align-items:end!important;min-height:0!important;margin:0!important;padding:0!important}
  .editor-heading{display:none!important}
  .mobile-section-picker{display:block!important;margin:0!important;min-width:0!important}
  .mobile-section-picker>span{display:block!important;margin:0 0 3px!important}
  .save-cluster{display:flex!important;align-items:flex-end!important}
  .dirty-state{display:none!important}
  .admin-menu{display:none!important}
  .admin-sticky-controls .message{position:static!important;margin:6px 0 0!important}
  #editor{position:relative!important;z-index:1!important;overflow:visible!important;min-height:0!important;padding-top:0!important;scroll-margin-top:0!important}
}
</style>
</head>
<body>
<header class="admin-header">
  <div class="admin-brand">
    <span class="admin-brand-mark">AB</span>
    <div><strong>Tennisschule Alex Braun</strong><span>Website-Verwaltung</span></div>
  </div>
  <div class="admin-actions">
    <a class="admin-site-link" href="/" target="_blank" rel="noopener">Website öffnen ↗</a>
    <button id="logoutBtn" class="secondary compact" hidden>Abmelden</button>
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
    <section id="contentAuditWrap" class="content-audit-wrap" hidden>
      <button id="contentAuditToggle" class="content-audit-toggle" type="button" aria-expanded="true" aria-controls="contentAudit">
        <span>
          <strong>Inhaltsprüfung</strong>
          <span id="contentAuditSummary" class="content-audit-summary"></span>
        </span>
        <span class="content-audit-toggle-icon" aria-hidden="true">⌃</span>
      </button>
      <div id="contentAudit" class="content-audit"></div>
    </section>

    <div class="admin-sticky-controls">
      <div class="editor-top">
        <div class="editor-heading">
          <span class="editor-kicker">Aktueller Bereich</span>
          <h1 id="currentSectionTitle">Allgemein</h1>
          <p id="currentSectionDescription">Grundlegende Website-Einstellungen, Navigation, Hero und Footer.</p>
        </div>
        <label class="mobile-section-picker" for="mobileSectionSelect">
          <span>Bereich</span>
          <select id="mobileSectionSelect" aria-label="CMS-Bereich auswählen">
            <option value="allgemein">Allgemein</option>
            <option value="home">Home</option>
            <option value="angebote">Angebote</option>
            <option value="trainerteam">Trainerteam</option>
            <option value="standorte">Standorte</option>
            <option value="kontakt">Kontakt</option>
            <option value="newsletter">Newsletter</option>
            <option value="weitere">Weitere Seiten</option>
            <option value="statistik">Statistik</option>
          </select>
        </label>
        <div class="save-cluster">
          <span id="dirtyState" class="dirty-state saved">Gespeichert</span>
          <button id="saveBtn">Änderungen speichern</button>
        </div>
      </div>
      <nav class="admin-menu" id="adminMenu" aria-label="Bereiche">
        <button type="button" class="admin-menu-item active" data-admin-tab="allgemein">Allgemein</button>
        <button type="button" class="admin-menu-item" data-admin-tab="home">Home</button>
        <button type="button" class="admin-menu-item" data-admin-tab="angebote">Angebote</button>
        <button type="button" class="admin-menu-item" data-admin-tab="trainerteam">Trainerteam</button>
        <button type="button" class="admin-menu-item" data-admin-tab="standorte">Standorte</button>
        <button type="button" class="admin-menu-item" data-admin-tab="kontakt">Kontakt</button>
        <button type="button" class="admin-menu-item" data-admin-tab="newsletter">Newsletter</button>
        <button type="button" class="admin-menu-item" data-admin-tab="weitere">Weitere Seiten</button>
        <button type="button" class="admin-menu-item stats-tab" data-admin-tab="statistik">Statistik</button>
      </nav>
      <div id="saveMessage" class="message"></div>
      <div id="conflictBanner" class="cms-conflict-banner" hidden>
        <div><strong>Neuere Inhalte vorhanden</strong><span>Dieser CMS-Tab ist nicht mehr aktuell. Speichern wurde gestoppt, damit keine neueren Änderungen überschrieben werden.</span></div>
        <button id="reloadLatestBtn" type="button" class="secondary compact">Neueste Version laden</button>
      </div>
    </div>

    <div id="editor"></div>
  </section>
</main>
<script src="/admin/admin.js"></script>
</body>
</html>
