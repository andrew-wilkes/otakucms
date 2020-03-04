<template>
  <div>
    <h1>Register</h1>
      <form class="pure-form" v-on:submit.prevent>
          <fieldset>
              <input type="text" placeholder="Name" v-model="user.name">
              <input type="email" placeholder="Email" v-model="user.email" v-validate="'required|email'" name="email">              
              <input type="password" placeholder="Password" v-model="user.pass" v-validate="'required|min:4'" name="password">

              <button type="submit" @click="submit" class="pure-button pure-button-primary"
              :disabled="errors.any() || user.email == null || user.pass == null">Register</button>
          </fieldset>
          <div v-show="errors.has('email')" class="help danger">{{ errors.first('email') }}</div>
          <div v-show="errors.has('password')" class="help danger">{{ errors.first('password') }}</div>
          <div v-show="alert" class="help danger">{{ alert }}</div>
      </form>
  </div>
</template>

<script>
import axios from 'axios'
import router from '../router'
import { store } from '../main.js'

export default {
  data () {
    return {
      user: {},
      alert: ''
    }
  },
  mounted () {
    if (store.get('loggedIn', 'Login')) {
      router.push('/')
    }
  },
  methods: {
    submit () {
      axios.post(store.get('phpServer', 'Register') + '?class=User&method=register', this.user).then(
          response => {
            store.set('msg', response.data.msg, 'Register')
            router.push('Login')
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
  div.danger {
    color: red;
    border-color: red;
  }
  input.danger {
    border-color: red !important;
  }
</style>
