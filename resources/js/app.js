import Vue from 'vue'
import './bootstrap';
import vuetify from './plugins/vuetify'

// global
Vue.component('navigation', require('./components/global/Navigation.vue').default);
Vue.component('lookup',     require('./components/global/Lookup.vue').default);

// cadastros
Vue.component('motoristas', require('./components/Motoristas.vue').default);
Vue.component('veiculos',   require('./components/Veiculos.vue').default);
Vue.component('pacientes',  require('./components/Pacientes.vue').default);

// viagens
Vue.component('viagens',        require('./components/viagem/Index.vue').default);
Vue.component('viagem-edit',    require('./components/viagem/Edit.vue').default);
Vue.component('viagem-list',    require('./components/viagem/Lista.vue').default);

// admin
Vue.component('admin-unidade',  require('./components/admin/Unidades.vue').default);
Vue.component('admin-usuario',  require('./components/admin/Usuarios.vue').default);
Vue.component('admin-reports',  require('./components/admin/Reports.vue').default);
Vue.component('admin-logs',     require('./components/admin/Logs.vue').default);

// public
Vue.component('auth-login',     require('./components/public/Login.vue').default);
Vue.component('chamada',        require('./components/public/Chamada.vue').default);

//dashboard
Vue.component('dashboard',                  require('./components/dashboard/Index.vue').default);
Vue.component('dashboard-top5',             require('./components/dashboard/Top5.vue').default);
Vue.component('dashboard-stats',            require('./components/dashboard/Stats.vue').default);
Vue.component('dashboard-graph-pacientes',  require('./components/dashboard/GraphPacientes.vue').default);
Vue.component('dashboard-graph-viagens',    require('./components/dashboard/GraphViagens.vue').default);
Vue.component('dashboard-next-viagens',     require('./components/dashboard/NextViagens.vue').default);
Vue.component('dashboard-absenteism',       require('./components/dashboard/Absenteism.vue').default);

/*
 * Tempo de debounce para autocompletes
 */
Vue.prototype.$debounceTime = 1200;

/*
 * Apenas abre uma URL em target _blank
 */
Vue.prototype.$openBlank = function (url) {
  window.open(url,'_blank')
},

/*
 * Abre um popup centralizado na tela
 */
Vue.prototype.$openPopup = function (url, title) {

  let windowWidth = window.innerWidth - 200;
  let windowHeight = window.innerHeight - 100;
  let left = (window.innerWidth / 2) - (windowWidth / 2)
  let popupParams = `width=${windowWidth}, height=${windowHeight}, top=50, left=${left}`;

  let popup = window.open(url, title, popupParams);
  if (!popup || popup.closed || typeof popup.closed=='undefined') {
      window.open(url, 'blank')
  }
}

const app = new Vue({
  el: '#app',
  vuetify
});
