document.addEventListener('DOMContentLoaded', () => {
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

  const fallbackLots = [
    { title: 'Nissan Leaf', body_type: 'Седан', engine: '3.0L', year: 2021, price: 'от 2 100 000 ₽', image_url: './assets/images/camry.jpg' },
    { title: 'Nissan Leaf', body_type: 'Хэтчбек', engine: 'Электро', year: 2022, price: 'от 1 750 000 ₽', image_url: './assets/images/nissan.jpg' },
    { title: 'BMW 5 Series', body_type: 'Седан', engine: '2.0L Turbo', year: 2020, price: 'от 2 850 000 ₽', image_url: './assets/images/bmw.png' }
  ];

  function renderLots(lots) {
    if (!lotsContainer) return;
    lotsContainer.innerHTML = lots.map(lot => `
      <div class="border border-text/15 rounded-[28px] overflow-hidden transition hover:shadow-lg min-w-full md:min-w-0 snap-start shrink-0 md:shrink px-2 md:px-0">
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
          <a href="#contact" class="btn btn-primary w-full text-[14px] md:text-[15px]">Подробнее</a>
        </div>
      </div>
    `).join('');
  }

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
});
