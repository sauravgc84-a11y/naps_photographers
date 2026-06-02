/* ============================================================
   script.js — Naps Photographers
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  // ─── LOADER ───
  const loader = document.getElementById('loader');
  if (loader) {
    window.addEventListener('load', () => {
      setTimeout(() => loader.classList.add('hidden'), 1800);
    });
  }

  // ─── NAVBAR SCROLL ───
  const navbar = document.getElementById('navbar');
  if (navbar) {
    const onScroll = () => {
      navbar.classList.toggle('scrolled', window.scrollY > 60);
      updateActiveLink();
    };
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  // ─── ACTIVE NAV LINK ───
  function updateActiveLink() {
    const sections = document.querySelectorAll('section[id]');
    const links    = document.querySelectorAll('.nav-link');
    let current    = '';
    sections.forEach(s => {
      if (window.scrollY >= s.offsetTop - 120) current = s.id;
    });
    links.forEach(l => {
      l.classList.toggle('active', l.getAttribute('href') === `#${current}`);
    });
  }

  // ─── HAMBURGER ───
  const hamburger = document.getElementById('hamburger');
  const navLinks  = document.getElementById('navLinks');
  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('active');
      navLinks.classList.toggle('open');
    });
    navLinks.addEventListener('click', e => {
      if (e.target.classList.contains('nav-link')) {
        hamburger.classList.remove('active');
        navLinks.classList.remove('open');
      }
    });
  }

  // ─── HERO SLIDER ───
  const slides  = document.querySelectorAll('.slide');
  const dotsContainer = document.getElementById('sliderDots');
  let current   = 0, timer;

  if (slides.length && dotsContainer) {
    // Build dots
    slides.forEach((_, i) => {
      const dot = document.createElement('button');
      dot.classList.add('dot');
      if (i === 0) dot.classList.add('active');
      dot.setAttribute('aria-label', `Slide ${i + 1}`);
      dot.addEventListener('click', () => goTo(i));
      dotsContainer.appendChild(dot);
    });

    function goTo(idx) {
      slides[current].classList.remove('active');
      dotsContainer.children[current].classList.remove('active');
      current = (idx + slides.length) % slides.length;
      slides[current].classList.add('active');
      dotsContainer.children[current].classList.add('active');
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    document.getElementById('nextSlide')?.addEventListener('click', () => { clearInterval(timer); next(); timer = setInterval(next, 5000); });
    document.getElementById('prevSlide')?.addEventListener('click', () => { clearInterval(timer); prev(); timer = setInterval(next, 5000); });

    timer = setInterval(next, 5000);
  }

  // ─── REVEAL ON SCROLL ───
  const reveals = document.querySelectorAll('.reveal');
  const revealObs = new IntersectionObserver((entries) => {
    entries.forEach((e, i) => {
      if (e.isIntersecting) {
        setTimeout(() => e.target.classList.add('visible'), i * 80);
        revealObs.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });
  reveals.forEach(el => revealObs.observe(el));

  // ─── GALLERY FILTER ───
  const filterBtns = document.querySelectorAll('.filter-btn');
  const galleryItems = document.querySelectorAll('.gallery-item');
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const filter = btn.dataset.filter;
      galleryItems.forEach(item => {
        const show = filter === 'all' || item.dataset.cat === filter;
        item.style.display = show ? '' : 'none';
        if (show) item.classList.remove('visible');
        setTimeout(() => { if (show) item.classList.add('visible'); }, 50);
      });
    });
  });

  // ─── LIGHTBOX ───
  window.openLightbox = (src, caption) => {
    const lb  = document.getElementById('lightbox');
    const img = document.getElementById('lightboxImg');
    const cap = document.getElementById('lightboxCaption');
    if (lb && img) {
      img.src = src;
      if (cap) cap.textContent = caption;
      lb.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
  };
  window.closeLightbox = () => {
    document.getElementById('lightbox')?.classList.remove('open');
    document.body.style.overflow = '';
  };
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeLightbox();
  });

  // ─── BOOKING FORM VALIDATION ───
  const bookingForm = document.getElementById('bookingForm');
  if (bookingForm) {
    bookingForm.addEventListener('submit', e => {
      const date = bookingForm.querySelector('[name="booking_date"]')?.value;
      const time = bookingForm.querySelector('[name="booking_time"]')?.value;
      const svc  = bookingForm.querySelector('[name="service_id"]')?.value;
      if (!date || !time || !svc) {
        e.preventDefault();
        showToast('Please fill in all required fields.', 'error');
        return;
      }
      const selected = new Date(date);
      const today    = new Date();
      today.setHours(0,0,0,0);
      if (selected <= today) {
        e.preventDefault();
        showToast('Please select a future date.', 'error');
      }
    });
  }

  // ─── PAYMENT FORM (card formatting) ───
  const cardInput = document.getElementById('card_number');
  if (cardInput) {
    cardInput.addEventListener('input', e => {
      let v = e.target.value.replace(/\D/g,'').substring(0,16);
      e.target.value = v.match(/.{1,4}/g)?.join(' ') || v;
    });
  }
  const expiryInput = document.getElementById('expiry');
  if (expiryInput) {
    expiryInput.addEventListener('input', e => {
      let v = e.target.value.replace(/\D/g,'').substring(0,4);
      if (v.length >= 2) v = v.substring(0,2) + '/' + v.substring(2);
      e.target.value = v;
    });
  }
  const cvvInput = document.getElementById('cvv');
  if (cvvInput) {
    cvvInput.addEventListener('input', e => {
      e.target.value = e.target.value.replace(/\D/g,'').substring(0,4);
    });
  }

  // ─── TOAST UTILITY ───
  window.showToast = (msg, type = 'info') => {
    const existing = document.querySelector('.toast.dynamic');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.className = `toast toast-${type} dynamic`;
    toast.textContent = msg;
    document.body.appendChild(toast);
    requestAnimationFrame(() => {
      requestAnimationFrame(() => toast.classList.add('show'));
    });
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 400);
    }, 4000);
  };

  // ─── SMOOTH SCROLL ───
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const target = document.querySelector(a.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // ─── ADMIN SIDEBAR TOGGLE ───
  const sidebarToggle = document.getElementById('sidebarToggle');
  const adminSidebar  = document.getElementById('adminSidebar');
  if (sidebarToggle && adminSidebar) {
    sidebarToggle.addEventListener('click', () => {
      adminSidebar.classList.toggle('open');
    });
  }

  // ─── CONFIRM DIALOGS ───
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
  });

  // ─── AUTO-DISMISS ALERTS ───
  document.querySelectorAll('.alert-auto').forEach(el => {
    setTimeout(() => {
      el.style.transition = 'opacity .5s';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 500);
    }, 4000);
  });

});
w