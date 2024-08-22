document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.getElementById('myDropdown');
    const dropsearch = document.getElementById('myDropsearch');

    const menuButton = document.querySelector('.item');
    const loupeButton = document.getElementById('drop');

    function toggleDropdown(event) {
        event.stopPropagation();
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }
    function toggleDropsearch(event) {
        event.stopPropagation();
        dropsearch.style.display = dropsearch.style.display === 'block' ? 'none' : 'block';
    }

    menuButton.addEventListener('click', toggleDropdown);
    loupeButton.addEventListener('click', toggleDropsearch);

    document.addEventListener('click', function(event) {
        if (!dropdown.contains(event.target) && !menuButton.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });
    document.addEventListener('click', function(event) {
        if (!dropsearch.contains(event.target) && !loupeButton.contains(event.target)) {
            dropsearch.style.display = 'none';
        }
    });
});