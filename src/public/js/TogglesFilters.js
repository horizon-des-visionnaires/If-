document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById('toggleFilterBarButton');
    const toggleButton2 = document.getElementById('toggleFilterBarButton2');
    const filterBar = document.getElementById('allPostFiltre');

    function toggleFilterBar(event) {
        event.stopPropagation();
        filterBar.classList.toggle('show');
    }
    toggleButton.addEventListener('click', toggleFilterBar);
    toggleButton2.addEventListener('click', toggleFilterBar);

    document.addEventListener('click', function (event) {
        if (!filterBar.contains(event.target) && !toggleButton2.contains(event.target)||!filterBar.contains(event.target) && !toggleButton.contains(event.target) ) {
            filterBar.classList.remove('show');
        }
    });
});