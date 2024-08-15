document.addEventListener("DOMContentLoaded", function () {
    var profilPost = document.querySelector('.profilPost');
    var profilFavorites = document.querySelector('.profilFavorites');
    var profilePlanning = document.querySelector('.profilePlanning');
    var profilNotations = document.querySelector('.profilNotations');

    var showPostsButton = document.getElementById('showPosts');
    var showFavoritesButton = document.getElementById('showFavorites');
    var showPlanningButton = document.getElementById('showPlanning');
    var showNotationsButton = document.getElementById('showNotations');

    profilPost.style.display = 'block';
    profilFavorites.style.display = 'none';
    profilePlanning.style.display = 'none';
    profilNotations.style.display = 'none';

    showPostsButton.addEventListener('click', function () {
        profilPost.style.display = 'block';
        profilFavorites.style.display = 'none';
        profilePlanning.style.display = 'none';
        profilNotations.style.display = 'none';
    });

    showFavoritesButton.addEventListener('click', function () {
        profilPost.style.display = 'none';
        profilFavorites.style.display = 'block';
        profilePlanning.style.display = 'none';
        profilNotations.style.display = 'none';
    });

    showPlanningButton.addEventListener('click', function () {
        profilPost.style.display = 'none';
        profilFavorites.style.display = 'none';
        profilePlanning.style.display = 'block';
        profilNotations.style.display = 'none';
    });

    showNotationsButton.addEventListener('click', function () {
        profilPost.style.display = 'none';
        profilFavorites.style.display = 'none';
        profilePlanning.style.display = 'none';
        profilNotations.style.display = 'block';
    });
});
