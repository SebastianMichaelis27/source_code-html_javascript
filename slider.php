<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Slider Fleksibel dengan Gap</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    .slider {
      position: relative;
      overflow: hidden;
      border-radius: 10px;
      touch-action: pan-y;
    }

    .slides {
      display: flex;
      transition: transform 0.3s ease;
      will-change: transform;
      gap: 16px; /* <-- tambahkan gap */
    }

    .slide {
      flex-shrink: 0;
      background: darkorange;
      height: 120px;
      color: white;
      font-size: 1.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .nav {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      font-size: 2rem;
      background: rgba(0,0,0,0.4);
      color: white;
      border: none;
      padding: 10px;
      cursor: pointer;
      z-index: 10;
    }

    .prev { left: 10px; }
    .next { right: 10px; }
  </style>
</head>
<body>

<div class="slider" id="slider1"></div>
<div class="slider" id="slider2"></div>

<script>
function initSlider(sliderId, items, visibleCount = 1) {
  const slider = document.getElementById(sliderId);
  slider.innerHTML = `
    <div class="slides"></div>
    <button class="nav prev">&#10094;</button>
    <button class="nav next">&#10095;</button>
  `;

  const slidesContainer = slider.querySelector('.slides');
  const gap = 16; // jarak antar slide (px)

  const slides = [];
  for (let i = items.length - visibleCount; i < items.length; i++) {
    slides.push(`<div class="slide">${items[i]}</div>`);
  }
  for (let item of items) {
    slides.push(`<div class="slide">${item}</div>`);
  }
  for (let i = 0; i < visibleCount; i++) {
    slides.push(`<div class="slide">${items[i]}</div>`);
  }

  slidesContainer.innerHTML = slides.join('');
  const allSlides = slidesContainer.querySelectorAll('.slide');

  allSlides.forEach(slide => {
    slide.style.flexShrink = '0';
  });

  let index = visibleCount;
  let sliderWidth = slider.offsetWidth;
  let slideWidth = 0;
  let slideStep = 0;
  let isDragging = false;
  let startX = 0;
  let currentTranslate = 0;
  let prevTranslate = 0;
  let autoSlide;

  function setPosition() {
    slidesContainer.style.transform = `translateX(${-index * slideStep}px)`;
    currentTranslate = -index * slideStep;
    prevTranslate = currentTranslate;
  }

  function updateDimensions() {
    sliderWidth = slider.offsetWidth;
    slideWidth = (sliderWidth - gap * (visibleCount - 1)) / visibleCount;
    slideStep = slideWidth + gap;

    allSlides.forEach(slide => {
      slide.style.minWidth = slideWidth + 'px';
    });

    setPosition();
  }

  function nextSlide() {
    index++;
    slidesContainer.style.transition = 'transform 0.3s ease';
    setPosition();
    if (index >= allSlides.length - visibleCount) {
      setTimeout(() => {
        slidesContainer.style.transition = 'none';
        index = visibleCount;
        requestAnimationFrame(setPosition);
      }, 300);
    }
  }

  function prevSlide() {
    index--;
    slidesContainer.style.transition = 'transform 0.3s ease';
    setPosition();
    if (index < visibleCount) {
      if (index === visibleCount - 1) {
        setTimeout(() => {
          slidesContainer.style.transition = 'none';
          index = items.length + visibleCount - 1;
          requestAnimationFrame(setPosition);
        }, 300);
      } else {
        slidesContainer.style.transition = 'none';
        index = items.length + visibleCount - 1;
        requestAnimationFrame(setPosition);
      }
    }
  }

  function startAutoSlide() {
    autoSlide = setInterval(() => {
      nextSlide();
    }, 5000);
  }

  function stopAutoSlide() {
    clearInterval(autoSlide);
  }

  // Touch & Mouse Events
  slider.addEventListener('touchstart', (e) => {
    isDragging = true;
    startX = e.touches[0].clientX;
    slidesContainer.style.transition = 'none';
    stopAutoSlide();
  });

  slider.addEventListener('touchmove', (e) => {
    if (!isDragging) return;
    const deltaX = e.touches[0].clientX - startX;
    currentTranslate = prevTranslate + deltaX;
    slidesContainer.style.transform = `translateX(${currentTranslate}px)`;
  });

  slider.addEventListener('touchend', () => {
    isDragging = false;
    const movedBy = currentTranslate - prevTranslate;
    const threshold = slideStep * 0.1;
    if (movedBy < -threshold) nextSlide();
    else if (movedBy > threshold) prevSlide();
    else {
      slidesContainer.style.transition = 'transform 0.3s ease';
      setPosition();
    }
    startAutoSlide();
  });

  let mouseDown = false;

  slider.addEventListener('mousedown', (e) => {
    isDragging = true;
    mouseDown = true;
    startX = e.clientX;
    slidesContainer.style.transition = 'none';
    stopAutoSlide();
  });

  slider.addEventListener('mousemove', (e) => {
    if (!isDragging || !mouseDown) return;
    const deltaX = e.clientX - startX;
    currentTranslate = prevTranslate + deltaX;
    slidesContainer.style.transform = `translateX(${currentTranslate}px)`;
  });

  slider.addEventListener('mouseup', () => {
    if (!mouseDown) return;
    isDragging = false;
    mouseDown = false;
    const movedBy = currentTranslate - prevTranslate;
    const threshold = slideStep * 0.1;
    if (movedBy < -threshold) nextSlide();
    else if (movedBy > threshold) prevSlide();
    else {
      slidesContainer.style.transition = 'transform 0.3s ease';
      setPosition();
    }
    startAutoSlide();
  });

  slider.addEventListener('mouseleave', () => {
    if (isDragging && mouseDown) {
      isDragging = false;
      mouseDown = false;
      const movedBy = currentTranslate - prevTranslate;
      const threshold = slideStep * 0.1;
      if (movedBy < -threshold) nextSlide();
      else if (movedBy > threshold) prevSlide();
      else {
        slidesContainer.style.transition = 'transform 0.3s ease';
        setPosition();
      }
      startAutoSlide();
    }
  });

  slider.querySelector('.prev').addEventListener('click', () => {
    stopAutoSlide();
    prevSlide();
    startAutoSlide();
  });

  slider.querySelector('.next').addEventListener('click', () => {
    stopAutoSlide();
    nextSlide();
    startAutoSlide();
  });

  window.addEventListener('resize', updateDimensions);
  updateDimensions();
  startAutoSlide();
  setPosition();
}
</script>

<script>
  // Contoh penggunaan
  initSlider("slider1", ["A", "B", "C", "D", "E"], 3); // tampil 3 item per layar
  initSlider("slider2", ["X", "Y", "Z"], 1); // tampil 1 item per layar
</script>

</body>
</html>
