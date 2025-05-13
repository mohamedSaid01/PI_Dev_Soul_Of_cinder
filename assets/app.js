import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');


import Toastify from 'toastify-js';
import 'toastify-js/src/toastify.css';
// Exemple d'utilisation
window.showToast = function(message, type = 'error') {
    Toastify({
        text: message,
        duration: 3000,
        close: true,
        gravity: "top", // `top` ou `bottom`
        position: "right", // `left`, `center` ou `right`
        backgroundColor: type === 'error' ? "#ff4444" : "#00C851", // Couleur en fonction du type
    }).showToast();
};


import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        events: '/api/rendezvous', // Endpoint pour récupérer les événements
        eventClick: function(info) {
            window.location.href = '/rendez-vous/' + info.event.id;
        }
    });

    calendar.render();
});






// Import SweetAlert2
import Swal from 'sweetalert2';

// Fonction pour afficher un toast d'erreur
function showErrorToast(message) {
    Swal.fire({
        icon: 'error',
        title: 'Erreur de connexion',
        text: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
}

// Vérifie s'il y a une erreur dans le DOM
document.addEventListener('DOMContentLoaded', function () {
    const errorDiv = document.querySelector('.alert.alert-danger');
    if (errorDiv) {
        const errorMessage = errorDiv.textContent.trim();
        showErrorToast(errorMessage);
    }
});
