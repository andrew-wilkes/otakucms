<template>
  <div>
    <div v-if="greet">
      <div class="message">Welcome back {{user.name}}!</div>
      <p>You last logged in on <b>{{loggedOnTime}}</b> from IP address <b>{{user.last_ip}}</b></p>
    </div>
    <h1>Dashboard</h1>    

    <div class="pure-g">
        <div class="pure-u-1-2">
          <p>Software version <strong :class="{ 'green': isLatest, 'red': !isLatest }">{{thisVersionNumber}}</strong> 
          <p v-if="isLatest">This is the latest version.</p>
          <p v-else class="red">There is a new version.</p>        
        </div>
        <div v-if="!updated && !isLatest" class="pure-u-1-2">
          <button class="pure-button pure-button-primary" @click="updateSoftware">Update to version {{latestVersionNumber}}</button>
          <h4>Notes</h4>
          <ul>
            <li v-for="note in versionNotes">{{note}}</li>
          </ul>
        </div>
        <div v-if="updated" class="pure-u-1-2">
          <button @click="reloadPage" class="pure-button pure-button-primary">Activate Update</button>
          <ul>
            <li v-for="item in status.result.log" :class="item.class">{{item.txt}}</li>
          </ul>
        </div>
    </div>

  </div>
</template>

<script>
import dateformat from 'dateformat'
import { store } from '../main.js'
import router from '../router'

export default {
  data () {
    return {
      greet: false,
      user: null,
      status: { result: {} },
      updated: false
    }
  },
  computed: {
    loggedOnTime () {
      return dateformat(1000 * this.user.last_time, 'dddd, mmmm dS, yyyy, h:MM TT')
    },
    isLatest () {
      return !this.status.latestVersion || this.status.thisVersion === this.status.latestVersion.number
    },
    thisVersionNumber () {
      if (this.status.thisVersion) {
        return this.status.thisVersion
      }
    },
    latestVersionNumber () {
      if (this.status.latestVersion) {
        return this.status.latestVersion.number
      }
    },
    versionNotes () {
      if (this.status.latestVersion) {
        return this.status.latestVersion.notes
      }
    }
  },
  mounted () {
    if (store) {
      this.sayHi()
      // This is the first page to be displayed after login so need to delay loading status in order for the DOM to update
      this.$nextTick(this.init)
    }
  },
  methods: {
    sayHi () {
      let greet = store.get('greet', 'Dashboard')
      if (greet) {
        store.set('greet', false, 'Dashboard')
        this.user = store.get('user', 'Dashboard')
      }
      this.greet = greet
    },
    updateSoftware () {
      store.load('Status', 'update', 'Dashboard').then(
        response => {
          this.status = response.data
          this.updated = true
        })
    },
    reloadPage () {
      window.location.reload(true)
      router.push('logout')
    },
    init () {
      store.load('Status', null, 'Dashboard').then(
        response => {
          this.status = response.data
        }
      )
    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>
.red, .fail {
  color: red;
}
.green, .pass {
  color: green;
}
.notes {
  border-radius: 1em;
  border: 1px solid #ccc;
}
</style>
