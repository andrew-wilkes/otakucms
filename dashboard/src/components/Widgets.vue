<template>
  <div>
    <h1>Widgets</h1>
    <p class="note">The available widgets are provided by the current website theme. Widgets may be replicated on multiple web pages and contain content such as sign-up forms and Ads. Basically, raw cut and paste code (Javascript and HTML).</p>
    <form class="pure-form" v-on:submit.prevent>
        <fieldset v-for="widget in widgets">
          <legend>{{ widget.key }}</legend>
          <textarea class="widget" v-model="widget.value"></textarea>
      </fieldset>
      <button type="submit" @click="save" class="pure-button pure-button-primary" :class="{ 'button-success': saving }">Save</button>
    </form>
  </div>
</template>

<script>
import { store } from '../main.js'
import router from '../router'

export default {
  data () {
    return {
      widgets: [],
      saving: false
    }
  },
  mounted () {
    if (store) {
      store.load('Widgets', 'get', 'App').then(
        response => {
          var items = response.data
          let widgetNames = this.getWidgetAreaNames()
          widgetNames.forEach(name => {
            let widget = this.getWidget(name, items)
            if (widget) {
              this.widgets.push(widget)
            } else {
              this.widgets.push({ key: name, value: '' })
            }
          })
        }
      )
    } else {
      router.push('/')
    }
  },
  methods: {
    getWidgetAreaNames () {
      let project = store.get('settings', 'Widgets').find(item => item.key === 'project')
      let theme = store.get('themes', 'Widgets').find(item => item.name === project.theme)
      return theme.widget_areas
    },
    getWidget (id, items) {
      let widget = items.find(item => {
        return item.key === id
      })
      return widget
    },
    save () {
      this.saving = true
      store.save('Widgets', 'save', this.widgets, 'App').then(
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
<style>
.widget {
  border: 1px solid #ccc;
  width: 100%;
}
</style>
