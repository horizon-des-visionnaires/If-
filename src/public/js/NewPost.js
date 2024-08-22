

document.addEventListener('DOMContentLoaded', function() {
    var InputField = document.getElementById('postInputField');
    var postButton = document.getElementById('postButton');
    var commentModal = document.getElementById('profileModal');
    var modalContent = document.getElementById('modalContentPost');
    var closeButton = commentModal.querySelector('.close');

    // Fonction pour ouvrir la modal
    function openModal() {
        commentModal.style.display = 'block';
        modalContent.value = InputField.value;
    }

    // Fonction pour fermer la modal
    function closeModal() {
        commentModal.style.display = 'none';
    }

    // Événement pour ouvrir la modal et transférer le texte
    postButton.addEventListener('click', function() {
        openModal();
    });

    // Événement pour fermer la modal
    closeButton.addEventListener('click', function() {
        closeModal();
    });

    // Fermer la modal en cliquant en dehors du contenu
    window.addEventListener('click', function(event) {
        if (event.target === commentModal) {
            closeModal();
        }
    });

    // Mettre à jour le contenu du textarea de la modal lors de la saisie
    InputField.addEventListener('input', function() {
        modalContent.value = InputField.value;
    });


    var photoButton = document.getElementById('photoButton');
    if (photoButton) {
        photoButton.addEventListener('click', function() {
            openModal();
        });
    }
});