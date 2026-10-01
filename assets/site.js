(() => {
  const body=document.body;
  const path=location.pathname || '/';
  const device=innerWidth<768?'mobile':innerWidth<1024?'tablet':'desktop';

  let session='';
  try{
    session=sessionStorage.getItem('ts_analytics_session')||'';
    if(!session){
      session=(crypto?.randomUUID?.() || (Date.now().toString(36)+Math.random().toString(36).slice(2)));
      sessionStorage.setItem('ts_analytics_session',session);
    }
  }catch(_){ session=Date.now().toString(36); }

  let referrer='direct';
  try{
    if(document.referrer){
      const u=new URL(document.referrer);
      referrer=u.hostname===location.hostname?'internal':u.hostname.replace(/^www\./,'');
    }
  }catch(_){}

  const track=(event,key='',label='',href='')=>{
    const payload={event,key,label,href,path,device,referrer,session};
    const bodyData=JSON.stringify(payload);
    if(navigator.sendBeacon){
      navigator.sendBeacon('/api/track.php',new Blob([bodyData],{type:'application/json'}));
    }else{
      fetch('/api/track.php',{method:'POST',headers:{'Content-Type':'application/json'},body:bodyData,keepalive:true}).catch(()=>{});
    }
  };

  track('pageview','pageview',document.title,location.href);
  if(path==='/nachricht-erfolgreich-zugestellt/' || path==='/nachricht-erfolgreich-zugestellt') track('form_success','contact_success','Kontakt erfolgreich','');

  document.addEventListener('click',e=>{
    const el=e.target.closest('a,button');
    if(!el || el.closest('#adminMenu')) return;
    const label=(el.textContent||el.getAttribute('aria-label')||'').trim().replace(/\s+/g,' ').slice(0,160);
    const href=el.tagName==='A'?(el.getAttribute('href')||''):'';
    let key=el.dataset.track||'';
    if(!key){
      if(href.startsWith('tel:')) key='phone_click';
      else if(href.startsWith('mailto:')) key='email_click';
      else if(href){
        try{
          const u=new URL(href,location.href);
          key=u.hostname!==location.hostname?'external:'+u.hostname.replace(/^www\./,''):'link:'+u.pathname;
        }catch(_){ key='link'; }
      }else key='button:'+label.toLowerCase().replace(/[^a-z0-9äöüß]+/gi,'-').slice(0,60);
    }
    track('click',key,label,href);
  },true);

  document.querySelectorAll('form[data-track-form]').forEach(form=>{
    form.addEventListener('submit',()=>track('form_submit','form:'+form.dataset.trackForm,'Formular gesendet',form.action));
  });

  const toggle=document.querySelector('.menu-toggle');
  const mobileMenu=document.getElementById('mobileMenu');
  const mobileOverlay=document.getElementById('mobileOverlay');
  const mobileClose=document.querySelector('.mobile-close');
  const setMenu=open=>{
    if(!mobileMenu||!mobileOverlay||!toggle) return;
    mobileMenu.classList.toggle('open',open); mobileOverlay.classList.toggle('open',open);
    mobileMenu.setAttribute('aria-hidden',String(!open)); toggle.setAttribute('aria-expanded',String(open));
    body.classList.toggle('menu-open',open);
  };
  toggle?.addEventListener('click',()=>setMenu(true));
  mobileClose?.addEventListener('click',()=>setMenu(false));
  mobileOverlay?.addEventListener('click',()=>setMenu(false));
  mobileMenu?.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>setMenu(false)));

  const trainingModal=document.getElementById('trainingModal');
  const trainingModalContent=document.getElementById('trainingModalContent');
  const trainingClose=trainingModal?.querySelector('.trainings-close');
  const closeTrainingModal=()=>{
    if(!trainingModal||!trainingModalContent)return;
    trainingModal.classList.remove('active'); trainingModal.setAttribute('aria-hidden','true');
    setTimeout(()=>{trainingModalContent.innerHTML='';trainingModalContent.classList.remove('visible');body.classList.remove('popup-open');},300);
  };
  document.querySelectorAll('.trainings-btn').forEach(btn=>btn.addEventListener('click',()=>{
    if(!trainingModal||!trainingModalContent)return;
    const formUrl=btn.dataset.trainingUrl||'', errorMsg=btn.dataset.trainingError||'Die Anmeldung ist aktuell nicht verfügbar.';
    trainingModal.classList.add('active'); trainingModal.setAttribute('aria-hidden','false'); body.classList.add('popup-open');
    if(formUrl){
      const iframe=document.createElement('iframe');iframe.src=formUrl;iframe.frameBorder='0';iframe.allowFullscreen=true;trainingModalContent.replaceChildren(iframe);
    }else{
      const error=document.createElement('div');error.className='trainings-error';error.textContent=errorMsg;trainingModalContent.replaceChildren(error);
    }
    setTimeout(()=>trainingModalContent.classList.add('visible'),50);
  }));
  trainingClose?.addEventListener('click',closeTrainingModal);
  trainingModal?.addEventListener('click',e=>{if(e.target===trainingModal)closeTrainingModal();});

  const loadMaps=()=>{
    document.querySelectorAll('[data-map-src]').forEach(frame=>{
      if(!frame.src) frame.src=frame.dataset.mapSrc;
      frame.hidden=false; frame.previousElementSibling?.remove();
    });
    try{localStorage.setItem('ts_maps_consent','yes');}catch(_){}
  };
  try{if(localStorage.getItem('ts_maps_consent')==='yes')loadMaps();}catch(_){}
  document.querySelectorAll('[data-load-map]').forEach(btn=>btn.addEventListener('click',loadMaps));

  const mapboxContainer=document.getElementById('mapbox-container');
  const mapDataEl=document.getElementById('mapbox-location-data');
  if(mapboxContainer&&mapDataEl&&typeof window.mapboxgl!=='undefined'){
    const token=mapboxContainer.dataset.mapboxToken||'';
    let locations=[]; try{locations=JSON.parse(mapDataEl.textContent||'[]');}catch(_){}
    if(token&&locations.length){
      window.mapboxgl.accessToken=token;
      const map=new window.mapboxgl.Map({container:mapboxContainer,style:'mapbox://styles/mapbox/streets-v12',center:[11.32,48.15],zoom:13,maxZoom:15,scrollZoom:false,dragPan:false,doubleClickZoom:false,touchZoomRotate:false});
      const bounds=new window.mapboxgl.LngLatBounds();
      locations.forEach(loc=>{
        const marker=document.createElement('div');marker.className='custom-marker';
        const img=document.createElement('img');img.src=loc.logo;img.alt=loc.name;marker.appendChild(img);
        const label=document.createElement('div');label.className='marker-label';label.textContent=mapboxContainer.dataset.routeLabel||'Route berechnen';marker.appendChild(label);
        marker.addEventListener('click',()=>{track('map_route','map_route:'+loc.key,loc.name,loc.link);location.href=loc.link;});
        new window.mapboxgl.Marker(marker,{anchor:'bottom'}).setLngLat([loc.lng,loc.lat]).addTo(map);bounds.extend([loc.lng,loc.lat]);
      });
      map.fitBounds(bounds,{padding:80,maxZoom:15});
    }
  }

  const winterPopup=document.getElementById('winterPopup');
  if(winterPopup){
    const close=winterPopup.querySelector('.wp-popup-close');
    const frequency=Math.max(0,Number(winterPopup.dataset.frequencyDays||7))*86400000;
    const delay=Math.max(0,Number(winterPopup.dataset.delaySeconds||1))*1000;
    const closeDelay=Math.max(0,Number(winterPopup.dataset.closeDelaySeconds||2))*1000;
    const storageKey='ts-popup-closed-'+(winterPopup.dataset.popupKey||'default');
    let mayShow=true;
    try{const closedAt=Number(localStorage.getItem(storageKey)||0);if(frequency>0&&closedAt&&Date.now()-closedAt<frequency)mayShow=false;}catch(_){}
    const closePopup=()=>{
      winterPopup.classList.remove('open');winterPopup.setAttribute('aria-hidden','true');body.classList.remove('popup-open');
      track('popup_close','popup_close','Popup geschlossen','');
      try{localStorage.setItem(storageKey,String(Date.now()));}catch(_){}
    };
    close?.addEventListener('click',closePopup);
    winterPopup.addEventListener('click',e=>{if(e.target===winterPopup)closePopup();});
    if(mayShow){
      if(close) close.style.visibility='hidden';
      setTimeout(()=>{
        winterPopup.classList.add('open');winterPopup.setAttribute('aria-hidden','false');body.classList.add('popup-open');
        track('popup_view','popup_view','Popup angezeigt','');
        try{if(frequency>0)localStorage.setItem(storageKey,String(Date.now()));}catch(_){}
        setTimeout(()=>{if(close)close.style.visibility='visible';},closeDelay);
      },delay);
    }
  }

  document.addEventListener('keydown',e=>{
    if(e.key!=='Escape')return;
    setMenu(false); if(trainingModal?.classList.contains('active'))closeTrainingModal();
    if(winterPopup?.classList.contains('open')) winterPopup.querySelector('.wp-popup-close')?.click();
    body.classList.remove('popup-open');
  });
})();