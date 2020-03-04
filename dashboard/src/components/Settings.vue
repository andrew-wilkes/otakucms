<template>
  <div>
    <h1>Settings</h1>
    <form class="pure-form pure-form-stacked" v-on:submit.prevent>
      <fieldset>
        <legend>Project Name</legend>
        <input type="text" v-model="settings[0].name">
      </fieldset>
      <fieldset>
        <legend>Notes</legend>
        <textarea class="widget" v-model="settings[0].notes"></textarea>
      </fieldset>
      <fieldset>
        <legend>Theme</legend>
        <select v-model="settings[0].theme">
        <option v-for="theme in themes" :value="theme.name">{{ theme.description }}</option>
        </select>
      </fieldset>
      <button type="submit" @click="save" class="pure-button pure-button-primary" :class="{ 'button-success': saving }">Save</button>
    </form>
  </div>
</template>

<script>
import { bus, store } from '../main.js'
import router from '../router'

export default {
  data () {
    return {
      settings: [{ name: '' }],
      themes: [],
      saving: false
    }
  },
  mounted () {
    if (store) {
      this.settings = store.get('settings', 'Settings')
      this.themes = store.get('themes', 'Settings')
    } else {
      router.push('/')
    }
  },
  methods: {
    save () {
      this.saving = true
      store.set('settings', this.settings, 'Settings')
      bus.$emit('settingsChanged', null)
      store.save('Settings', 'save', this.settings, 'App').then(
        response => {
          setTimeout(this.saved, 200)
        }
      )
    },
    saved () {
      this.saving = false
    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>
