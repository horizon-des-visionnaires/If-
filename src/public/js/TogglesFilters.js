document.getElementById('toggleFilterBarButton').addEventListener('click', function() {
    const filterBar = document.getElementById('filterBar');
    if (filterBar.style.display === 'none' || filterBar.style.display === '') {
        filterBar.style.display = 'block';
    } else {
        filterBar.style.display = 'none';
    }
});