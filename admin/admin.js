(() => {
  const $=s=>document.querySelector(s);
  const loginPanel=$('#loginPanel'),editorPanel=$('#editorPanel'),loginForm=$('#loginForm'),loginMessage=$('#loginMessage');
  const logoutBtn=$('#logoutBtn'),saveBtn=$('#saveBtn'),saveMessage=$('#saveMessage'),editor=$('#editor'),adminMenu=$('#adminMenu');
  const currentSectionTitle=$('#currentSectionTitle'),currentSectionDescription=$('#currentSectionDescription'),dirtyState=$('#dirtyState');
  let storedTab='allgemein';
  try{storedTab=sessionStorage.getItem('ts_admin_tab')||'allgemein';}catch(_){}
  let data=null,csrf='',currentTab=storedTab,statsDays=30,dirty=false;

  const sectionMeta={
    allgemein:['Allgemein','Grundlegende Website-Einstellungen, Navigation, Hero und Footer.'],
    home:['Home','Startseite, Vertrauensbereiche, Angebote-Teaser, Trainerteaser, Popup und Bilder.'],
    angebote:['Angebote','Trainingsangebote, Status, Preise, Anmeldungen, FAQ und Galerie.'],
    trainerteam:['Trainerteam','Trainer verwalten, hinzufügen, löschen und Detailinformationen bearbeiten.'],
    standorte:['Standorte','Vereine, Standorttexte, Links, Logos, Karten und Routen bearbeiten.'],
    kontakt:['Kontakt','Kontaktseite, Formularfelder, Anliegen-Auswahl und Rückmeldungen bearbeiten.'],
    newsletter:['Newsletter','Anmeldung, Double-Opt-in-Texte und Abonnenten verwalten.'],
    weitere:['Weitere Seiten','SEO, Erfolgsseite, 404 sowie Impressum und Datenschutz bearbeiten.'],
    statistik:['Statistik','Seitenaufrufe, Conversions, Klicks und Besucherherkunft auswerten.']
  };

  function updateDirtyState(state=dirty?'dirty':'saved'){
    if(!dirtyState)return;
    dirtyState.className='dirty-state '+state;
    dirtyState.textContent=state==='saving'?'Wird gespeichert …':state==='dirty'?'Ungespeichert':'Gespeichert';
    if(saveBtn) saveBtn.textContent=state==='saving'?'Speichert …':'Änderungen speichern';
  }
  function markDirty(){
    if(!data)return;
    dirty=true;
    updateDirtyState('dirty');
  }

  const syncAdminHeaderHeight=()=>{
    const header=document.querySelector('.admin-header');
    document.documentElement.style.setProperty('--admin-header-height',(header?.offsetHeight||72)+'px');
  };
  syncAdminHeaderHeight();
  window.addEventListener('resize',syncAdminHeaderHeight);
  if('ResizeObserver'in window){
    const header=document.querySelector('.admin-header');
    if(header)new ResizeObserver(syncAdminHeaderHeight).observe(header);
  }

  const esc=(v='')=>String(v).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'","&#039;");
  const setPath=(obj,path,value)=>{const p=path.split('.');let c=obj;for(let i=0;i<p.length-1;i++)c=c[p[i]];c[p.at(-1)]=value;markDirty();};
  async function request(url,options={}){const r=await fetch(url,{credentials:'same-origin',...options});const b=await r.json().catch(()=>({ok:false,error:'Ungültige Serverantwort.'}));if(!r.ok||b.ok===false)throw new Error(b.error||'Fehler');return b;}
  function msg(el,text,type=''){el.textContent=text||'';el.className='message'+(type?' '+type:'');}

  function textField(label,path,value,textarea=false,note=''){
    return `<label>${esc(label)}${textarea?`<textarea data-path="${esc(path)}">${esc(value??'')}</textarea>`:`<input data-path="${esc(path)}" value="${esc(value??'')}">`}${note?`<span class="field-note">${esc(note)}</span>`:''}</label>`;
  }
  function numberField(label,path,value,min=0,max=999,note=''){
    return `<label>${esc(label)}<input type="number" min="${min}" max="${max}" step="1" data-number-path="${esc(path)}" value="${esc(value??0)}">${note?`<span class="field-note">${esc(note)}</span>`:''}</label>`;
  }
  function checkboxField(label,path,value,note=''){
    return `<label class="field-inline"><input type="checkbox" data-bool-path="${esc(path)}" ${value?'checked':''}><span>${esc(label)}${note?`<span class="field-note">${esc(note)}</span>`:''}</span></label>`;
  }
  function selectField(label,path,value,options,note=''){
    return `<label>${esc(label)}<select data-path="${esc(path)}">${options.map(o=>`<option value="${esc(o.value)}" ${o.value===value?'selected':''}>${esc(o.label)}</option>`).join('')}</select>${note?`<span class="field-note">${esc(note)}</span>`:''}</label>`;
  }
  function linesField(label,path,value){return `<label>${esc(label)}<textarea data-lines="${esc(path)}">${esc((value||[]).join('\n'))}</textarea></label>`;}
  function preview(value,label='Aktuell verwendetes Bild',wide=false){return value?`<div class="current-image${wide?' wide':''}"><span class="current-image-label">${esc(label)}</span><img src="${esc(value)}" alt="" loading="lazy"></div>`:'';}
  function imageField(label,path,value,wide=false){return `<div class="image-row"><div>${textField(label,path,value)}${preview(value,'Aktuell verwendet',wide)}</div><label>Neues Bild<input class="cms-upload" data-upload-path="${esc(path)}" type="file" accept="image/jpeg,image/png,image/webp"></label></div>`;}

  function showTab(tab){
    if(!sectionMeta[tab])tab='allgemein';
    currentTab=tab;
    try{sessionStorage.setItem('ts_admin_tab',tab);}catch(_){}
    adminMenu?.querySelectorAll('[data-admin-tab]').forEach(b=>{
      const active=b.dataset.adminTab===tab;
      b.classList.toggle('active',active);
      if(active)b.setAttribute('aria-current','page');else b.removeAttribute('aria-current');
    });
    editor?.querySelectorAll('[data-admin-section]').forEach(s=>s.hidden=s.dataset.adminSection!==tab);
    const meta=sectionMeta[tab]||sectionMeta.allgemein;
    if(currentSectionTitle)currentSectionTitle.textContent=meta[0];
    if(currentSectionDescription)currentSectionDescription.textContent=meta[1];
    if(editor)editor.scrollTop=0;
    if(tab==='statistik') loadStats(statsDays);
    if(tab==='newsletter') loadNewsletterAdmin();
  }

  function render(){
    if(!data)return;
    const s=data.site||{}, nav=s.navigation||{}, foot=s.footer||{}, h=data.home||{}, offers=data.offers?.items||[], trainers=data.trainers?.items||[];
    const lf=data.locations?.feldkirchen||{}, lh=data.locations?.heimstetten||{}, contact=data.contact||{}, labels=contact.labels||{}, legal=data.legal||{};

    editor.innerHTML=`
    <section class="cms-section" data-admin-section="allgemein">
      <h2>Allgemein</h2>
      ${textField('Name der Website','site.name',s.name)}
      ${imageField('Logo','site.logo',s.logo)}
      <div class="cms-card"><strong>Navigation</strong><div class="grid2">
        ${textField('Home','site.navigation.home',nav.home)}${textField('Angebote','site.navigation.offers',nav.offers)}
        ${textField('Trainerteam','site.navigation.trainers',nav.trainers)}${textField('Standorte','site.navigation.locations',nav.locations)}
        ${textField('Übersicht im Standortmenü','site.navigation.locations_overview',nav.locations_overview)}${textField('Shop','site.navigation.shop',nav.shop)}
        ${textField('Shop-URL','site.navigation.shop_url',nav.shop_url)}${textField('Kontakt','site.navigation.contact',nav.contact)}
      </div></div>
      <div class="cms-card"><strong>Hero</strong>
        ${textField('Überschrift Desktop','site.hero_title',s.hero_title)}
        ${textField('Überschrift Mobil','site.hero_title_mobile',s.hero_title_mobile,true,'Zeilenumbruch ist erlaubt.')}
        ${textField('Unterzeile','site.tagline',s.tagline)}
        ${textField('Hero-Text','site.hero_copy',s.hero_copy,true)}
        ${imageField('Hero-Bild','site.hero_image',s.hero_image,true)}
        <div class="grid2">${textField('Button 1 Text','site.hero_primary_button',s.hero_primary_button)}${textField('Button 1 Ziel','site.hero_primary_url',s.hero_primary_url)}
        ${textField('Button 2 Text','site.hero_secondary_button',s.hero_secondary_button)}${textField('Button 2 Ziel','site.hero_secondary_url',s.hero_secondary_url)}</div>
      </div>
      <div class="cms-card"><strong>Footer & Kontaktwege</strong><div class="grid2">
        ${textField('Footer-Name','site.footer.name',foot.name)}${textField('Impressum-Linktext','site.footer.legal_label',foot.legal_label)}
        ${textField('Copyright-Name','site.footer.copyright_name',foot.copyright_name)}${textField('Telefon','site.footer.phone',foot.phone)}
        ${textField('E-Mail','site.footer.email',foot.email)}${textField('Instagram-URL','site.footer.instagram',foot.instagram)}
      </div>
      ${(foot.links||[]).map((l,i)=>`<div class="grid2">${textField('Footer-Link '+(i+1),`site.footer.links.${i}.label`,l.label)}${textField('Ziel',`site.footer.links.${i}.url`,l.url)}</div>`).join('')}
      </div>
      <div class="cms-card"><strong>Mobile Schnellaktionen</strong>
        ${checkboxField('Sticky-CTA auf Mobilgeräten aktiv','mobile_cta.enabled',data.mobile_cta?.enabled)}
        <div class="grid2">
          ${textField('Button 1 Text','mobile_cta.primary_label',data.mobile_cta?.primary_label)}${textField('Button 1 Ziel','mobile_cta.primary_url',data.mobile_cta?.primary_url)}
          ${textField('Button 2 Text','mobile_cta.secondary_label',data.mobile_cta?.secondary_label)}${textField('Button 2 Ziel','mobile_cta.secondary_url',data.mobile_cta?.secondary_url)}
        </div>
      </div>
    </section>

    <section class="cms-section" data-admin-section="home" hidden>
      <h2>Home</h2>
      ${textField('Überschrift Über uns','home.about_title',h.about_title)}
      ${(h.about_paragraphs||[]).map((p,i)=>textField('Über uns – Absatz '+(i+1),'home.about_paragraphs.'+i,p,true)).join('')}
      ${imageField('Bild Über uns','home.about_image',h.about_image,true)}
      <div class="cms-card"><strong>Vertrauenszeile</strong>
        ${checkboxField('Vertrauenszeile anzeigen','home.trust_enabled',h.trust_enabled)}
        ${linesField('Punkte – eine Zeile pro Eintrag','home.trust_items',h.trust_items)}
      </div>
      <div class="cms-card"><strong>Warum wir?</strong>
        ${checkboxField('Sektion anzeigen','home.why_enabled',h.why_enabled)}
        ${textField('Überschrift','home.why_title',h.why_title)}
        ${(h.why_items||[]).map((w,i)=>`<div class="grid2">${textField('Titel '+(i+1),`home.why_items.${i}.title`,w.title)}${textField('Text '+(i+1),`home.why_items.${i}.text`,w.text,true)}</div>`).join('')}
      </div>
      <div class="cms-card"><strong>Angebotsbereich</strong>
        ${textField('Überschrift','home.offers_title',h.offers_title)}${textField('Einleitung','home.offers_intro',h.offers_intro,true)}
        <div class="grid2">${textField('Linktext auf Karten','home.offer_link_text',h.offer_link_text)}${textField('Linkziel','home.offer_link_url',h.offer_link_url)}</div>
      </div>
      <div class="cms-card"><strong>Trainerteam-Teaser</strong>
        ${textField('Überschrift','home.team_title',h.team_title)}${textField('Einleitung','home.team_intro',h.team_intro,true)}
        ${imageField('Trainerteam-Bild','home.team_image',h.team_image,true)}
        <div class="grid2">${textField('Buttontext','home.team_button_text',h.team_button_text)}${textField('Buttonziel','home.team_button_url',h.team_button_url)}</div>
      </div>
      <div class="cms-card"><strong>Standorte & Mapbox</strong>
        ${textField('Überschrift','home.locations_title',h.locations_title)}${textField('Marker-Text','home.map_route_label',h.map_route_label)}
      </div>
      <div class="cms-card"><strong>Popup</strong>
        ${checkboxField('Popup aktiv','home.popup_enabled',h.popup_enabled,'Wenn deaktiviert, wird es Besuchern gar nicht angezeigt.')}
        <div class="grid2">
          ${numberField('Erneut anzeigen nach Tagen','home.popup_frequency_days',h.popup_frequency_days,0,365,'7 = höchstens einmal alle 7 Tage; 0 = bei jedem Besuch.')}
          ${numberField('Öffnen nach Sekunden','home.popup_delay_seconds',h.popup_delay_seconds,0,60)}
          ${numberField('Schließen-X nach Sekunden','home.popup_close_delay_seconds',h.popup_close_delay_seconds,0,30)}
          ${textField('Popup-Ziel','home.popup_link',h.popup_link)}
        </div>
        ${selectField('Popup-Darstellung','home.popup_mode',h.popup_mode,[{value:'html',label:'Modernes HTML-Popup'},{value:'image',label:'Bild-Fokus'}])}
        ${textField('Überschrift','home.popup_heading',h.popup_heading)}
        ${textField('Text','home.popup_text',h.popup_text,true)}
        ${textField('Buttontext','home.popup_button_text',h.popup_button_text)}
        ${textField('Alternativtext','home.popup_alt',h.popup_alt)}
        ${imageField('Popup-Bild','home.popup_image',h.popup_image,true)}
      </div>
    </section>

    <section class="cms-section" data-admin-section="angebote" hidden>
      <h2>Angebote</h2>
      ${textField('Seitenüberschrift','offers.page_title',data.offers?.page_title)}
      ${textField('Einleitung','offers.intro',data.offers?.intro,true)}
      <div class="grid2">
        ${textField('Hover-Text der Anmeldebuttons','offers.hover_text',data.offers?.hover_text)}
        ${textField('Kennzeichnung des aktuellen Angebots','offers.featured_label',data.offers?.featured_label)}
      </div>

      ${offers.map((o,i)=>`<div class="cms-card">
        <div class="item-head"><strong>${esc(o.title||'Angebot')}</strong>${o.featured?'<span class="offer-admin-current">Aktuell hervorgehoben</span>':''}</div>
        <div class="grid2">
          ${textField('Titel',`offers.items.${i}.title`,o.title)}
          ${textField('Buttontext',`offers.items.${i}.button`,o.button)}
        </div>

        ${textField('Beschreibung',`offers.items.${i}.home_text`,o.home_text,true)}
        ${imageField('Bild',`offers.items.${i}.image`,o.image,true)}

        <div class="grid2">
          ${textField('Zielgruppe / Für wen',`offers.items.${i}.target_group`,o.target_group,'','Leer lassen, wenn keine sichere Angabe vorhanden ist.')}
          ${textField('Schwerpunkte',`offers.items.${i}.focus`,o.focus)}
          ${textField('Trainingsumfang',`offers.items.${i}.training_scope`,o.training_scope)}
          ${textField('Zeitraum',`offers.items.${i}.period`,o.period)}
          ${textField('Standort',`offers.items.${i}.location`,o.location)}
          ${textField('Preis',`offers.items.${i}.price`,o.price)}
        </div>

        <div class="grid2">
          ${textField('Anmeldestatus',`offers.items.${i}.status`,o.status)}
          ${selectField('Statusdarstellung',`offers.items.${i}.status_kind`,o.status_kind,[{value:'open',label:'Offen / grün'},{value:'upcoming',label:'Bald buchbar / dunkel'},{value:'closed',label:'Geschlossen / dunkel'}])}
        </div>
        ${checkboxField('Als aktuelles Angebot hervorheben',`offers.items.${i}.featured`,o.featured,'Für die aktuelle Saison nur gezielt verwenden.')}

        <div class="grid2">
          ${textField('Startseite: CTA-Text',`offers.items.${i}.home_cta_label`,o.home_cta_label)}
          ${textField('Startseite: CTA-Ziel',`offers.items.${i}.home_cta_url`,o.home_cta_url)}
        </div>

        ${textField('Hinweis, wenn nicht buchbar',`offers.items.${i}.unavailable_message`,o.unavailable_message)}
        ${o.id==='winter'?textField('Formular-URL',`offers.items.${i}.form_url`,o.form_url):''}
        ${o.id==='camps'?'<div class="grid2">'+textField('Camp-Button Feldkirchen',`offers.items.${i}.button_feldkirchen`,o.button_feldkirchen,true)+textField('Camp-Button Heimstetten',`offers.items.${i}.button_heimstetten`,o.button_heimstetten,true)+'</div>':''}
      </div>`).join('')}

      <div class="cms-card"><strong>FAQ</strong>
        ${checkboxField('FAQ anzeigen','faq.enabled',data.faq?.enabled)}
        ${textField('FAQ-Überschrift','faq.title',data.faq?.title)}
        ${textField('FAQ-Einleitung','faq.intro',data.faq?.intro,true)}
        ${(data.faq?.items||[]).map((item,i)=>`<div class="legal-section-editor">${textField('Frage',`faq.items.${i}.question`,item.question)}${textField('Antwort',`faq.items.${i}.answer`,item.answer,true)}</div>`).join('')}
      </div>
      <div class="cms-card"><strong>Galerie</strong>
        ${(data.offers?.gallery||[]).map((img,i)=>imageField('Galeriebild '+(i+1),`offers.gallery.${i}`,img)).join('')}
      </div>
    </section>

    <section class="cms-section" data-admin-section="trainerteam" hidden>
      <div class="trainer-section-head">
        <div>
          <h2>Trainerteam</h2>
          <p class="muted">Aktuell ${trainers.length} Trainer im Team.</p>
        </div>
        <button type="button" class="add-trainer">+ Trainer hinzufügen</button>
      </div>

      <div class="trainer-overview">
        ${trainers.map((t,i)=>`
          <article class="trainer-overview-card">
            <img src="${esc(t.image||'/assets/media/logo.png')}" alt="${esc(t.name||'Trainer')}" loading="lazy">
            <div class="trainer-overview-info">
              <strong>${esc(t.name||'Trainer')}</strong>
              <span>${esc(t.role||'')}</span>
            </div>
            <div class="trainer-overview-actions">
              <button type="button" class="secondary small edit-trainer" data-index="${i}">Bearbeiten</button>
              <button type="button" class="danger small remove-trainer" data-index="${i}">Löschen</button>
            </div>
          </article>
        `).join('')}
        ${trainers.length===0?'<div class="trainer-overview-empty">Noch keine Trainer vorhanden.</div>':''}
      </div>

      <div class="trainer-page-settings">
        ${textField('Seitenüberschrift','trainers.page_title',data.trainers?.page_title)}
        ${textField('Einleitung','trainers.intro',data.trainers?.intro,true)}
      </div>

      <div class="trainer-editor-list">
        ${trainers.map((t,i)=>`<div class="cms-card trainer-editor-card" data-trainer-editor="${i}">
          <div class="item-head">
            <strong>${esc(t.name||'Trainer')}</strong>
            <button type="button" class="danger small remove-trainer" data-index="${i}">Löschen</button>
          </div>
          <div class="grid2">
            ${textField('Name',`trainers.items.${i}.name`,t.name)}
            ${textField('Rolle',`trainers.items.${i}.role`,t.role)}
          </div>
          ${imageField('Bild',`trainers.items.${i}.image`,t.image)}
          ${textField('Qualifikationen',`trainers.items.${i}.qualifications`,t.qualifications,true)}
          ${textField('Erfahrung',`trainers.items.${i}.experience`,t.experience,true)}
          ${textField('Trainingsschwerpunkte',`trainers.items.${i}.focus`,t.focus,true)}
          ${textField('Kurzbeschreibung',`trainers.items.${i}.bio`,t.bio,true)}
        </div>`).join('')}
      </div>

      <div class="grid2">${textField('CTA-Text','trainers.cta_label',data.trainers?.cta_label)}${textField('CTA-Ziel','trainers.cta_url',data.trainers?.cta_url)}</div>
      <button type="button" class="secondary add-trainer bottom-add-trainer">+ Trainer hinzufügen</button>
    </section>

    <section class="cms-section" data-admin-section="standorte" hidden>
      <h2>Standorte</h2>
      ${textField('Seitenüberschrift','locations.page_title',data.locations?.page_title)}
      ${textField('Karten-Hinweis','locations.map_consent_text',data.locations?.map_consent_text,true)}
      ${textField('Karten-Button','locations.map_consent_button',data.locations?.map_consent_button)}
      <div class="grid2">${textField('Standort-Button','locations.location_button',data.locations?.location_button)}${textField('Routen-Button','locations.route_button',data.locations?.route_button)}
      ${textField('CTA-Text','locations.cta_label',data.locations?.cta_label)}${textField('CTA-Ziel','locations.cta_url',data.locations?.cta_url)}</div>
      ${locationEditor('feldkirchen','TSV Feldkirchen',lf)}
      ${locationEditor('heimstetten','SV Heimstetten',lh,true)}
    </section>

    <section class="cms-section" data-admin-section="kontakt" hidden>
      <h2>Kontakt</h2>
      ${textField('Seitentitel','contact.page_title',contact.page_title)}${textField('Überschrift','contact.heading',contact.heading)}
      ${imageField('Kontaktbild','contact.image',contact.image,true)}
      <div class="cms-card"><strong>Formularbeschriftungen</strong><div class="grid2">
      ${textField('Name','contact.labels.name',labels.name)}${textField('E-Mail','contact.labels.email',labels.email)}
      ${textField('Telefon','contact.labels.phone',labels.phone)}${textField('Anliegen','contact.labels.topic',labels.topic)}
      ${textField('Nachricht','contact.labels.message',labels.message)}${textField('Senden','contact.labels.submit',labels.submit)}
      </div>
      ${checkboxField('Telefon ist Pflichtfeld','contact.phone_required',contact.phone_required)}
      ${linesField('Auswahl Anliegen – eine Zeile pro Eintrag','contact.topics',contact.topics)}
      ${textField('Erfolgsmeldung','contact.success_message',contact.success_message,true)}${textField('Fehlermeldung','contact.error_message',contact.error_message,true)}</div>
    </section>

    <section class="cms-section" data-admin-section="newsletter" hidden>
      <h2>Newsletter</h2>
      <div class="cms-card"><strong>Anmeldung</strong>
        ${checkboxField('Newsletter-Anmeldung aktiv','newsletter.enabled',data.newsletter?.enabled)}
        ${textField('Seitentitel','newsletter.page_title',data.newsletter?.page_title)}
        ${textField('Willkommenstext','newsletter.welcome',data.newsletter?.welcome)}
        ${textField('Überschrift Anmeldung','newsletter.heading',data.newsletter?.heading)}
        ${textField('Beschreibung','newsletter.signup_text',data.newsletter?.signup_text,true)}
        <div class="grid2">
          ${textField('Name-Feld','newsletter.name_label',data.newsletter?.name_label)}
          ${textField('E-Mail-Feld','newsletter.email_label',data.newsletter?.email_label)}
        </div>
        ${textField('Einwilligungstext','newsletter.consent_label',data.newsletter?.consent_label,true)}
        <div class="grid2">
          ${textField('Datenschutz-Linktext','newsletter.privacy_label',data.newsletter?.privacy_label)}
          ${textField('Buttontext','newsletter.submit_label',data.newsletter?.submit_label)}
        </div>
      </div>
      <div class="cms-card"><strong>Double-Opt-in-E-Mails</strong>
        ${textField('Betreff Bestätigung','newsletter.confirmation_subject',data.newsletter?.confirmation_subject)}
        ${textField('Text Bestätigung','newsletter.confirmation_intro',data.newsletter?.confirmation_intro,true)}
        ${textField('Betreff nach Bestätigung','newsletter.welcome_subject',data.newsletter?.welcome_subject)}
        ${textField('Text nach Bestätigung','newsletter.welcome_text',data.newsletter?.welcome_text,true)}
        ${textField('Hinweis nach Anmeldung','newsletter.pending_message',data.newsletter?.pending_message,true)}
        ${textField('Hinweis nach Bestätigung','newsletter.confirmed_message',data.newsletter?.confirmed_message,true)}
      </div>
      <div class="cms-card"><strong>Abmeldung</strong>
        ${textField('Seitentitel','newsletter.unsubscribe_page_title',data.newsletter?.unsubscribe_page_title)}
        ${textField('Bestätigungstext','newsletter.unsubscribe_confirm_text',data.newsletter?.unsubscribe_confirm_text,true)}
        ${textField('Abmeldebutton','newsletter.unsubscribe_button',data.newsletter?.unsubscribe_button)}
        ${textField('Text nach Abmeldung','newsletter.unsubscribed_message',data.newsletter?.unsubscribed_message,true)}
      </div>
      <div class="cms-card">
        <div class="item-head"><strong>Abonnenten</strong><div class="newsletter-admin-toolbar">
          <button type="button" class="secondary" id="newsletterRefresh">Aktualisieren</button>
          <a class="admin-download-link" href="/api/newsletter-admin.php?format=csv">CSV exportieren</a>
        </div></div>
        <div id="newsletterSubscribers"><p class="muted">Abonnenten werden geladen …</p></div>
      </div>
    </section>

    <section class="cms-section" data-admin-section="weitere" hidden>
      <h2>Weitere Seiten</h2>
      <div class="cms-card"><strong>Erfolgsseite</strong>
        ${textField('Seitentitel','success.page_title',data.success?.page_title)}${textField('Nachricht','success.message',data.success?.message,true)}
      </div>
      <div class="cms-card"><strong>404 – Seite nicht gefunden</strong>
        ${textField('Überschrift','not_found.title',data.not_found?.title)}${textField('Text','not_found.text',data.not_found?.text,true)}
        <div class="grid2">${textField('Button 1 Text','not_found.primary_label',data.not_found?.primary_label)}${textField('Button 1 Ziel','not_found.primary_url',data.not_found?.primary_url)}
        ${textField('Button 2 Text','not_found.secondary_label',data.not_found?.secondary_label)}${textField('Button 2 Ziel','not_found.secondary_url',data.not_found?.secondary_url)}</div>
      </div>
      <div class="cms-card"><strong>SEO & Teilen</strong>
        ${textField('Basis-URL','seo.base_url',data.seo?.base_url)}
        ${imageField('Standard Open-Graph-Bild','seo.default_og_image',data.seo?.default_og_image,true)}
        ${Object.entries(data.seo?.pages||{}).map(([k,m])=>`<div class="legal-section-editor"><strong>${esc(k)}</strong>${textField('Titel',`seo.pages.${k}.title`,m.title)}${textField('Beschreibung',`seo.pages.${k}.description`,m.description,true)}${textField('Pfad',`seo.pages.${k}.path`,m.path)}</div>`).join('')}
      </div>
      <div class="cms-card"><strong>Impressum & Datenschutz</strong>
        ${textField('Seitentitel','legal.page_title',legal.page_title)}${textField('Einleitung','legal.intro',legal.intro,true)}
        ${(legal.sections||[]).map((sec,i)=>`<div class="legal-section-editor">${textField('Überschrift',`legal.sections.${i}.title`,sec.title)}${textField('Text',`legal.sections.${i}.body`,sec.body,true)}</div>`).join('')}
      </div>
    </section>

    <section class="cms-section" data-admin-section="statistik" hidden>
      <h2>Statistik</h2>
      ${checkboxField('Statistik aktiv','analytics.enabled',data.analytics?.enabled,'First-Party-Auswertung ohne IP-Speicherung oder externe Analytics-Plattform.')}
      ${numberField('Aufbewahrung in Tagen','analytics.retention_days',data.analytics?.retention_days,30,730)}
      <div class="stats-toolbar"><button type="button" data-stats-days="7">7 Tage</button><button type="button" data-stats-days="30" class="active">30 Tage</button><button type="button" data-stats-days="90">90 Tage</button></div>
      <div id="statsContent"><p class="muted">Statistik wird geladen …</p></div>
    </section>`;

    bindEditors();
    showTab(currentTab);
  }

  function locationEditor(key,title,l,second=false){
    return `<div class="cms-card"><strong>${esc(title)}</strong>
      ${textField('Name',`locations.${key}.name`,l.name)}
      ${textField('Beschreibung',`locations.${key}.description`,l.description,true)}
      ${textField('Kurztext Übersicht',`locations.${key}.overview_text`,l.overview_text,true)}
      ${textField('Kurzer Fakt',`locations.${key}.fact`,l.fact)}
      ${second?textField('Zweiter Absatz',`locations.${key}.description_2`,l.description_2,true):''}
      ${imageField('Logo',`locations.${key}.logo`,l.logo)}
      <div class="grid2">${textField('Website',`locations.${key}.website`,l.website)}${textField('Website-Button',`locations.${key}.button_website`,l.button_website)}
      ${textField('Mitgliedsantrag',`locations.${key}.membership`,l.membership)}${textField('Mitgliedsantrag-Button',`locations.${key}.button_membership`,l.button_membership)}
      ${textField('Google-Maps-Suchbegriff',`locations.${key}.map_query`,l.map_query)}${textField('Mapbox-Routenlink',`locations.${key}.marker_link`,l.marker_link)}</div>
      ${imageField('Mapbox-Marker',`locations.${key}.marker_image`,l.marker_image)}
    </div>`;
  }

  function bindEditors(){
    editor.querySelectorAll('[data-path]').forEach(el=>el.addEventListener('input',()=>setPath(data,el.dataset.path,el.value)));
    editor.querySelectorAll('[data-number-path]').forEach(el=>el.addEventListener('input',()=>setPath(data,el.dataset.numberPath,Number(el.value))));
    editor.querySelectorAll('[data-bool-path]').forEach(el=>el.addEventListener('change',()=>setPath(data,el.dataset.boolPath,el.checked)));
    editor.querySelectorAll('[data-lines]').forEach(el=>el.addEventListener('input',()=>setPath(data,el.dataset.lines,el.value.split('\n').map(x=>x.trim()).filter(Boolean))));
    editor.querySelectorAll('.cms-upload').forEach(el=>el.addEventListener('change',uploadImage));
    const removeTrainer=(i)=>{
      const trainer=data.trainers.items[i];
      if(!trainer)return;
      if(confirm((trainer.name||'Trainer')+' wirklich löschen?')){
        data.trainers.items.splice(i,1);
        markDirty();
        render();
        showTab('trainerteam');
      }
    };
    editor.querySelectorAll('.remove-trainer').forEach(btn=>btn.addEventListener('click',()=>removeTrainer(Number(btn.dataset.index))));
    editor.querySelectorAll('.add-trainer').forEach(btn=>btn.addEventListener('click',()=>{
      data.trainers.items.push({name:'Neuer Trainer',role:'Trainer',image:'/assets/media/logo.png',qualifications:'',experience:'',focus:'',bio:''});
      markDirty();
      render();
      showTab('trainerteam');
      const newIndex=data.trainers.items.length-1;
      window.setTimeout(()=>editor.querySelector('[data-trainer-editor="'+newIndex+'"]')?.scrollIntoView({behavior:'smooth',block:'start'}),50);
    }));
    editor.querySelectorAll('.edit-trainer').forEach(btn=>btn.addEventListener('click',()=>{
      const i=Number(btn.dataset.index);
      editor.querySelector('[data-trainer-editor="'+i+'"]')?.scrollIntoView({behavior:'smooth',block:'start'});
    }));
    editor.querySelectorAll('[data-stats-days]').forEach(btn=>btn.addEventListener('click',()=>{statsDays=Number(btn.dataset.statsDays);editor.querySelectorAll('[data-stats-days]').forEach(b=>b.classList.toggle('active',b===btn));loadStats(statsDays);}));
    $('#newsletterRefresh')?.addEventListener('click',loadNewsletterAdmin);
  }

  async function uploadImage(e){
    const input=e.currentTarget,file=input.files?.[0];if(!file)return;msg(saveMessage,'Bild wird hochgeladen …');
    const form=new FormData();form.append('file',file);
    try{const r=await request('/api/upload.php',{method:'POST',headers:{'X-CSRF-Token':csrf},body:form});setPath(data,input.dataset.uploadPath,r.path);render();updateDirtyState('dirty');msg(saveMessage,r.github?.ok?'Bild hochgeladen. Bitte Änderungen speichern.':'Bild hochgeladen; GitHub-Sicherung fehlgeschlagen. Bitte Änderungen speichern.','warning');}
    catch(err){msg(saveMessage,err.message,'error');}
  }

  async function loadNewsletterAdmin(){
    const box=$('#newsletterSubscribers');
    if(!box)return;
    box.innerHTML='<p class="muted">Abonnenten werden geladen …</p>';
    try{
      const r=await request('/api/newsletter-admin.php');
      const counts=r.counts||{},subs=r.subscribers||[];
      box.innerHTML=`
        <div class="newsletter-counts">
          <div class="newsletter-count"><strong>${esc(counts.active||0)}</strong><span>Aktiv</span></div>
          <div class="newsletter-count"><strong>${esc(counts.pending||0)}</strong><span>Nicht bestätigt</span></div>
          <div class="newsletter-count"><strong>${esc(counts.unsubscribed||0)}</strong><span>Abgemeldet</span></div>
          <div class="newsletter-count"><strong>${esc(counts.all||0)}</strong><span>Gesamt</span></div>
        </div>
        <div class="newsletter-subscriber-wrap"><table class="newsletter-subscriber-table">
          <thead><tr><th>Name</th><th>E-Mail</th><th>Status</th><th>Angelegt</th><th>Aktionen</th></tr></thead>
          <tbody>${subs.map(s=>`<tr>
            <td>${esc(s.name||'–')}</td>
            <td>${esc(s.email||'')}</td>
            <td><span class="newsletter-status ${esc(s.status||'pending')}">${esc(newsletterStatusLabel(s.status))}</span></td>
            <td>${esc(formatAdminDate(s.created_at))}</td>
            <td><div class="newsletter-row-actions">
              ${s.status==='active'?'<button type="button" class="secondary small newsletter-unsubscribe" data-email="'+esc(s.email)+'">Abmelden</button>':''}
              <button type="button" class="danger small newsletter-delete" data-email="${esc(s.email)}">Löschen</button>
            </div></td>
          </tr>`).join('')||'<tr><td colspan="5">Noch keine Newsletter-Anmeldungen.</td></tr>'}</tbody>
        </table></div>`;
      box.querySelectorAll('.newsletter-unsubscribe').forEach(btn=>btn.addEventListener('click',()=>newsletterAdminAction('unsubscribe',btn.dataset.email)));
      box.querySelectorAll('.newsletter-delete').forEach(btn=>btn.addEventListener('click',()=>newsletterAdminAction('delete',btn.dataset.email)));
    }catch(err){box.innerHTML='<p class="message error">'+esc(err.message)+'</p>';}
  }
  function newsletterStatusLabel(status){
    return status==='active'?'Aktiv':status==='unsubscribed'?'Abgemeldet':'Bestätigung offen';
  }
  function formatAdminDate(value){
    if(!value)return '–';
    try{return new Intl.DateTimeFormat('de-DE',{dateStyle:'short',timeStyle:'short'}).format(new Date(value));}catch(_){return value;}
  }
  async function newsletterAdminAction(action,email){
    const question=action==='delete'?'Eintrag '+email+' wirklich endgültig löschen?':email+' wirklich vom Newsletter abmelden?';
    if(!confirm(question))return;
    try{
      await request('/api/newsletter-admin.php',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':csrf},body:JSON.stringify({action,email})});
      await loadNewsletterAdmin();
    }catch(err){alert(err.message);}
  }

  async function loadStats(days){
    const box=$('#statsContent');if(!box)return;
    box.innerHTML='<p class="muted">Statistik wird geladen …</p>';
    try{
      const r=await request('/api/stats.php?days='+days),s=r.stats||{},t=s.totals||{},events=s.events||{};
      const contact=events.contact_success?.count||0;
      const training=Object.entries(events).filter(([k])=>k.startsWith('training_')).reduce((a,[,v])=>a+(v.count||0),0);
      const popupViews=events.popup_view?.count||0,popupClicks=events.popup_click?.count||0;
      const maxDaily=Math.max(1,...(s.daily||[]).map(x=>x.pageviews||0));
      box.innerHTML=`
        <div class="stats-cards">
          ${statCard(t.pageviews||0,'Seitenaufrufe')}${statCard(t.sessions||0,'Sitzungen')}${statCard(training,'Trainings-Klicks')}${statCard(contact,'Kontakt-Abschlüsse')}
        </div>
        <div class="stats-cards">
          ${statCard(t.sessions?Math.round(training/t.sessions*100)+'%':'0%','Trainings-Klickrate')}${statCard(t.sessions?Math.round(contact/t.sessions*100)+'%':'0%','Kontakt-Abschlussrate')}${statCard(popupViews?Math.round(popupClicks/popupViews*100)+'%':'0%','Popup-Klickrate')}${statCard(t.sessions?((t.pageviews||0)/t.sessions).toFixed(1):'0','Seiten / Sitzung')}
        </div>
        <div class="stats-box"><h3>Verlauf</h3><div class="daily-bars">${(s.daily||[]).map(x=>`<div class="daily-bar" style="height:${Math.max(2,(x.pageviews||0)/maxDaily*100)}%" data-tip="${esc(x.date+': '+(x.pageviews||0))}"></div>`).join('')}</div></div>
        <div class="stats-grid">
          ${tableBox('Top-Seiten',s.pages||{},'Seite')}
          ${eventsBox(events)}
          ${tableBox('Herkunft',s.referrers||{},'Quelle')}
          ${tableBox('Geräte',s.devices||{},'Gerät')}
        </div>`;
    }catch(err){box.innerHTML='<p class="message error">'+esc(err.message)+'</p>';}
  }
  const statCard=(v,l)=>`<div class="stat-card"><strong>${esc(v)}</strong><span>${esc(l)}</span></div>`;
  function tableBox(title,obj,label){const rows=Object.entries(obj).slice(0,12);return `<div class="stats-box"><h3>${esc(title)}</h3><table class="stats-table"><thead><tr><th>${esc(label)}</th><th>Anzahl</th></tr></thead><tbody>${rows.map(([k,v])=>`<tr><td>${esc(k)}</td><td>${esc(v)}</td></tr>`).join('')||'<tr><td colspan="2">Noch keine Daten</td></tr>'}</tbody></table></div>`;}
  function eventsBox(events){const rows=Object.entries(events).slice(0,18);return `<div class="stats-box"><h3>Buttons & Links</h3><table class="stats-table"><thead><tr><th>Aktion</th><th>Klicks</th></tr></thead><tbody>${rows.map(([k,v])=>`<tr><td>${esc(v.label||k)}<br><small>${esc(v.path||'')}</small></td><td>${esc(v.count||0)}</td></tr>`).join('')||'<tr><td colspan="2">Noch keine Daten</td></tr>'}</tbody></table></div>`;}

  async function loadContent(){
    const r=await request('/api/content.php');
    data=r.data;csrf=r.csrf;dirty=false;
    loginPanel.hidden=true;editorPanel.hidden=false;logoutBtn.hidden=false;
    document.body.classList.add('admin-editing');
    render();updateDirtyState('saved');syncAdminHeaderHeight();
  }
  async function checkAuth(){try{const r=await request('/api/auth.php');if(r.authenticated){csrf=r.csrf||'';await loadContent();}}catch(_){}}
  loginForm.addEventListener('submit',async e=>{e.preventDefault();msg(loginMessage,'Anmeldung …');try{const r=await request('/api/auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({password:$('#password').value})});csrf=r.csrf||'';$('#password').value='';await loadContent();msg(loginMessage,'');}catch(err){msg(loginMessage,err.message,'error');}});
  async function saveContent(){
    if(!data||saveBtn.disabled)return;
    saveBtn.disabled=true;updateDirtyState('saving');msg(saveMessage,'Änderungen werden gespeichert …');
    try{
      const r=await request('/api/save.php',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':csrf},body:JSON.stringify({data})});
      dirty=false;updateDirtyState('saved');
      msg(saveMessage,r.github?.ok?'Gespeichert und in GitHub versioniert.':'Website gespeichert; GitHub-Sicherung fehlgeschlagen: '+(r.github?.warning||''),r.github?.ok?'success':'warning');
    }catch(err){
      dirty=true;updateDirtyState('dirty');msg(saveMessage,err.message,'error');
    }finally{saveBtn.disabled=false;}
  }
  saveBtn.addEventListener('click',saveContent);
  logoutBtn.addEventListener('click',async()=>{try{await request('/api/logout.php',{method:'POST',headers:{'X-CSRF-Token':csrf}});}catch(_){}location.reload();});
  adminMenu?.querySelectorAll('[data-admin-tab]').forEach(btn=>btn.addEventListener('click',()=>showTab(btn.dataset.adminTab||'allgemein')));

  document.addEventListener('keydown',e=>{
    if((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='s'&&!editorPanel.hidden){
      e.preventDefault();
      saveContent();
    }
  });
  window.addEventListener('beforeunload',e=>{
    if(!dirty)return;
    e.preventDefault();
    e.returnValue='';
  });

  checkAuth();
})();