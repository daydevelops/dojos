
import VueRouter from 'vue-router';

let routes = [
    {
        path: '/',
        component: require('./views/Home.vue').default
    },
    {
        path: '/users',
        component: require('./views/Users.vue').default
    },
    {
        path: '/categories',
        component: require('./views/Categories.vue').default
    },
    {
        path: '/dojos/new',
        component: require('./views/DojoForm.vue').default
    },
    {
        name: 'EditDojo',
        path: '/dojos/:id',
        component: require('./views/DojoForm.vue').default,
        props:true
    },

]

export default new VueRouter({
    routes:routes,
})

