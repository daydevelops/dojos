
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
    }
]

export default new VueRouter({
    routes:routes
})

