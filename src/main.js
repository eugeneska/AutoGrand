document.addEventListener('DOMContentLoaded', () => {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const revealObserver = prefersReducedMotion
    ? null
    : new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.18, rootMargin: '0px 0px -8% 0px' });

  const attachRevealAnimation = (elements, baseIndex = 0) => {
    if (!elements || elements.length === 0) return;
    elements.forEach((el, index) => {
      if (!(el instanceof HTMLElement)) return;
      if (prefersReducedMotion) {
        el.classList.add('is-visible');
        return;
      }
      if (!el.classList.contains('animate-fade-up')) {
        el.classList.add('animate-fade-up');
      }
      const delay = ((baseIndex + index) % 6) * 70;
      el.style.setProperty('--reveal-delay', `${delay}ms`);
      revealObserver.observe(el);
    });
  };

  const initPageAnimations = () => {
    const staticTargets = document.querySelectorAll(
      'section h2, .stat-card, .faq-card, #about .border, #reviews .flex.flex-col, #how .flex.gap-6, #contact form'
    );
    attachRevealAnimation(Array.from(staticTargets));
  };

  const burger = document.getElementById('burger');
  const mobileMenu = document.getElementById('mobile-menu');

  if (burger && mobileMenu) {
    burger.addEventListener('click', () => {
      const isOpen = !mobileMenu.classList.contains('hidden');
      mobileMenu.classList.toggle('hidden');
      const spans = burger.querySelectorAll('span');

      if (isOpen) {
        spans[0].style.transform = '';
        spans[1].style.opacity = '1';
        spans[2].style.transform = '';
      } else {
        spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
        spans[1].style.opacity = '0';
        spans[2].style.transform = 'rotate(-45deg) translate(5px, -5px)';
      }
    });

    mobileMenu.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        mobileMenu.classList.add('hidden');
        const spans = burger.querySelectorAll('span');
        spans[0].style.transform = '';
        spans[1].style.opacity = '1';
        spans[2].style.transform = '';
      });
    });
  }

  const lotsContainer = document.getElementById('lots-container');
  const lotsPrev = document.getElementById('lots-prev');
  const lotsNext = document.getElementById('lots-next');
  const reviewsContainer = document.getElementById('reviews-container');
  const reviewsPrev = document.getElementById('reviews-prev');
  const reviewsNext = document.getElementById('reviews-next');

  const fallbackLots = [
    { title: 'Toyota Camry', body_type: 'Седан', engine: '3.0L', year: 2021, price: 'от 2 100 000 ₽', image_url: './assets/images/camry.png' },
    { title: 'Nissan Leaf', body_type: 'Хэтчбек', engine: 'Электро', year: 2022, price: 'от 1 750 000 ₽', image_url: './assets/images/nissan.png' },
    { title: 'BMW 5 Series', body_type: 'Седан', engine: '2.0L Turbo', year: 2020, price: 'от 2 850 000 ₽', image_url: './assets/images/bmw.png' }
  ];
  let currentLots = [...fallbackLots];

  function renderLots(lots) {
    if (!lotsContainer) return;
    currentLots = Array.isArray(lots) ? lots : [];
    lotsContainer.innerHTML = currentLots.map((lot, index) => `
      <div class="border border-text/15 rounded-[28px] overflow-hidden transition hover:shadow-lg min-w-full md:min-w-0 snap-start shrink-0 md:shrink px-2 md:px-0 lot-card-animate">
        <div class="p-5">
          <img src="${lot.image_url}" alt="${lot.title}" class="w-full h-[200px] md:h-[240px] object-cover rounded-xl">
        </div>
        <div class="px-5 md:px-6 pb-6 md:pb-7">
          <h3 class="text-[18px] md:text-[22px] font-bold text-text mb-3 md:mb-4">${lot.title}</h3>
          <div class="flex flex-col gap-2 text-[15px] md:text-[17px] mb-4 md:mb-6">
            <p class="flex justify-between"><span class="text-text-secondary">Кузов:</span> <span class="text-text-secondary font-semibold">${lot.body_type}</span></p>
            <p class="flex justify-between"><span class="text-text-secondary">Двигатель:</span> <span class="text-text-secondary font-semibold">${lot.engine}</span></p>
            <p class="flex justify-between"><span class="text-text-secondary">Год выпуска:</span> <span class="text-text-secondary font-semibold">${lot.year}</span></p>
          </div>
          <p class="text-[18px] md:text-[22px] font-bold text-text mb-4 md:mb-6">${lot.price}</p>
          <button type="button" class="btn btn-primary w-full text-[14px] md:text-[15px]" data-lot-trigger data-lot-index="${index}">Подробнее</button>
        </div>
      </div>
    `).join('');

    const lotCards = lotsContainer.querySelectorAll(':scope > div');
    attachRevealAnimation(Array.from(lotCards), 2);
  }

  const lotModal = document.getElementById('lot-modal');
  const lotModalOverlay = document.getElementById('lot-modal-overlay');
  const lotModalClose = document.getElementById('lot-modal-close');
  const lotModalMainImage = document.getElementById('lot-modal-main-image');
  const lotModalThumbs = document.getElementById('lot-modal-thumbs');
  const lotModalTitle = document.getElementById('lot-modal-title');
  const lotModalPrice = document.getElementById('lot-modal-price');

  const getLotImages = (lot) => {
    if (!lot || typeof lot !== 'object') return [];
    const raw = Array.isArray(lot.gallery) ? lot.gallery : (Array.isArray(lot.images) ? lot.images : []);
    const sanitized = raw.filter((image) => typeof image === 'string' && image.trim() !== '');
    if (sanitized.length > 0) return sanitized.slice(0, 6);
    if (typeof lot.image_url === 'string' && lot.image_url.trim() !== '') return [lot.image_url];
    return ['./assets/images/bmw.png'];
  };

  const updateModalImage = (src, alt) => {
    if (!lotModalMainImage) return;
    lotModalMainImage.src = src;
    lotModalMainImage.alt = alt;
  };

  const openLotModal = (lot) => {
    if (!lotModal || !lotModalTitle || !lotModalPrice || !lotModalThumbs) return;
    const lotTitle = lot.title || 'Автомобиль';
    const lotPrice = lot.price || '';
    const images = getLotImages(lot);

    lotModalTitle.textContent = lotTitle;
    lotModalPrice.textContent = lotPrice;
    updateModalImage(images[0], lotTitle);

    lotModalThumbs.innerHTML = images.map((image, index) => `
      <button type="button" class="lot-modal-thumb overflow-hidden rounded-[12px] border ${index === 0 ? 'border-primary' : 'border-border'} cursor-pointer" data-image="${image}">
        <img src="${image}" alt="${lotTitle} фото ${index + 1}" class="w-full h-[74px] object-cover">
      </button>
    `).join('');

    lotModalThumbs.querySelectorAll('[data-image]').forEach((button) => {
      button.addEventListener('click', () => {
        const src = button.getAttribute('data-image');
        if (!src) return;
        updateModalImage(src, lotTitle);
        lotModalThumbs.querySelectorAll('.lot-modal-thumb').forEach((thumb) => thumb.classList.remove('border-primary'));
        lotModalThumbs.querySelectorAll('.lot-modal-thumb').forEach((thumb) => {
          if (!thumb.classList.contains('border-border')) thumb.classList.add('border-border');
        });
        button.classList.add('border-primary');
        button.classList.remove('border-border');
      });
    });

    lotModal.classList.remove('hidden');
    lotModal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');
  };

  const closeLotModal = () => {
    if (!lotModal) return;
    lotModal.classList.add('hidden');
    lotModal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-hidden');
  };

  fetch('./api/lots.php')
    .then(r => r.json())
    .then(data => {
      const lots = data.lots && data.lots.length > 0 ? data.lots : fallbackLots;
      renderLots(lots);
    })
    .catch(() => {
      renderLots(fallbackLots);
    });

  if (lotsContainer && lotsPrev && lotsNext) {
    const getScrollAmount = () => {
      const card = lotsContainer.querySelector(':scope > div');
      if (!card) return 300;
      return card.offsetWidth + 24;
    };

    lotsNext.addEventListener('click', () => {
      lotsContainer.scrollBy({ left: getScrollAmount(), behavior: 'smooth' });
    });

    lotsPrev.addEventListener('click', () => {
      lotsContainer.scrollBy({ left: -getScrollAmount(), behavior: 'smooth' });
    });
  }

  if (reviewsContainer && reviewsPrev && reviewsNext) {
    const getReviewsScrollAmount = () => {
      const card = reviewsContainer.querySelector(':scope > div');
      if (!card) return 320;
      const styles = window.getComputedStyle(reviewsContainer);
      const gap = parseFloat(styles.columnGap || styles.gap || '16') || 16;
      return card.offsetWidth + gap;
    };

    reviewsNext.addEventListener('click', () => {
      reviewsContainer.scrollBy({ left: getReviewsScrollAmount(), behavior: 'smooth' });
    });

    reviewsPrev.addEventListener('click', () => {
      reviewsContainer.scrollBy({ left: -getReviewsScrollAmount(), behavior: 'smooth' });
    });
  }

  if (lotsContainer) {
    lotsContainer.addEventListener('click', (event) => {
      const target = event.target;
      if (!(target instanceof HTMLElement)) return;
      const trigger = target.closest('[data-lot-trigger]');
      if (!(trigger instanceof HTMLElement)) return;
      const lotIndex = Number(trigger.dataset.lotIndex);
      const selectedLot = currentLots[lotIndex];
      if (!selectedLot) return;
      openLotModal(selectedLot);
    });
  }

  if (lotModalOverlay) {
    lotModalOverlay.addEventListener('click', closeLotModal);
  }

  if (lotModalClose) {
    lotModalClose.addEventListener('click', closeLotModal);
  }

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    if (!lotModal || lotModal.classList.contains('hidden')) return;
    closeLotModal();
  });

  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', (e) => {
      const targetId = anchor.getAttribute('href');
      if (targetId === '#') return;
      const target = document.querySelector(targetId);
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  initPageAnimations();
});
