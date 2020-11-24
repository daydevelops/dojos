require('./bootstrap');

window.events = new Vue();

window.flash = function( message, level ) {
    window.events.$emit('flash', {message,level});
};

import router from './routes.js';

Vue.component('dojo', require('./components/Dojo.vue').default);
Vue.component('AreYouSureModal', require('./components/AreYouSureModal.vue').default);
Vue.component('AvatarForm', require('./components/AvatarForm.vue').default);
Vue.component('flash', require('./components/Flash.vue').default);

const app = new Vue({
    el: '#app',
    router:router
});

function initializeCSRF() {
    axios.get('/sanctum/csrf-cookie').then(response => {
        axios.post('/login', {
            email:$('#email').val(),
            password: $('#password').val()
        })
    });
    return false;
}
