import './bootstrap';

import Vue from 'vue';
import HomeComponent from './components/HomeComponent.vue';

Vue.component('home-component', HomeComponent);

const app = new Vue({
    el: '#app',
    data: {
        test: 'Vue is working!'
    },
    mounted() {
        console.log('Vue app mounted!', this.$el);
        console.log('Test data:', this.test);
    }
});
