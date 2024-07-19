document.addEventListener('DOMContentLoaded', function () {
    const swipes = document.querySelectorAll('.swipe');

    swipes.forEach(swipe => {
        const items = swipe.querySelectorAll('.swipe-item');
        let currentIndex = 0;

        const prevButton = swipe.querySelector('.swipe-control-prev');
        const nextButton = swipe.querySelector('.swipe-control-next');

        function updateCarousel() {
            items.forEach((item, index) => {
                item.classList.remove('active');
                if (index === currentIndex) {
                    item.classList.add('active');
                }
            });
        }

        prevButton.addEventListener('click', () => {
            currentIndex = (currentIndex === 0) ? items.length - 1 : currentIndex - 1;
            updateCarousel();
        });

        nextButton.addEventListener('click', () => {
            currentIndex = (currentIndex === items.length - 1) ? 0 : currentIndex + 1;
            updateCarousel();
        });

        updateCarousel();
    });
});