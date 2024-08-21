function loadNotifications() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', '/notification', true); // Fetch notifications from the notification page
    xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(xhr.responseText, 'text/html');
            const newNotificationCount = doc.querySelector('#notification-count');
            
            // Update notification count
            const notificationCountElement = document.querySelector('#notification-count');
            if (newNotificationCount) {
                notificationCountElement.textContent = newNotificationCount.textContent;
            } else {
                if (notificationCountElement) {
                    notificationCountElement.textContent = '0'; // or remove the element
                }
            }
        }
    };
    xhr.send();
}

// Load notifications every 5 seconds
setInterval(loadNotifications, 1000);

function loadMessages() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', window.location.href, true); // Rechargement de la même URL
    xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(xhr.responseText, 'text/html');
            const newNotifications = doc.querySelectorAll('.notification'); // Sélectionner toutes les nouvelles notifications
            const notificationContainer = document.querySelector('.notification-container'); // Container pour les notifications

            // Mise à jour des notifications
            notificationContainer.innerHTML = ''; // Vider l'ancien contenu
            newNotifications.forEach(notification => {
                notificationContainer.appendChild(notification);
            });

            // Défilement vers le bas
            const scrollTarget = document.getElementById('scroll-target');
            scrollTarget.scrollIntoView({ behavior: 'smooth' });
        }
    };
    xhr.send();
}

// Charger les notifications toutes les secondes
setInterval(loadMessages, 1000);

function loadMessages() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', window.location.href, true); // Rechargement de la même URL
    xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(xhr.responseText, 'text/html');
            const newMessage = doc.querySelector('#messages');
            if(newMessage){
                const newMessages= newMessage.innerHTML; // Sélectionne uniquement les nouveaux messages
                const chat = document.getElementById('messages');
                chat.innerHTML = newMessages; // Met à jour uniquement la partie des messages
                const scrollTarget = document.getElementById('scroll-target');
                chat.appendChild(scrollTarget); // Ajouter le cible de défilement
            }
        }
    };
    xhr.send();
}

// Charger les messages toutes les secondes
setInterval(loadMessages, 1000);

