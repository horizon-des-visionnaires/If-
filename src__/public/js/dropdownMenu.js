document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.getElementById('myDropdown');
    const menuButton = document.querySelector('.item');

    function toggleDropdown(event) {
        event.stopPropagation();
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }

    menuButton.addEventListener('click', toggleDropdown);

    document.addEventListener('click', function(event) {
        if (!dropdown.contains(event.target) && !menuButton.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });
});