<template>
  <div v-show="alert" class="help danger">{{ alert }}</div>
</template>

<script>
import router from '../router'
import { bus, store } from '../main.js'

export default {
  data () {
    return {
      alert: ''
    }
  },
  created () {
    this.logOut()
  },
  methods: {
    logOut () {
      store.load('User', 'logOff', 'Logout').then(
          response => {
            store.set('msg', 'You have been logged out', 'Logout')
            store.set('loggedIn', false, 'Logout')
            bus.$emit('login', false)
            router.push('Login')
          }
        ).catch(error => {
          this.alert = error.response.statusText
        })
    }
  }
}
</script>
