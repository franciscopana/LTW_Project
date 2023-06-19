function showPrevious() {
    const slider = document.querySelector('.slider');
    const activeSlide = slider.querySelector('.active');

    if (activeSlide.previousElementSibling) {
        activeSlide.classList.remove('active');
        activeSlide.previousElementSibling.classList.add('active');
    } else {
        // If the active slide is the first slide, wrap around to the last slide
        activeSlide.classList.remove('active');
        slider.lastElementChild.classList.add('active');
    }
}

function showNext() {
    const slider = document.querySelector('.slider');
    const activeSlide = slider.querySelector('.active');

    if (activeSlide.nextElementSibling) {
        activeSlide.classList.remove('active');
        activeSlide.nextElementSibling.classList.add('active');
    } else {
        // If the active slide is the last slide, wrap around to the first slide
        activeSlide.classList.remove('active');
        slider.firstElementChild.classList.add('active');
    }
}
