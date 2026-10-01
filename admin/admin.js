(() => {
  const $=s=>document.querySelector(s);
  const loginPanel=$('#loginPanel'),editorPanel=$('#editorPanel'),loginForm=$('#loginForm'),loginMessage=$('#loginMessage');
  const logoutBtn=$('#logoutBtn'),saveBtn=$('#saveBtn'),saveMessage=$('#saveMessage'),editor=$('#editor'),adminMenu=$('#adminMenu');
  let data=null,csrf='',currentTab='allgemein',statsDays=30;

  const esc=(v='')=>String(v).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'","&#039;");
  const setPath=(obj,path,value)=>{const p=path.split('.');let c=obj;for(let i=0;i<p.length-1;i++)c=c[p[i]];c[p.at(-1)]=value;};
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
  function linesField(label,path,value){return `<label>${esc(label)}<textarea data-lines="${esc(path)}">${esc((value||[]).join('\n'))}</textarea></label>`;}
  function preview(value,label='Aktuell verwendetes Bild',wide=false){return value?`<div class="current-image${wide?' wide':''}"><span class="current-image-label">${esc(label)}</span><img src="${esc(value)}" alt="" loading="lazy"></div>`:'';}
  function imageField(label,path,value,wide=false){return `<div class="image-row"><div>${textField(label,path,value)}${preview(value,'Aktuell verwendet',wide)}</div><label>Neues Bild<input class="cms-upload" data-upload-path="${esc(path)}" type="file" accept="image/jpeg,image/png,image/webp"></label></div>`;}

  function showTab(tab){
    currentTab=tab;
    adminMenu?.querySelectorAll('[data-admin-tab]').forEach(b=>b.classList.toggle('active',b.dataset.adminTab===tab));
    editor?.querySelectorAll('[data-admin-section]').forEach(s=>s.hidden=s.dataset.adminSection!==tab);
    if(tab==='statistik') loadStats(statsDays);
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
      </div></div>
    </section>

    <section class="cms-section" data-admin-section="home" hidden>
      <h2>Home</h2>
      ${textField('Überschrift Über uns','home.about_title',h.about_title)}
      ${(h.about_paragraphs||[]).map((p,i)=>textField('Über uns – Absatz '+(i+1),'home.about_paragraphs.'+i,p,true)).join('')}
      ${imageField('Bild Über uns','home.about_image',h.about_image,true)}
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
        ${textField('Alternativtext','home.popup_alt',h.popup_alt)}
        ${imageField('Popup-Bild','home.popup_image',h.popup_image,true)}
      </div>
    </section>

    <section class="cms-section" data-admin-section="angebote" hidden>
      <h2>Angebote</h2>
      ${textField('Seitenüberschrift','offers.page_title',data.offers?.page_title)}
      ${textField('Einleitung','offers.intro',data.offers?.intro,true)}
      ${textField('Hover-Text der Anmeldebuttons','offers.hover_text',data.offers?.hover_text)}
      ${offers.map((o,i)=>`<div class="cms-card"><div class="item-head"><strong>${esc(o.title||'Angebot')}</strong></div>
        <div class="grid2">${textField('Titel',`offers.items.${i}.title`,o.title)}${textField('Buttontext',`offers.items.${i}.button`,o.button)}</div>
        ${textField('Text auf Startseite',`offers.items.${i}.home_text`,o.home_text,true)}${imageField('Bild',`offers.items.${i}.image`,o.image,true)}
        ${linesField('Eckdaten – eine Zeile pro Punkt',`offers.items.${i}.details`,o.details)}
        ${textField('Hinweis, wenn nicht buchbar',`offers.items.${i}.unavailable_message`,o.unavailable_message)}
        ${o.id==='winter'?textField('Formular-URL',`offers.items.${i}.form_url`,o.form_url):''}
        ${o.id==='camps'?'<div class="grid2">'+textField('Camp-Button Feldkirchen',`offers.items.${i}.button_feldkirchen`,o.button_feldkirchen,true)+textField('Camp-Button Heimstetten',`offers.items.${i}.button_heimstetten`,o.button_heimstetten,true)+'</div>':''}
      </div>`).join('')}
      <div class="cms-card"><strong>Galerie</strong>
        ${(data.offers?.gallery||[]).map((img,i)=>imageField('Galeriebild '+(i+1),`offers.gallery.${i}`,img)).join('')}
      </div>
    </section>

    <section class="cms-section" data-admin-section="trainerteam" hidden>
      <h2>Trainerteam</h2>
      ${textField('Seitenüberschrift','trainers.page_title',data.trainers?.page_title)}
      ${textField('Einleitung','trainers.intro',data.trainers?.intro,true)}
      ${trainers.map((t,i)=>`<div class="cms-card"><div class="item-head"><strong>${esc(t.name||'Trainer')}</strong><button type="button" class="danger small remove-trainer" data-index="${i}">Entfernen</button></div>
      <div class="grid2">${textField('Name',`trainers.items.${i}.name`,t.name)}${textField('Rolle',`trainers.items.${i}.role`,t.role)}</div>${imageField('Bild',`trainers.items.${i}.image`,t.image)}</div>`).join('')}
      <button type="button" id="addTrainer" class="secondary">Trainer hinzufügen</button>
    </section>

    <section class="cms-section" data-admin-section="standorte" hidden>
      <h2>Standorte</h2>
      ${textField('Seitenüberschrift','locations.page_title',data.locations?.page_title)}
      ${textField('Karten-Hinweis','locations.map_consent_text',data.locations?.map_consent_text,true)}
      ${textField('Karten-Button','locations.map_consent_button',data.locations?.map_consent_button)}
      ${locationEditor('feldkirchen','TSV Feldkirchen',lf)}
      ${locationEditor('heimstetten','SV Heimstetten',lh,true)}
    </section>

    <section class="cms-section" data-admin-section="kontakt" hidden>
      <h2>Kontakt</h2>
      ${textField('Seitentitel','contact.page_title',contact.page_title)}${textField('Überschrift','contact.heading',contact.heading)}
      ${imageField('Kontaktbild','contact.image',contact.image,true)}
      <div class="cms-card"><strong>Formularbeschriftungen</strong><div class="grid2">
      ${textField('Name','contact.labels.name',labels.name)}${textField('Betreff','contact.labels.subject',labels.subject)}
      ${textField('Telefon','contact.labels.phone',labels.phone)}${textField('E-Mail','contact.labels.email',labels.email)}
      ${textField('Nachricht','contact.labels.message',labels.message)}${textField('Senden','contact.labels.submit',labels.submit)}
      </div>${textField('Erfolgsmeldung','contact.success_message',contact.success_message,true)}${textField('Fehlermeldung','contact.error_message',contact.error_message,true)}</div>
    </section>

    <section class="cms-section" data-admin-section="weitere" hidden>
      <h2>Weitere Seiten</h2>
      <div class="cms-card"><strong>Newsletter</strong>
        ${textField('Seitentitel','newsletter.page_title',data.newsletter?.page_title)}${textField('Willkommenstext','newsletter.welcome',data.newsletter?.welcome)}
        ${textField('Newsletter-Text','newsletter.text',data.newsletter?.text,true)}
      </div>
      <div class="cms-card"><strong>Erfolgsseite</strong>
        ${textField('Seitentitel','success.page_title',data.success?.page_title)}${textField('Nachricht','success.message',data.success?.message,true)}
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
    editor.querySelectorAll('.remove-trainer').forEach(btn=>btn.addEventListener('click',()=>{const i=Number(btn.dataset.index);if(confirm('Trainer wirklich entfernen?')){data.trainers.items.splice(i,1);render();}}));
    $('#addTrainer')?.addEventListener('click',()=>{data.trainers.items.push({name:'Neuer Trainer',role:'Trainer',image:'/assets/media/logo.png'});render();});
    editor.querySelectorAll('[data-stats-days]').forEach(btn=>btn.addEventListener('click',()=>{statsDays=Number(btn.dataset.statsDays);editor.querySelectorAll('[data-stats-days]').forEach(b=>b.classList.toggle('active',b===btn));loadStats(statsDays);}));
  }

  async function uploadImage(e){
    const input=e.currentTarget,file=input.files?.[0];if(!file)return;msg(saveMessage,'Bild wird hochgeladen …');
    const form=new FormData();form.append('file',file);
    try{const r=await request('/api/upload.php',{method:'POST',headers:{'X-CSRF-Token':csrf},body:form});setPath(data,input.dataset.uploadPath,r.path);render();msg(saveMessage,r.github?.ok?'Bild hochgeladen und in GitHub gespeichert.':'Bild hochgeladen; GitHub-Sicherung fehlgeschlagen.','success');}
    catch(err){msg(saveMessage,err.message,'error');}
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

  async function loadContent(){const r=await request('/api/content.php');data=r.data;csrf=r.csrf;loginPanel.hidden=true;editorPanel.hidden=false;logoutBtn.hidden=false;render();}
  async function checkAuth(){try{const r=await request('/api/auth.php');if(r.authenticated){csrf=r.csrf||'';await loadContent();}}catch(_){}}
  loginForm.addEventListener('submit',async e=>{e.preventDefault();msg(loginMessage,'Anmeldung …');try{const r=await request('/api/auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({password:$('#password').value})});csrf=r.csrf||'';$('#password').value='';await loadContent();msg(loginMessage,'');}catch(err){msg(loginMessage,err.message,'error');}});
  saveBtn.addEventListener('click',async()=>{saveBtn.disabled=true;msg(saveMessage,'Änderungen werden gespeichert …');try{const r=await request('/api/save.php',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':csrf},body:JSON.stringify({data})});msg(saveMessage,r.github?.ok?'Gespeichert und in GitHub versioniert.':'Website gespeichert; GitHub-Sicherung fehlgeschlagen: '+(r.github?.warning||''),r.github?.ok?'success':'warning');}catch(err){msg(saveMessage,err.message,'error');}finally{saveBtn.disabled=false;}});
  logoutBtn.addEventListener('click',async()=>{try{await request('/api/logout.php',{method:'POST',headers:{'X-CSRF-Token':csrf}});}catch(_){}location.reload();});
  adminMenu?.querySelectorAll('[data-admin-tab]').forEach(btn=>btn.addEventListener('click',()=>showTab(btn.dataset.adminTab||'allgemein')));
  checkAuth();
})();