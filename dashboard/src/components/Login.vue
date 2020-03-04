<template>
  <div>
    <div class="message" v-if="msg">{{ msg }}</div>
    <h1>Login</h1>
      <form class="pure-form" v-on:submit.prevent>
          <fieldset>
              <input type="email" placeholder="Email" v-model="user.email" v-validate="'required'" name="email">              
              <input type="password" placeholder="Password" v-model="user.pass" v-validate="'required'" name="password">

              <button type="submit" @click="submit" class="pure-button pure-button-primary"
              :disabled="errors.any() || user.email == null">Login</button>
          </fieldset>
          <div v-show="alert" class="help danger">{{ alert }}</div>
      </form>
  </div>
</template>

<script>
import router from '../router'
import { bus, store } from '../main.js'

export default {
  data () {
    return {
      user: {},
      msg: '',
      alert: ''
    }
  },
  mounted () {
    this.$nextTick(this.showMessage)
  },
  methods: {
    showMessage () {
      if (store.get('loggedIn', 'Login')) {
        router.push('/')
      } else {
        this.msg = store.get('msg', 'Login')
      }
    },
    submit () {
      store.save('User', 'logOn', this.user, 'Login').then(
          response => {
            store.set('msg', '', 'Login')
            store.set('loggedIn', true, 'Login')
            bus.$emit('login', true)
            store.set('greet', true, 'Login')
            store.set('user', response.data[0], 'Login')
            router.push('/')
          }
        ).catch(error => {
          this.alert = error.response.statusText
        })
    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>
  .message {
    color: #090;
    font-weight: bold;
    font-size: 1.5em;
    margin-top: 20px;
  }
</style>
