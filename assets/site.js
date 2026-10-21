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

  const siteHeader=document.querySelector('.site-header');
  const updateSiteHeader=()=>{
    siteHeader?.classList.toggle('is-scrolled',window.scrollY>8);
  };
  updateSiteHeader();
  window.addEventListener('scroll',updateSiteHeader,{passive:true});

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
    setTimeout(()=>{trainingModalContent.innerHTML='';trainingModalContent.classList.remove('visible');body.classList.remove('popup-open');if(trainingReturnFocus instanceof HTMLElement)trainingReturnFocus.focus();trainingReturnFocus=null;},300);
  };
  const openTrainingModal=btn=>{
    if(!btn||!trainingModal||!trainingModalContent)return;
    const formUrl=btn.dataset.trainingUrl||'', errorMsg=btn.dataset.trainingError||'Die Anmeldung ist aktuell nicht verfügbar.';
    const registrationOpen=btn.dataset.registrationOpen==='1';
    trainingReturnFocus=btn;
    trainingModal.classList.add('active'); trainingModal.setAttribute('aria-hidden','false'); body.classList.add('popup-open');
    if(formUrl&&registrationOpen){
      const iframe=document.createElement('iframe');iframe.src=formUrl;iframe.frameBorder='0';iframe.allowFullscreen=true;trainingModalContent.replaceChildren(iframe);
    }else{
      const error=document.createElement('div');error.className='trainings-error';error.textContent=errorMsg;trainingModalContent.replaceChildren(error);
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
      if(popupReturnFocus instanceof HTMLElement)popupReturnFocus.focus();
      popupReturnFocus=null;
    };
    close?.addEventListener('click',closePopup);
    winterPopup.addEventListener('click',e=>{if(e.target===winterPopup)closePopup();});
    if(mayShow){
      if(close) close.style.visibility='hidden';
      setTimeout(()=>{
        popupReturnFocus=document.activeElement;
        winterPopup.classList.add('open');winterPopup.setAttribute('aria-hidden','false');body.classList.add('popup-open');
        close?.focus();
        track('popup_view','popup_view','Popup angezeigt','');
        try{if(frequency>0)localStorage.setItem(storageKey,String(Date.now()));}catch(_){}
        setTimeout(()=>{if(close)close.style.visibility='visible';},closeDelay);
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