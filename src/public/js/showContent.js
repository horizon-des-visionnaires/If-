
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
    if(showFavoritesButton){
        profilFavorites.style.display = 'none';
    }
    if(showPlanningButton){
        profilePlanning.style.display = 'none';
    }
    if(showNotationsButton){
        profilNotations.style.display = 'none';
    }

    showPostsButton.addEventListener('click', function () {
        profilPost.style.display = 'block';
        if(showFavoritesButton){
            profilFavorites.style.display = 'none';
        }
        if(showPlanningButton){
            profilePlanning.style.display = 'none';
        }
        if(showNotationsButton){
            profilNotations.style.display = 'none';
        }
    });
    if(showFavoritesButton){
        showFavoritesButton.addEventListener('click', function () {
            profilPost.style.display = 'none';
            profilFavorites.style.display = 'block';
            if(showPlanningButton){
                profilePlanning.style.display = 'none';
            }
            if(showNotationsButton){
                profilNotations.style.display = 'none';
            }
        });
    }
    if(showPlanningButton){
        showPlanningButton.addEventListener('click', function () {
            profilPost.style.display = 'none';
            if(showFavoritesButton){
                profilFavorites.style.display = 'none';
            }
            profilePlanning.style.display = 'block';
            if(showNotationsButton){
                profilNotations.style.display = 'none';
            }
        });
    }
    if(showNotationsButton){
        showNotationsButton.addEventListener('click', function () {
            profilPost.style.display = 'none';
            if(showFavoritesButton){
                profilFavorites.style.display = 'none';
            }
            if(showPlanningButton){
                profilePlanning.style.display = 'none';
            }
            profilNotations.style.display = 'block';
        });
    }
});
