let lastScrollTop = 0;
const header = document.querySelector('.main-header');

window.addEventListener('scroll', function () {
    let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

    if (scrollTop > 50) {
        header.classList.add('header-scrolled');
    } else {
        header.classList.remove('header-scrolled');
    }

    // Thu nhỏ khi scroll xuống
    if (scrollTop > lastScrollTop && scrollTop > 100) {
        header.classList.add('header-shrink');
    } else {
        header.classList.remove('header-shrink');
    }

    lastScrollTop = scrollTop;
});
