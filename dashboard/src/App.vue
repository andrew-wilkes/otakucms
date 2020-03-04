<template>
  <div id="layout" :class="{ active: menuActive }">
    <!-- Menu toggle -->
    <a id="menuLink" class="menu-link" :class="{ active: menuActive }" @click="menuActive = !menuActive">
        <!-- Hamburger icon -->
        <span></span>
    </a>
    <sidebar :enabled="sidebarEnabled"></sidebar>

    <div id="main">
      <div class="header">
          <a href="../" target="_blank">{{projectTitle}}</a>
      </div>

      <div class="content">
        <router-view></router-view>
      </div>
    </div>

    <div class="footer">
        <a href="https://otakucms.com" target="_blank">OtakuCMS</a>
    </div>
  </div>
</template>

<script>
import router from './router'
import sidebar from './components/Sidebar'
import { bus, store } from './main.js'
import alertify from 'alertify.js'

export default {
  data () {
    return {
      menuActive: false,
      projectTitle: '',
      router,
      sidebarEnabled: false
    }
  },
  components: {
    sidebar,
    alertify
  },
  mounted () {
    this.$nextTick(this.init)
    alertify.delay(1000)
    alertify.logPosition('top right')
  },
  methods: {
    init () {
      this.checkUserStatus()
      bus.$on('login', value => {
        this.sidebarEnabled = value
        if (value) {
          // Logged in
          this.loadData()
        }
      })
      bus.$on('settingsChanged', () => {
        this.setAppTitle()
      })
    },
    setAppTitle () {
      this.projectTitle = store.get('settings', 'App').find(item => item.key === 'project').name
      let titleEl = document.getElementsByTagName('title')[0]
      titleEl.innerText = this.projectTitle
    },
    checkUserStatus () {
      // Check logged in status of user
      store.load('User', 'get', 'App').then(
        response => {
          let cmd = response.data
          switch (cmd) {
            case 'register':
              store.set('loggedIn', false, 'App')
              router.push('Register')
              break

            case 'login':
              store.set('loggedIn', false, 'App')
              router.push('Login')
              break

            default:
              this.sidebarEnabled = true
              store.set('loggedIn', true, 'App')
              store.set('user', response.data[0], 'App')
              // Try to avoid session timeout
              setTimeout(this.checkUserStatus, 600000) // Every 10 minutes
              this.loadData()
              break
          }
        }
      )
    },
    loadData () {
      if (store.state.settings.length < 1) {
        // Load settings
        store.load('Settings', 'get', 'App').then(
          response => {
            store.set('settings', response.data, 'App')
            this.setAppTitle()
          }
        )
        store.load('Themes', 'get', 'App').then(
          response => {
            store.set('themes', response.data, 'App')
          }
        )
      }
    }
  }
}

</script>

<style>
.button-success {
  background: rgb(28, 184, 65); /* this is a green */
}

.button-error {
  background: rgb(202, 60, 60); /* this is a maroon */
}

.button-warning {
  background: rgb(223, 117, 20); /* this is an orange */
}

body {
  background-color: #2f2f2f;
  color: #fff;
}

#main {
    background-color: #fff;
}

/*
The content `<div>` is where all your content goes.
*/
.content {
    margin: 0;
    padding: 0 2em 50px 2em;
    max-width: 800px;
    line-height: 1.6em;
    background-color: #fff;
}

.header {
     margin: 0;
     color: #fff;
     text-align: left;
     padding: 1em 0 0 4em;
     border-bottom: 2px solid #999;
     background-color: #191818;
     min-height: 46px;
     box-sizing: border-box;
 }

.header a, .header a:visited {
     color: #fff; 
}

.header a:hover {
     color: #00cc37; 
}

.content-subhead {
    margin: 50px 0 20px 0;
    font-weight: 300;
    color: #888;
}

.footer {
  color: #c3c3c3;
  padding: 50px 0;
  text-align: center;
}

.footer a, .footer a:visited {
  color: #009bcc;
}

.footer a:hover {
  color: #00cc37;
}

.alertify-logs.top {
  top: 44px;
}
</style>
