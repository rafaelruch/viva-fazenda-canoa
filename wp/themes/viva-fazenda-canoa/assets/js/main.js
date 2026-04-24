/* ============================================================
   VIVA FAZENDA CANOA — LP Emocional
   Video autoplay, countUp, modais, widget, scrollspy, reveal
   ============================================================ */
(() => {
  'use strict';

  // Analytics: dispara Meta Pixel Lead + Google Ads conversion com event_id (deduplicação CAPI)
  const fireLeadEvents = (data) => {
    return new Promise((resolve) => {
      const cfg = window.FC_ANALYTICS || {};
      const eventId = 'lead_' + Date.now() + '_' + Math.random().toString(36).slice(2, 9);
      if (cfg.metaPixelId && typeof window.fbq === 'function') {
        try { window.fbq('track', 'Lead', { content_name: 'Formulario LP', content_category: data && data.interesse ? data.interesse : 'Informações gerais' }, { eventID: eventId }); } catch(e){}
      }
      if (cfg.googleAdsConv && typeof window.gtag === 'function') {
        try { window.gtag('event', 'conversion', { send_to: cfg.googleAdsConv, transaction_id: eventId }); } catch(e){}
      }
      setTimeout(() => resolve(eventId), 300);
    });
  };
  window.fireLeadEvents = fireLeadEvents;

  const hdr = document.getElementById('hdr');
  const burger = document.querySelector('.hdr__burger');
  const mobileMenu = document.getElementById('mobile-menu');
  const form = document.getElementById('lead-form');
  const scrollProgress = document.getElementById('scroll-progress');
  const prm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // --- 1. Header scroll state + scroll progress ---
  const onScroll = () => {
    hdr.classList.toggle('hdr--scrolled', window.scrollY > 60);
    if (scrollProgress) {
      const h = document.documentElement.scrollHeight - window.innerHeight;
      scrollProgress.style.width = (h > 0 ? (window.scrollY / h) * 100 : 0) + '%';
    }
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // --- 2. Mobile menu ---
  const toggleMenu = (open) => {
    const next = typeof open === 'boolean' ? open : burger.getAttribute('aria-expanded') !== 'true';
    burger.setAttribute('aria-expanded', String(next));
    mobileMenu.setAttribute('aria-hidden', String(!next));
    document.body.style.overflow = next ? 'hidden' : '';
  };
  burger?.addEventListener('click', () => toggleMenu());
  mobileMenu?.querySelectorAll('a').forEach(a => a.addEventListener('click', () => toggleMenu(false)));
  document.addEventListener('keydown', e => { if (e.key === 'Escape') toggleMenu(false); });

  // --- 3. Smooth scroll ---
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const id = a.getAttribute('href').slice(1);
      if (!id) return;
      const target = document.getElementById(id);
      if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    });
  });

  // --- 4. Video autoplay handler (iOS Safari fallback) ---
  const heroVideo = document.querySelector('.hero__video');
  if (heroVideo) {
    heroVideo.play().catch(() => {
      // iOS pode bloquear autoplay; mostrar poster como fallback
      heroVideo.style.display = 'none';
    });
  }

  // --- 5. Lead form (seção Consultor) ---
  const submitLeadLocal = (data) => {
    try {
      const leads = JSON.parse(localStorage.getItem('fcanoa_viva_leads') || '[]');
      leads.push({ ...data, timestamp: new Date().toISOString() });
      localStorage.setItem('fcanoa_viva_leads', JSON.stringify(leads));
    } catch (_) {}
    console.log('[LEAD CAPTURED]', data);
    if (window.FC_AJAX) {
      fetch(window.FC_AJAX.url, {
        method: 'POST',
        body: new URLSearchParams({ action: 'lfc_submit_lead', _nonce: window.FC_AJAX.nonce, ...data }),
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      }).catch(() => {});
    }
  };

  form?.addEventListener('submit', e => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(form));
    if (!data.nome || !data.telefone || !data.interesse) { alert('Preencha nome, WhatsApp e interesse.'); return; }
    form.querySelector('.lead-form__success').hidden = false;
    form.querySelector('button[type="submit"]').disabled = true;
    // Webhook ImobMeet é disparado server-side pelo plugin lfc-opcoes-plugin via lfc_submit_lead.
    fireLeadEvents(data).then((eventId) => {
      submitLeadLocal({ ...data, source: 'form-principal', event_id: eventId });
    });
  });

  // --- Carrossel Estilo de Vida (scroll-snap nativo + dots) ---
  const lfTrack = document.getElementById('lf-track');
  const lfDots = document.getElementById('lf-dots');
  if (lfTrack && lfDots) {
    const slides = Array.from(lfTrack.querySelectorAll('.lf-slide'));
    // Gera os dots dinamicamente
    slides.forEach((_, i) => {
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.className = 'lf-dot' + (i === 0 ? ' is-active' : '');
      dot.setAttribute('role', 'tab');
      dot.setAttribute('aria-label', `Foto ${i + 1} de ${slides.length}`);
      dot.addEventListener('click', () => {
        slides[i].scrollIntoView({ behavior: 'smooth', inline: 'start', block: 'nearest' });
      });
      lfDots.appendChild(dot);
    });
    // Atualiza dot ativo conforme scroll (IntersectionObserver com root no próprio track)
    if ('IntersectionObserver' in window) {
      const dots = Array.from(lfDots.querySelectorAll('.lf-dot'));
      const slideIO = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting && entry.intersectionRatio > 0.6) {
            const idx = slides.indexOf(entry.target);
            dots.forEach((d, i) => d.classList.toggle('is-active', i === idx));
          }
        });
      }, { root: lfTrack, threshold: [0.6, 0.9] });
      slides.forEach(s => slideIO.observe(s));
    }
  }

  // --- 6. CountUp animation ---
  if (!prm && 'IntersectionObserver' in window) {
    const counters = document.querySelectorAll('.counter__number[data-count]');
    const countIO = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        const target = parseInt(el.dataset.count, 10);
        if (!target || isNaN(target)) return;
        countIO.unobserve(el);
        let current = 0;
        const duration = 2000;
        const step = target / (duration / 16);
        const tick = () => {
          current += step;
          if (current >= target) { el.textContent = target.toLocaleString('pt-BR'); return; }
          el.textContent = Math.floor(current).toLocaleString('pt-BR');
          requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
      });
    }, { threshold: 0.4 });
    counters.forEach(c => countIO.observe(c));
  }

  // --- 7. Reveal on scroll ---
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) { entry.target.classList.add('is-visible'); io.unobserve(entry.target); }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -60px 0px' });
    document.querySelectorAll('.for-who__card,.pg-card,.testimonial,.architecture__gallery figure,.capture__text,.lead-form,.location__text,.location__map,.developer__inner,.counter__item,.faq__item,.sec-head')
      .forEach(el => io.observe(el));
  }

  // --- 8. Scrollspy ---
  if ('IntersectionObserver' in window) {
    const sections = document.querySelectorAll('section[id]');
    const menuLinks = document.querySelectorAll('.hdr__menu a[href^="#"],.mobile-menu__nav a[href^="#"]');
    const spyIO = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const id = entry.target.id;
          menuLinks.forEach(link => link.classList.toggle('is-active', link.getAttribute('href') === `#${id}`));
        }
      });
    }, { rootMargin: '-40% 0px -55% 0px', threshold: 0 });
    sections.forEach(s => spyIO.observe(s));
  }

  // --- 9. Modal de captação ---
  const modal = document.getElementById('capture-modal');
  const modalForm = document.getElementById('capture-form');
  const modalContext = document.getElementById('modal-context');
  const modalInterest = document.getElementById('modal-interest');
  const modalTitle = document.getElementById('modal-title');
  const modalSubtitle = document.getElementById('modal-subtitle');

  const CONTEXTS = {
    hero:            { eyebrow:'Fazenda Canoa · Consultor dedicado', title:'Conheça a Fazenda Canoa', subtitle:'Um consultor retorna em breve com todas as informações.', interest:'Informações gerais' },
    book:            { eyebrow:'Material exclusivo', title:'Receba o book completo', subtitle:'Plantas, diferenciais e condições da Reserva Fazenda Canoa.', interest:'Book do empreendimento', mode:'book', submitLabel:'Solicitar book' },
    'lote-frente':   { eyebrow:'Tipologia · Frente-Lago', title:'Lote Frente-Lago', subtitle:'Acesso privilegiado à orla do Lago Corumbá IV.', interest:'Lote frente-lago' },
    'lote-vista':    { eyebrow:'Tipologia · Vista-Lago', title:'Lote Vista-Lago', subtitle:'Panorama aberto do espelho d\'água.', interest:'Lote vista-lago' },
    'lote-bosque':   { eyebrow:'Tipologia · Bosque', title:'Lote Bosque', subtitle:'Imerso em mata preservada.', interest:'Lote bosque' },
    visita:          { eyebrow:'Visita', title:'Agende sua visita', subtitle:'Tour presencial ou virtual com consultor.', interest:'Agendamento de visita' },
    consultor:       { eyebrow:'Atendimento', title:'Falar com um consultor', subtitle:'Resposta no mesmo dia útil.', interest:'Informações gerais' },
  };
  let lastFocused = null;
  const openModal = (key) => {
    const ctx = CONTEXTS[key] || CONTEXTS.consultor;
    if (modalContext) modalContext.value = key || 'consultor';
    if (modalTitle) modalTitle.textContent = ctx.title;
    if (modalSubtitle) modalSubtitle.textContent = ctx.subtitle;
    const ey = modal.querySelector('.modal__eyebrow'); if (ey) ey.textContent = ctx.eyebrow;
    const interestSel = modal.querySelector('#modal-interest');
    if (interestSel) interestSel.value = ctx.interest;
    const panel = modal.querySelector('.modal__panel');
    if (ctx.mode === 'book') { panel.classList.add('modal--book'); if (interestSel) interestSel.required = false; }
    else { panel.classList.remove('modal--book'); if (interestSel) interestSel.required = true; }
    lastFocused = document.activeElement;
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    const successEl = modal.querySelector('.modal__success'); if (successEl) successEl.hidden = true;
    if (modalForm) {
      modalForm.hidden = false;
      const btn = modalForm.querySelector('.modal__submit');
      btn.disabled = false;
      const label = ctx.submitLabel || 'Solicitar contato';
      btn.innerHTML = label + ' <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12h14M13 5l7 7-7 7"/></svg>';
    }
    setTimeout(() => modal.querySelector('input[name="nome"]')?.focus(), 300);
  };
  const closeModal = () => {
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
    if (modalForm) modalForm.reset();
    if (lastFocused?.focus) lastFocused.focus();
  };
  document.querySelectorAll('[data-capture]').forEach(el => el.addEventListener('click', e => { e.preventDefault(); openModal(el.dataset.capture); }));
  modal?.querySelectorAll('[data-modal-close]').forEach(el => el.addEventListener('click', closeModal));
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal?.getAttribute('aria-hidden') === 'false') closeModal(); });
  modalForm?.addEventListener('submit', e => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(modalForm));
    const isBook = modal.querySelector('.modal__panel').classList.contains('modal--book');
    if (!data.nome || !data.telefone) { alert('Preencha nome e WhatsApp.'); return; }
    if (!isBook && !data.interesse) { alert('Selecione seu interesse.'); return; }
    if (isBook) data.interesse = 'Book do empreendimento';

    // Mostra success state dentro do modal (form some, mensagem aparece — usuário fecha quando quiser)
    modalForm.hidden = true;
    const successEl = modal.querySelector('.modal__success');
    if (successEl) {
      successEl.hidden = false;
      const title = isBook ? 'Recebemos seu pedido!' : 'Recebemos seu contato!';
      const sub   = isBook ? 'Em breve enviamos o book completo pra você.' : 'Em breve um consultor entra em contato com você.';
      successEl.innerHTML = `<h3>${title}</h3><p>${sub}</p>`;
    }

    // Webhook ImobMeet é disparado server-side pelo plugin lfc-opcoes-plugin via lfc_submit_lead.
    fireLeadEvents(data).then((eventId) => {
      submitLeadLocal({ ...data, source: isBook ? 'book' : 'modal', event_id: eventId });
    });
  });

  // --- 10. Widget flutuante toggle ---
  const floatBar = document.getElementById('float-bar');
  const floatClose = document.getElementById('float-bar-close');
  const floatMini = document.getElementById('float-mini');
  const FK = 'fcanoa_viva_float_collapsed';
  const collapseFloat = () => { floatBar?.classList.add('is-collapsed'); floatMini?.classList.add('is-visible'); try { localStorage.setItem(FK, '1'); } catch(_){} };
  const expandFloat = () => { floatBar?.classList.remove('is-collapsed'); floatMini?.classList.remove('is-visible'); try { localStorage.setItem(FK, '0'); } catch(_){} };
  floatClose?.addEventListener('click', collapseFloat);
  floatMini?.addEventListener('click', expandFloat);
  try { if (localStorage.getItem(FK) === '1') { floatBar?.classList.add('is-collapsed'); requestAnimationFrame(() => floatMini?.classList.add('is-visible')); } } catch(_){}

  // Ripple removido nesta LP — estética cinematográfica não combina
})();
