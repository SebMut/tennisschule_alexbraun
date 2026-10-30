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

  const trustStrip=document.querySelector('.trust-strip');
  const trustViewport=trustStrip?.querySelector('.trust-strip-inner');
  const trustTrack=trustStrip?.querySelector('.trust-strip-track');
  const trustOriginalGroup=trustStrip?.querySelector('.trust-strip-group');
  let trustResizeTimer=null;

  const updateTrustStrip=()=>{
    if(!trustStrip||!trustViewport||!trustTrack||!trustOriginalGroup)return;

    trustTrack.querySelectorAll('.trust-strip-group[data-clone="true"]').forEach(el=>el.remove());
    trustStrip.classList.remove('is-marquee');
    trustTrack.style.removeProperty('--trust-duration');

    requestAnimationFrame(()=>{
      const originalWidth=trustOriginalGroup.scrollWidth;
      const availableWidth=trustViewport.clientWidth;
      const shouldMarquee=originalWidth>availableWidth+2;

      if(!shouldMarquee)return;

      const clone=trustOriginalGroup.cloneNode(true);
      clone.dataset.clone='true';
      clone.setAttribute('aria-hidden','true');
      trustTrack.appendChild(clone);
      trustStrip.classList.add('is-marquee');

      const pixelsPerSecond=55;
      const duration=Math.max(18,originalWidth/pixelsPerSecond);
      trustTrack.style.setProperty('--trust-duration',duration.toFixed(1)+'s');
    });
  };

  updateTrustStrip();
  window.addEventListener('resize',()=>{
    clearTimeout(trustResizeTimer);
    trustResizeTimer=setTimeout(updateTrustStrip,120);
  });

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

  const newsletterForm=document.getElementById('newsletterSignupForm');
  if(newsletterForm){
    newsletterForm.addEventListener('submit',async e=>{
      e.preventDefault();
      const message=document.getElementById('newsletterMessage');
      const submit=newsletterForm.querySelector('button[type="submit"]');
      if(message){message.textContent='Anmeldung wird verarbeitet …';message.className='newsletter-message';}
      if(submit)submit.disabled=true;
      try{
        const response=await fetch(newsletterForm.action,{method:'POST',body:new FormData(newsletterForm),credentials:'same-origin'});
        const result=await response.json().catch(()=>({ok:false,error:'Ungültige Serverantwort.'}));
        if(!response.ok||result.ok===false)throw new Error(result.error||'Die Anmeldung konnte nicht verarbeitet werden.');
        if(message){message.textContent=result.message||'Bitte bestätige deine Anmeldung per E-Mail.';message.className='newsletter-message success';}
        if(result.status==='pending')newsletterForm.reset();
        track('newsletter_signup_success','newsletter_signup_success','Newsletter-Anmeldung','');
      }catch(err){
        if(message){message.textContent=err.message;message.className='newsletter-message error';}
        track('newsletter_signup_error','newsletter_signup_error','Newsletter-Anmeldung fehlgeschlagen','');
      }finally{if(submit)submit.disabled=false;}
    });
  }

  document.querySelectorAll('form[data-track-form]').forEach(form=>{
    form.addEventListener('submit',()=>track('form_submit','form:'+form.dataset.trackForm,'Formular gesendet',form.action));
  });

  const error404=document.querySelector('[data-error404]');
  if(error404){
    const scenes=[...error404.querySelectorAll('[data-error404-scene]')];
    if(scenes.length){
      let previous=-1;
      try{previous=Number(sessionStorage.getItem('ts_404_scene')??-1);}catch(_){}
      const choices=scenes.map((_,i)=>i).filter(i=>i!==previous);
      let randomValue=Math.random();
      if(window.crypto?.getRandomValues){
        const buffer=new Uint32Array(1);
        window.crypto.getRandomValues(buffer);
        randomValue=buffer[0]/4294967296;
      }
      const index=choices[Math.floor(randomValue*choices.length)]??0;
      scenes[index].classList.add('is-active');
      try{sessionStorage.setItem('ts_404_scene',String(index));}catch(_){}
      track('404_scene','404_scene:'+String(scenes[index].dataset.error404Scene||index),'404 Animation','');
    }
  }

  const siteHeader=document.querySelector('.site-header');
  const updateSiteHeader=()=>{
    siteHeader?.classList.toggle('is-scrolled',window.scrollY>8);
  };
  updateSiteHeader();
  window.addEventListener('scroll',updateSiteHeader,{passive:true});

  const desktopDropdown=document.querySelector('.main-nav .nav-dropdown');
  const desktopDropdownTrigger=desktopDropdown?.querySelector(':scope > a');
  const desktopDropdownMenu=desktopDropdown?.querySelector('.dropdown-menu');

  const setDesktopDropdown=open=>{
    if(!desktopDropdown||!desktopDropdownTrigger||!desktopDropdownMenu)return;
    desktopDropdown.classList.toggle('is-open',open);
    desktopDropdownTrigger.setAttribute('aria-expanded',String(open));
  };

  if(desktopDropdownTrigger&&desktopDropdownMenu){
    desktopDropdownTrigger.setAttribute('aria-haspopup','true');
    desktopDropdownTrigger.setAttribute('aria-expanded','false');

    desktopDropdownTrigger.addEventListener('click',e=>{
      const isOpen=desktopDropdown.classList.contains('is-open');
      // First click opens the menu. If it is already open, the normal
      // "Standorte" link may navigate to the overview page.
      if(!isOpen){
        e.preventDefault();
        setDesktopDropdown(true);
        desktopDropdownMenu.querySelector('a')?.focus({preventScroll:true});
      }
    });

    desktopDropdown.addEventListener('mouseenter',()=>setDesktopDropdown(true));
    desktopDropdown.addEventListener('mouseleave',()=>setDesktopDropdown(false));

    desktopDropdownMenu.addEventListener('click',e=>{
      if(e.target.closest('a')) setDesktopDropdown(false);
    });

    document.addEventListener('pointerdown',e=>{
      if(!desktopDropdown.contains(e.target)) setDesktopDropdown(false);
    });

    document.addEventListener('keydown',e=>{
      if(e.key==='Escape'&&desktopDropdown.classList.contains('is-open')){
        setDesktopDropdown(false);
        desktopDropdownTrigger.focus();
      }
    });
  }

  const toggle=document.querySelector('.menu-toggle');
  const mobileMenu=document.getElementById('mobileMenu');
  const mobileOverlay=document.getElementById('mobileOverlay');
  const mobileClose=document.querySelector('.mobile-close');
  let menuReturnFocus=null;
  const setMenu=open=>{
    if(!mobileMenu||!mobileOverlay||!toggle) return;
    mobileMenu.classList.toggle('open',open); mobileOverlay.classList.toggle('open',open);
    mobileMenu.setAttribute('aria-hidden',String(!open)); toggle.setAttribute('aria-expanded',String(open));
    body.classList.toggle('menu-open',open);
    siteHeader?.classList.toggle('menu-active',open);
    if(open){menuReturnFocus=document.activeElement;setTimeout(()=>mobileClose?.focus(),0);}
    else if(menuReturnFocus instanceof HTMLElement){menuReturnFocus.focus();menuReturnFocus=null;}
  };
  toggle?.addEventListener('click',()=>setMenu(true));
  mobileClose?.addEventListener('click',()=>setMenu(false));
  mobileOverlay?.addEventListener('click',()=>setMenu(false));
  mobileMenu?.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>setMenu(false)));

  document.querySelectorAll('[data-trainer-toggle]').forEach(btn=>{
    btn.addEventListener('click',()=>{
      const id='trainer-details-'+btn.dataset.trainerToggle;
      const panel=document.getElementById(id);
      if(!panel)return;
      const open=panel.hidden;
      panel.hidden=!open;
      btn.setAttribute('aria-expanded',String(open));
      btn.textContent=open?'Weniger anzeigen':'Mehr erfahren';
      if(open) track('trainer_detail','trainer_detail:'+btn.dataset.trainerToggle,btn.closest('.trainer-card')?.querySelector('h3')?.textContent||'Trainerdetail','');
    });
  });

  document.querySelectorAll('.faq-item').forEach((item,i)=>{
    item.addEventListener('toggle',()=>{
      if(item.open) track('faq_open','faq:'+i,item.querySelector('summary')?.textContent?.trim()||'FAQ','');
    });
  });

  const trainingModal=document.getElementById('trainingModal');
  const trainingModalContent=document.getElementById('trainingModalContent');
  const trainingClose=trainingModal?.querySelector('.trainings-close');
  let trainingReturnFocus=null;
  const closeTrainingModal=()=>{
    if(!trainingModal||!trainingModalContent)return;
    trainingModal.classList.remove('active'); trainingModal.setAttribute('aria-hidden','true');
    setTimeout(()=>{trainingModalContent.innerHTML='';trainingModalContent.classList.remove('visible');trainingModal.querySelector('.trainings-modal-content')?.classList.remove('is-info','is-form');body.classList.remove('popup-open');if(trainingReturnFocus instanceof HTMLElement)trainingReturnFocus.focus();trainingReturnFocus=null;},300);
  };
  const trainingDisplayName=id=>{
    const map={
      sommer:'Sommertraining',
      winter:'Wintertraining',
      'camp-feldkirchen':'Tenniscamp Feldkirchen',
      'camp-heimstetten':'Tenniscamp Heimstetten'
    };
    return map[id]||'Tennistraining';
  };
  const buildTrainingInfoCard=(btn,errorMsg)=>{
    const id=String(btn.dataset.trainingId||'');
    const name=trainingDisplayName(id);
    const dateMatch=String(errorMsg).match(/(\d{2}\.\d{2}\.\d{4})/);
    const isUpcoming=Boolean(dateMatch);
    const card=document.createElement('div');
    card.className='training-info-card';

    const icon=document.createElement('div');
    icon.className='training-info-icon';
    icon.setAttribute('aria-hidden','true');
    icon.innerHTML='<svg viewBox="0 0 24 24"><path d="M7 2v3M17 2v3M4 8h16M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm3 8h3v3H8v-3Z"/></svg>';

    const eyebrow=document.createElement('span');
    eyebrow.className='training-info-status '+(isUpcoming?'upcoming':'closed');
    eyebrow.textContent=isUpcoming?'Demnächst buchbar':'Anmeldung geschlossen';

    const title=document.createElement('h3');
    title.id='trainingModalTitle';
    title.textContent=name;

    const message=document.createElement('p');
    message.className='training-info-copy';
    message.textContent=isUpcoming
      ? 'Die Anmeldung ist noch nicht geöffnet.'
      : 'Für dieses Training ist die Anmeldung derzeit geschlossen.';

    const date=document.createElement('div');
    date.className='training-info-date';
    if(isUpcoming){
      const label=document.createElement('span');
      label.textContent='Voraussichtlich buchbar ab';
      const strong=document.createElement('strong');
      strong.textContent=dateMatch[1];
      date.append(label,strong);
    }else{
      const strong=document.createElement('strong');
      strong.textContent=errorMsg;
      date.append(strong);
    }

    const actions=document.createElement('div');
    actions.className='training-info-actions';
    const close=document.createElement('button');
    close.type='button';
    close.className='training-info-close';
    close.textContent='Verstanden';
    close.addEventListener('click',closeTrainingModal);
    actions.append(close);

    card.append(icon,eyebrow,title,message,date,actions);
    return card;
  };
  const openTrainingModal=btn=>{
    if(!btn||!trainingModal||!trainingModalContent)return;
    const formUrl=btn.dataset.trainingUrl||'', errorMsg=btn.dataset.trainingError||'Die Anmeldung ist aktuell nicht verfügbar.';
    const registrationOpen=btn.dataset.registrationOpen==='1';
    trainingReturnFocus=btn;
    const modalCard=trainingModal.querySelector('.trainings-modal-content');
    const isForm=Boolean(formUrl&&registrationOpen);
    modalCard?.classList.toggle('is-info',!isForm);
    modalCard?.classList.toggle('is-form',isForm);
    if(modalCard){
      if(isForm){
        modalCard.removeAttribute('aria-labelledby');
        modalCard.setAttribute('aria-label',trainingDisplayName(String(btn.dataset.trainingId||''))+' Anmeldung');
      }else{
        modalCard.removeAttribute('aria-label');
        modalCard.setAttribute('aria-labelledby','trainingModalTitle');
      }
    }
    trainingModal.classList.add('active'); trainingModal.setAttribute('aria-hidden','false'); body.classList.add('popup-open');
    if(formUrl&&registrationOpen){
      const iframe=document.createElement('iframe');iframe.src=formUrl;iframe.frameBorder='0';iframe.allowFullscreen=true;iframe.title=trainingDisplayName(String(btn.dataset.trainingId||''))+' Anmeldung';trainingModalContent.replaceChildren(iframe);
    }else{
      trainingModalContent.replaceChildren(buildTrainingInfoCard(btn,errorMsg));
    }
    track('training_modal_open','training_modal:'+String(btn.dataset.trainingId||'unknown'),(btn.textContent||'').trim(),formUrl);
    setTimeout(()=>{trainingModalContent.classList.add('visible');trainingClose?.focus();},50);
  };
  document.querySelectorAll('.trainings-btn').forEach(btn=>btn.addEventListener('click',()=>openTrainingModal(btn)));
  trainingClose?.addEventListener('click',closeTrainingModal);
  trainingModal?.addEventListener('click',e=>{if(e.target===trainingModal)closeTrainingModal();});

  const directTraining=new URLSearchParams(location.search).get('training');
  if(path.startsWith('/angebote')&&directTraining){
    if(directTraining==='camps'){
      const campCard=document.getElementById('tenniscamps');
      const campButtons=[
        document.querySelector('[data-training-id="camp-feldkirchen"]'),
        document.querySelector('[data-training-id="camp-heimstetten"]')
      ].filter(Boolean);
      setTimeout(()=>{
        campCard?.scrollIntoView({behavior:'smooth',block:'center'});
        setTimeout(()=>{
          campButtons.forEach(btn=>btn.classList.add('attention-pulse'));
          if(campButtons[0] instanceof HTMLElement) campButtons[0].focus({preventScroll:true});
          track('training_direct','training_direct:camps','Tenniscamps Standortauswahl','');
          setTimeout(()=>campButtons.forEach(btn=>btn.classList.remove('attention-pulse')),3400);
        },350);
      },120);
    }else if(directTraining==='sommer'||directTraining==='winter'){
      const target=document.querySelector('[data-training-id="'+directTraining+'"]');
      if(target){
        setTimeout(()=>{
          target.closest('.offer-detail-card')?.scrollIntoView({behavior:'auto',block:'center'});
          openTrainingModal(target);
          track('training_direct','training_direct:'+directTraining,(target.textContent||'').trim(),target.dataset.trainingUrl||'');
        },120);
      }
    }
  }

  const initMapbox=()=>{
    const mapboxContainer=document.getElementById('mapbox-container');
    const mapDataEl=document.getElementById('mapbox-location-data');
    if(!mapboxContainer||!mapDataEl||typeof window.mapboxgl==='undefined'||mapboxContainer.dataset.initialized==='1') return;

    const token=mapboxContainer.dataset.mapboxToken||'';
    let locations=[]; try{locations=JSON.parse(mapDataEl.textContent||'[]');}catch(_){}
    if(!token||!locations.length){
      mapboxContainer.classList.add('mapbox-error');
      mapboxContainer.textContent='Karte konnte nicht geladen werden.';
      return;
    }

    mapboxContainer.dataset.initialized='1';
    window.mapboxgl.accessToken=token;
    const single=mapboxContainer.dataset.mapboxSingle==='1';
    const first=locations[0];

    const map=new window.mapboxgl.Map({
      container:mapboxContainer,
      style:'mapbox://styles/mapbox/streets-v12',
      center:[Number(first.lng),Number(first.lat)],
      zoom:single?14.5:13,
      maxZoom:17,
      scrollZoom:false,
      dragPan:true,
      doubleClickZoom:true,
      touchZoomRotate:true
    });
    map.addControl(new window.mapboxgl.NavigationControl({showCompass:false}),'top-right');

    const bounds=new window.mapboxgl.LngLatBounds();
    locations.forEach(loc=>{
      const marker=document.createElement('button');
      marker.type='button';
      marker.className='custom-marker';
      marker.setAttribute('aria-label',loc.name+' anzeigen');

      const img=document.createElement('img');
      img.src=loc.logo;
      img.alt='';
      marker.appendChild(img);

      const label=document.createElement('div');
      label.className='marker-label';
      label.textContent=loc.name;
      marker.appendChild(label);

      const routeUrl=loc.route_url||loc.detail_url||'#';
      const popupHtml='<div class="map-popup"><strong>'+String(loc.name||'')+'</strong>'
        +(loc.fact?'<p>'+String(loc.fact)+'</p>':'')
        +'<div class="map-popup-actions"><a data-map-detail href="'+loc.detail_url+'">Standort ansehen</a>'
        +(!single?'<a data-map-route href="'+routeUrl+'">'+(mapboxContainer.dataset.routeLabel||'Route berechnen')+'</a>':'')
        +'</div></div>';

      const popup=new window.mapboxgl.Popup({offset:28,closeButton:true,maxWidth:'280px'}).setHTML(popupHtml);
      new window.mapboxgl.Marker(marker,{anchor:'bottom'}).setLngLat([Number(loc.lng),Number(loc.lat)]).setPopup(popup).addTo(map);

      marker.addEventListener('click',()=>track('map_location','map_location:'+loc.key,loc.name,loc.detail_url));
      popup.on('open',()=>{
        popup.getElement()?.querySelector('[data-map-route]')?.addEventListener('click',()=>track('map_route','map_route:'+loc.key,loc.name,routeUrl),{once:true});
        popup.getElement()?.querySelector('[data-map-detail]')?.addEventListener('click',()=>track('map_detail','map_detail:'+loc.key,loc.name,loc.detail_url),{once:true});
      });

      bounds.extend([Number(loc.lng),Number(loc.lat)]);
    });

    if(!single&&locations.length>1) map.fitBounds(bounds,{padding:80,maxZoom:15});

    if(single){
      const routeButton=document.querySelector('[data-mapbox-route]');
      const routeStatus=document.querySelector('[data-mapbox-route-status]');
      const routeLoading=mapboxContainer.dataset.routeLoading||'Standort wird ermittelt …';
      const routeError=mapboxContainer.dataset.routeError||'Dein Standort konnte nicht ermittelt werden.';
      const target=[Number(first.lng),Number(first.lat)];

      const setRouteStatus=(message,isError=false)=>{
        if(!routeStatus)return;
        routeStatus.textContent=message||'';
        routeStatus.classList.toggle('error',isError);
      };

      const renderRoute=async position=>{
        const origin=[position.coords.longitude,position.coords.latitude];
        const url='https://api.mapbox.com/directions/v5/mapbox/driving/'
          +origin.join(',')+';'+target.join(',')
          +'?alternatives=false&geometries=geojson&overview=full&steps=false&access_token='+encodeURIComponent(token);

        const response=await fetch(url);
        if(!response.ok) throw new Error('Route konnte nicht geladen werden.');
        const data=await response.json();
        const route=data.routes?.[0];
        if(!route?.geometry) throw new Error('Keine Route gefunden.');

        const draw=()=>{
          const geojson={type:'Feature',properties:{},geometry:route.geometry};
          if(map.getSource('route')) map.getSource('route').setData(geojson);
          else{
            map.addSource('route',{type:'geojson',data:geojson});
            map.addLayer({
              id:'route',
              type:'line',
              source:'route',
              layout:{'line-join':'round','line-cap':'round'},
              paint:{'line-color':'#f17c20','line-width':6,'line-opacity':.88}
            });
          }

          const originData={type:'Feature',properties:{},geometry:{type:'Point',coordinates:origin}};
          if(map.getSource('route-origin')) map.getSource('route-origin').setData(originData);
          else{
            map.addSource('route-origin',{type:'geojson',data:originData});
            map.addLayer({
              id:'route-origin',
              type:'circle',
              source:'route-origin',
              paint:{'circle-radius':7,'circle-color':'#2f7cff','circle-stroke-color':'#fff','circle-stroke-width':3}
            });
          }

          const routeBounds=new window.mapboxgl.LngLatBounds();
          route.geometry.coordinates.forEach(coord=>routeBounds.extend(coord));
          map.fitBounds(routeBounds,{padding:55,maxZoom:15});

          const km=(route.distance/1000).toFixed(route.distance>=10000?0:1);
          const minutes=Math.max(1,Math.round(route.duration/60));
          setRouteStatus(km+' km · ca. '+minutes+' Min.');
          track('map_route','map_route:'+first.key,first.name,'Mapbox Directions');
        };

        if(map.loaded()) draw(); else map.once('load',draw);
      };

      const requestRoute=()=>{
        if(!navigator.geolocation){
          setRouteStatus(routeError,true);
          return;
        }
        setRouteStatus(routeLoading,false);
        if(routeButton) routeButton.disabled=true;
        navigator.geolocation.getCurrentPosition(
          async position=>{
            try{await renderRoute(position);}
            catch(err){setRouteStatus(err.message||routeError,true);}
            finally{if(routeButton)routeButton.disabled=false;}
          },
          ()=>{
            setRouteStatus(routeError,true);
            if(routeButton)routeButton.disabled=false;
          },
          {enableHighAccuracy:false,timeout:12000,maximumAge:300000}
        );
      };

      routeButton?.addEventListener('click',requestRoute);
      if(new URLSearchParams(location.search).get('route')==='1'){
        setTimeout(requestRoute,350);
      }
    }
  };

  const loadMapbox=()=>{
    if(typeof window.mapboxgl!=='undefined'){initMapbox();return;}
    if(document.querySelector('script[data-mapbox-loader]'))return;
    const css=document.createElement('link');
    css.rel='stylesheet';
    css.href='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css';
    document.head.appendChild(css);

    const script=document.createElement('script');
    script.src='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js';
    script.dataset.mapboxLoader='1';
    script.onload=initMapbox;
    document.head.appendChild(script);
  };

  const mapboxContainer=document.getElementById('mapbox-container');
  if(mapboxContainer){
    if('IntersectionObserver'in window){
      const observer=new IntersectionObserver(entries=>{
        if(entries.some(e=>e.isIntersecting)){
          observer.disconnect();
          loadMapbox();
        }
      },{rootMargin:'300px'});
      observer.observe(mapboxContainer);
    }else loadMapbox();
  }

  const winterPopup=document.getElementById('winterPopup');
  if(winterPopup){
    const close=winterPopup.querySelector('.wp-popup-close');
    let popupReturnFocus=null;
    const frequency=24*60*60*1000; // 24 Stunden ab Anzeige
    const delay=Math.max(0,Number(winterPopup.dataset.delaySeconds||1))*1000;
    const closeDelay=Math.max(0,Number(winterPopup.dataset.closeDelaySeconds||2))*1000;
    const storageKey='ts-popup-last-shown-v1';
    const shownWithin24h=()=>{try{const t=Number(localStorage.getItem(storageKey)||0);return t>0&&Date.now()-t<frequency;}catch(_){return false;}};
    const closePopup=()=>{
      winterPopup.classList.remove('open');winterPopup.setAttribute('aria-hidden','true');body.classList.remove('popup-open');
      track('popup_close','popup_close','Popup geschlossen','');
      if(popupReturnFocus instanceof HTMLElement)popupReturnFocus.focus();
      popupReturnFocus=null;
    };
    close?.addEventListener('click',closePopup);
    winterPopup.addEventListener('click',e=>{if(e.target===winterPopup)closePopup();});
    if(!shownWithin24h()){
      if(close && closeDelay>0) close.style.visibility='hidden';
      setTimeout(()=>{
        if(shownWithin24h())return;
        popupReturnFocus=document.activeElement;
        winterPopup.classList.add('open');winterPopup.setAttribute('aria-hidden','false');body.classList.add('popup-open');
        if(close && closeDelay===0)close.focus();
        else winterPopup.querySelector('.wp-popup-image-link, .wp-popup-copy a')?.focus();
        track('popup_view','popup_view','Popup angezeigt','');
        try{localStorage.setItem(storageKey,String(Date.now()));}catch(_){}
        if(close && closeDelay>0)setTimeout(()=>{close.style.visibility='visible';},closeDelay);
      },delay);
    }
  }

  document.addEventListener('keydown',e=>{
    if(e.key==='Tab'){
      const scope=trainingModal?.classList.contains('active')?trainingModal:winterPopup?.classList.contains('open')?winterPopup:mobileMenu?.classList.contains('open')?mobileMenu:null;
      if(scope){
        const focusable=[...scope.querySelectorAll('a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])')].filter(el=>el.offsetParent!==null);
        if(focusable.length){
          const first=focusable[0],last=focusable[focusable.length-1];
          if(e.shiftKey&&document.activeElement===first){e.preventDefault();last.focus();}
          else if(!e.shiftKey&&document.activeElement===last){e.preventDefault();first.focus();}
        }
      }
      return;
    }
    if(e.key!=='Escape')return;
    setMenu(false); if(trainingModal?.classList.contains('active'))closeTrainingModal();
    if(winterPopup?.classList.contains('open')) winterPopup.querySelector('.wp-popup-close')?.click();
    body.classList.remove('popup-open');
  });
})();