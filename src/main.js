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

  const lotsSlider = document.querySelector('.lots-slider');
  const lotsPrev = document.getElementById('lots-prev');
  const lotsNext = document.getElementById('lots-next');

  if (lotsSlider && lotsPrev && lotsNext) {
    const getScrollAmount = () => {
      const card = lotsSlider.querySelector(':scope > div');
      if (!card) return 300;
      return card.offsetWidth + 24;
    };

    lotsNext.addEventListener('click', () => {
      lotsSlider.scrollBy({ left: getScrollAmount(), behavior: 'smooth' });
    });

    lotsPrev.addEventListener('click', () => {
      lotsSlider.scrollBy({ left: -getScrollAmount(), behavior: 'smooth' });
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
