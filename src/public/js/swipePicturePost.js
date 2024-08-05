document.addEventListener('DOMContentLoaded', function () {
    const swipeContainers = document.querySelectorAll('.swipe');

    swipeContainers.forEach(container => {
        const prevButton = container.querySelector('.swipe-control-prev');
        const nextButton = container.querySelector('.swipe-control-next');
        const items = container.querySelectorAll('.swipe-item');
        const bullets = container.querySelectorAll('.bullet');
        let currentIndex = 0;

        function updateSwipe() {
            items.forEach((item, index) => {
                item.classList.toggle('active', index === currentIndex);
            });
            bullets.forEach((bullet, index) => {
                bullet.classList.toggle('active', index === currentIndex);
            });
        }

        if (prevButton) {
            prevButton.addEventListener('click', () => {
                currentIndex = (currentIndex - 1 + items.length) % items.length;
                updateSwipe();
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', () => {
                currentIndex = (currentIndex + 1) % items.length;
                updateSwipe();
            });
        }

        bullets.forEach((bullet, index) => {
            bullet.addEventListener('click', () => {
                currentIndex = index;
                updateSwipe();
            });
        });

        updateSwipe();
    });
});