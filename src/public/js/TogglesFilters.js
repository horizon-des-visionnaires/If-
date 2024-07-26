document.addEventListener("DOMContentLoaded", function() {
    const toggleButton = document.getElementById('toggleFilterBarButton');
    const filterBar = document.querySelector('.filterBar');

    toggleButton.addEventListener('click', function() {
        if (filterBar.style.display === 'none' || filterBar.style.display === '') {
            filterBar.style.display = 'block';
        } else {
            filterBar.style.display = 'none';
        }
    });
});