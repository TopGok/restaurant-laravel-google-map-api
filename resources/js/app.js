import Vue from 'vue'
import App from './components/SearchFilterRestaurants.vue'
import axios from 'axios'
import * as VueGoogleMaps from 'vue2-google-maps'

(() => axios.get('api/googlemap_key').then(res => {
    Vue.use(VueGoogleMaps, {
        installComponents: true,
        load: {
            key: res.data,
            libraries: 'places'
        }
    })

    new Vue({
        el: '#app',
        render: h => h(App),
    })
}))()
