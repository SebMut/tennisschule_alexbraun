(() => {
  const loginPanel = document.getElementById('loginPanel');
  const editorPanel = document.getElementById('editorPanel');
  const loginForm = document.getElementById('loginForm');
  const loginMessage = document.getElementById('loginMessage');
  const logoutBtn = document.getElementById('logoutBtn');
  const saveBtn = document.getElementById('saveBtn');
  const saveMessage = document.getElementById('saveMessage');
  const editor = document.getElementById('editor');
  const adminMenu = document.getElementById('adminMenu');

  let data = null;
  let csrf = '';
  let currentTab = 'home';

  const esc = (value='') => String(value)
    .replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;')
    .replaceAll('"','&quot;').replaceAll("'","&#039;");

  const getPath = (obj, path) => path.split('.').reduce((o,k) => o?.[k], obj);
  const setPath = (obj, path, value) => {
    const parts = path.split('.');
    let cur = obj;
    for (let i=0;i<parts.length-1;i++) cur = cur[parts[i]];
    cur[parts.at(-1)] = value;
  };

  async function request(url, options={}) {
    const res = await fetch(url, {credentials:'same-origin', ...options});
    const body = await res.json().catch(() => ({ok:false,error:'Ungültige Serverantwort.'}));
    if (!res.ok || body.ok === false) throw new Error(body.error || 'Fehler');
    return body;
  }

  function message(el, text, type='') {
    el.textContent = text || '';
    el.className = 'message' + (type ? ' ' + type : '');
  }

  function textField(label, path, value, textarea=false) {
    return `<label>${esc(label)}${textarea
      ? `<textarea data-path="${esc(path)}">${esc(value ?? '')}</textarea>`
      : `<input data-path="${esc(path)}" value="${esc(value ?? '')}">`
    }</label>`;
  }

  function linesField(label, path, value) {
    return `<label>${esc(label)}<textarea data-lines="${esc(path)}">${esc((value || []).join('\n'))}</textarea></label>`;
  }

  function imagePreview(value, wide=false, label='Aktuell verwendetes Bild') {
    if (!value) return '';
    return `<div class="current-image${wide ? ' wide' : ''}">
      <span class="current-image-label">${esc(label)}</span>
      <img src="${esc(value)}" alt="" loading="lazy">
    </div>`;
  }

  function imageField(label, path, value, wide=false) {
    return `<div class="image-row">
      <div>
        ${textField(label, path, value)}
        ${imagePreview(value, wide)}
      </div>
      <label>Neues Bild<input class="cms-upload" data-upload-path="${esc(path)}" type="file" accept="image/jpeg,image/png,image/webp"></label>
    </div>`;
  }

  function staticImage(value, label, wide=false) {
    return imagePreview(value, wide, label);
  }

  function showTab(tab) {
    currentTab = tab;
    adminMenu?.querySelectorAll('[data-admin-tab]').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.adminTab === tab);
    });
    editor?.querySelectorAll('[data-admin-section]').forEach(section => {
      section.hidden = section.dataset.adminSection !== tab;
    });
  }

  function render() {
    if (!data) return;
    const offers = data.offers?.items || [];
    const trainers = data.trainers?.items || [];
    const locF = data.locations?.feldkirchen || {};
    const locH = data.locations?.heimstetten || {};

    editor.innerHTML = `
      <section class="cms-section" data-admin-section="home">
        <h2>Home</h2>
        ${textField('Unterzeile im Hero','site.tagline',data.site?.tagline)}
        ${textField('Hero-Text','site.hero_copy',data.site?.hero_copy,true)}
        ${imageField('Hero-Bild','site.hero_image',data.site?.hero_image,true)}
        ${textField('Überschrift Über uns','home.about_title',data.home?.about_title)}
        ${(data.home?.about_paragraphs || []).map((p,i)=>textField('Über uns – Absatz '+(i+1),'home.about_paragraphs.'+i,p,true)).join('')}
        ${imageField('Bild Über uns','home.about_image',data.home?.about_image,true)}
        ${textField('Einleitung Angebot auf Startseite','home.offers_intro',data.home?.offers_intro,true)}
        ${textField('Einleitung Trainerteam auf Startseite','home.team_intro',data.home?.team_intro,true)}
        <div class="current-image-grid">
          ${staticImage('/assets/media/trainerteam_2024.jpeg','Aktuelles Trainerteam-Bild')}
          ${staticImage('/assets/media/popup_wintertraining_2026_2027-2.png','Aktuelles Wintertraining-Popup')}
        </div>
      </section>

      <section class="cms-section" data-admin-section="angebote" hidden>
        <h2>Angebote</h2>
        ${textField('Einleitung','offers.intro',data.offers?.intro,true)}
        ${offers.map((offer,i)=>`
          <div class="cms-card">
            <div class="item-head"><strong>${esc(offer.title || ('Angebot '+(i+1)))}</strong></div>
            <div class="grid2">
              ${textField('Titel',`offers.items.${i}.title`,offer.title)}
              ${textField('Buttontext',`offers.items.${i}.button`,offer.button)}
            </div>
            ${textField('Text auf Startseite',`offers.items.${i}.home_text`,offer.home_text,true)}
            ${imageField('Bild',`offers.items.${i}.image`,offer.image,true)}
            ${linesField('Eckdaten – eine Zeile pro Punkt',`offers.items.${i}.details`,offer.details)}
          </div>
        `).join('')}
        <div class="cms-card">
          <strong>Galeriebilder auf der Angebotsseite</strong>
          <div class="current-image-grid">
            ${['angebote_1.jpg','angebote_2.jpg','angebote_3.jpg','angebote_4.jpg','angebote_5.jpg','angebote_6.jpg'].map((img,i)=>staticImage('/assets/media/'+img,'Galeriebild '+(i+1))).join('')}
          </div>
        </div>
      </section>

      <section class="cms-section" data-admin-section="trainerteam" hidden>
        <h2>Trainerteam</h2>
        ${textField('Einleitung','trainers.intro',data.trainers?.intro,true)}
        ${trainers.map((trainer,i)=>`
          <div class="cms-card">
            <div class="item-head"><strong>${esc(trainer.name || 'Trainer')}</strong><button type="button" class="danger small remove-trainer" data-index="${i}">Entfernen</button></div>
            <div class="grid2">
              ${textField('Name',`trainers.items.${i}.name`,trainer.name)}
              ${textField('Rolle',`trainers.items.${i}.role`,trainer.role)}
            </div>
            ${imageField('Bild',`trainers.items.${i}.image`,trainer.image)}
          </div>
        `).join('')}
        <button type="button" id="addTrainer" class="secondary">Trainer hinzufügen</button>
      </section>

      <section class="cms-section" data-admin-section="standorte" hidden>
        <h2>Standorte</h2>
        <div class="cms-card">
          <strong>TSV Feldkirchen</strong>
          ${textField('Name','locations.feldkirchen.name',locF.name)}
          ${textField('Beschreibung','locations.feldkirchen.description',locF.description,true)}
          ${imageField('Logo','locations.feldkirchen.logo',locF.logo)}
          <div class="grid2">
            ${textField('Website','locations.feldkirchen.website',locF.website)}
            ${textField('Mitgliedsantrag','locations.feldkirchen.membership',locF.membership)}
          </div>
        </div>
        <div class="cms-card">
          <strong>SV Heimstetten</strong>
          ${textField('Name','locations.heimstetten.name',locH.name)}
          ${textField('Beschreibung','locations.heimstetten.description',locH.description,true)}
          ${textField('Zweiter Absatz','locations.heimstetten.description_2',locH.description_2,true)}
          ${imageField('Logo','locations.heimstetten.logo',locH.logo)}
          <div class="grid2">
            ${textField('Website','locations.heimstetten.website',locH.website)}
            ${textField('Mitgliedsantrag','locations.heimstetten.membership',locH.membership)}
          </div>
        </div>
        <div class="cms-card">
          <strong>Mapbox-Marker</strong>
          <div class="current-image-grid">
            ${staticImage('/assets/media/mapbox-tsv.png','Marker TSV Feldkirchen')}
            ${staticImage('/assets/media/mapbox-svh.png','Marker SV Heimstetten')}
          </div>
        </div>
      </section>

      <section class="cms-section" data-admin-section="kontakt" hidden>
        <h2>Kontakt</h2>
        ${staticImage('/assets/media/braun_kontakt.jpg','Aktuelles Kontaktbild',true)}
        <div class="contact-info-box">
          <p><strong>Kontaktformular:</strong> Vorname &amp; Nachname, Betreff, Telefon, E-Mail und Nachricht.</p>
          <p><strong>Empfänger:</strong> info@tennisschule-alexbraun.de</p>
          <p><strong>Hinweis:</strong> Das E-Mail-Passwort wird aus Sicherheitsgründen nicht im CMS angezeigt.</p>
        </div>
      </section>
    `;

    showTab(currentTab);

    editor.querySelectorAll('[data-path]').forEach(el => {
      el.addEventListener('input', () => setPath(data, el.dataset.path, el.value));
    });
    editor.querySelectorAll('[data-lines]').forEach(el => {
      el.addEventListener('input', () => setPath(data, el.dataset.lines, el.value.split('\n').map(x=>x.trim()).filter(Boolean)));
    });
    editor.querySelectorAll('.remove-trainer').forEach(btn => {
      btn.addEventListener('click', () => {
        const i = Number(btn.dataset.index);
        if (Number.isInteger(i) && confirm('Trainer wirklich entfernen?')) {
          data.trainers.items.splice(i,1);
          render();
        }
      });
    });
    document.getElementById('addTrainer')?.addEventListener('click', () => {
      data.trainers.items.push({name:'Neuer Trainer',role:'Trainer',image:'/assets/media/logo.png'});
      render();
    });
    editor.querySelectorAll('.cms-upload').forEach(input => input.addEventListener('change', uploadImage));
  }

  async function uploadImage(event) {
    const input = event.currentTarget;
    const file = input.files?.[0];
    if (!file) return;
    message(saveMessage, 'Bild wird hochgeladen …');
    const form = new FormData();
    form.append('file', file);
    try {
      const result = await request('/api/upload.php', {
        method:'POST',
        headers:{'X-CSRF-Token':csrf},
        body:form
      });
      setPath(data, input.dataset.uploadPath, result.path);
      render();
      if (result.github?.ok) message(saveMessage, 'Bild hochgeladen und in GitHub gespeichert.', 'success');
      else message(saveMessage, 'Bild hochgeladen. GitHub-Sicherung konnte nicht erstellt werden: ' + (result.github?.warning || ''), 'warning');
    } catch (e) {
      message(saveMessage, e.message, 'error');
    }
  }

  async function loadContent() {
    const result = await request('/api/content.php');
    data = result.data;
    csrf = result.csrf;
    loginPanel.hidden = true;
    editorPanel.hidden = false;
    logoutBtn.hidden = false;
    render();
  }

  async function checkAuth() {
    try {
      const result = await request('/api/auth.php');
      if (result.authenticated) {
        csrf = result.csrf || '';
        await loadContent();
      }
    } catch (_) {}
  }

  loginForm.addEventListener('submit', async e => {
    e.preventDefault();
    message(loginMessage, 'Anmeldung …');
    try {
      const result = await request('/api/auth.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({password:document.getElementById('password').value})
      });
      csrf = result.csrf || '';
      document.getElementById('password').value = '';
      await loadContent();
      message(loginMessage, '');
    } catch (err) {
      message(loginMessage, err.message, 'error');
    }
  });

  saveBtn.addEventListener('click', async () => {
    saveBtn.disabled = true;
    message(saveMessage, 'Änderungen werden gespeichert …');
    try {
      const result = await request('/api/save.php', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-Token':csrf},
        body:JSON.stringify({data})
      });
      if (result.github?.ok) {
        message(saveMessage, 'Gespeichert. Die Website ist aktualisiert und die Änderung wurde in GitHub versioniert.', 'success');
      } else {
        message(saveMessage, 'Website wurde aktualisiert. GitHub-Sicherung fehlgeschlagen: ' + (result.github?.warning || 'unbekannter Fehler'), 'warning');
      }
    } catch (err) {
      message(saveMessage, err.message, 'error');
    } finally {
      saveBtn.disabled = false;
    }
  });

  logoutBtn.addEventListener('click', async () => {
    try {
      await request('/api/logout.php', {method:'POST',headers:{'X-CSRF-Token':csrf}});
    } catch (_) {}
    location.reload();
  });

  adminMenu?.querySelectorAll('[data-admin-tab]').forEach(btn => {
    btn.addEventListener('click', () => showTab(btn.dataset.adminTab || 'home'));
  });

  checkAuth();
})();