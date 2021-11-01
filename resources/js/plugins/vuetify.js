import Vue from 'vue'
import Vuetify from 'vuetify'
import VuetifyToast from 'vuetify-toast-snackbar'

Vue.use(Vuetify)

const veutifyObj = new Vuetify({
    theme: { dark: false },
});

Vue.use(VuetifyToast, {
    $vuetify: veutifyObj.framework,
    color: 'info',
    iconColor: 'transparent' ,
    showClose: false,
    timeout: 5000,
    x: 'center',
    y: 'bottom',
})

const opts = {
    theme: {
        themes: {
            light: {
                primary: '#17234e',
                secondary: '#0b51c5',
                accent: '#0091EA'
            },
        },
    },
}

export default new Vuetify(opts)
