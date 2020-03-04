// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.

var debug = true // Set this to false for a production build

import Vue from 'vue'
import VeeValidate from 'vee-validate'
import axios from 'axios'
import App from './App'
import router from './router'

Vue.config.productionTip = false

Vue.use(VeeValidate, { delay: 100 })

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  template: '<App/>',
  components: { App }
})

// Set up a global event bus
const bus = new Vue()

// Define a store to hold the source of truth for the App state
const store = new Vue({
  data () {
    return {
      debug, // Shorthand for debug: debug
      state: {
        phpServer: 'http://otaku6.local/',
        msg: '',
        user: {},
        loggedIn: false,
        greet: false,
        settings: [],
        themes: [],
        item: false
      },
      set (id, value, src) {
        this.debug && console.log('Set ' + id + ' in store' + this.src(src))
        this.state[id] = value
      },
      get (id, src, reset) {
        this.debug && console.log('Got ' + id + ' from store' + this.src(src))
        let value = this.state[id]
        if (reset) {
          this.state[id] = false
        }
        return value
      },
      src (src) {
        if (src) {
          return ' by: ' + src
        } else {
          return ''
        }
      },
      load (className, method, src) {
        if (method) {
          method = '&method=' + method
        } else {
          method = ''
        }
        return axios.get(this.get('phpServer', src) + '?class=' + className + method, { withCredentials: true })
      },
      save (className, method, data, src) {
        return axios.post(this.get('phpServer', src) + '?class=' + className + '&method=' + method, data, { withCredentials: true })
      }
    }
  }
})

export { store, bus }
