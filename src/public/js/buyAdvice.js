document.addEventListener('DOMContentLoaded', () => {
    // Gérer l'ouverture des modaux
    const openModalButtons = document.querySelectorAll('.BuyAdviceButton button');
    const closeModalSpans = document.querySelectorAll('.close2');

    openModalButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-modal-id');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = "block";
            }
        });
    });

    closeModalSpans.forEach(span => {
        span.addEventListener('click', () => {
            const modalId = span.getAttribute('data-modal-id');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = "none";
            }
        });
    });

    window.addEventListener('click', (event) => {
        // Vérifiez si le clic est à l'extérieur du modal
        const modals = document.querySelectorAll('.modal2');
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });
    });

    // Empêcher la propagation des clics à l'intérieur du modal
    const modals = document.querySelectorAll('.modal2');
    modals.forEach(modal => {
        modal.addEventListener('click', (event) => {
            event.stopPropagation(); // Empêche le clic de se propager au window
        });
    });
});
