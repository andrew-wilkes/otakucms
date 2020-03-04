<template>
  <div>
    <h1>Page Meta Data</h1>
    <form class="pure-form" v-on:submit.prevent>
      <fieldset>
        <legend>Title</legend>
        <input type="text" v-model="page.data.title" v-validate="'required|regex:^[^\<\>]*$'" name="title" v-on:keyup="suggestSlug" :class="{ danger: errors.has('title') }">
        <div v-show="errors.has('title')" class="help danger">{{ errors.first('title') }}</div>
      </fieldset>
      <fieldset v-show="page.data.key !== 'home'">
        <legend>URL Slug</legend>
        <input type="text" v-model="page.data.key" v-validate="'required|alpha_dash'" name="slug" :class="{ danger: errors.has('slug') }">
        <div v-show="errors.has('slug')" class="help danger">{{ errors.first('slug') }}</div>
      </fieldset>
      <fieldset>
        <legend>Category</legend>
        <select v-model="page.data.category">
        <option v-for="category in categories" :value="category.id">{{ category.title }}</option>
        </select>
      </fieldset>
      <fieldset>
        <legend>Tags</legend>
        <div><a @click="addTag()" title="Add a new tag"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#FFA500"><path d="M17,13H13V17H11V13H7V11H11V7H13V11H17M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" /></svg></a></div>
        <pill v-for="(tag, index) in page.data.tags" v-model="page.data.tags[index]" v-on:deleteTag="deleteTag(index)" placeholder="New tag" :key="index"></pill>
      </fieldset>
      <fieldset>
        <legend>Menu Placement</legend>        
        <label v-for="(menu, index) in menus" :for="'option-' + index" class="pure-checkbox">
          <input :id="'option-' + index" type="checkbox" v-model="selectedMenus[index]">
          {{ menu }}
        </label>
      </fieldset>
      <fieldset>
        <legend>Template</legend>
        <select v-model="page.data.template">
        <option v-for="template in templates" :value="template">{{ template }}</option>
        </select>
      </fieldset>
      <fieldset>
        <legend>Publication Date</legend>
        <input v-model="pubDate" type="date" placeholder="YYYY-MM-DD" v-validate="'required'" name="date">
      </fieldset>
      <fieldset>
        <legend>Timestamp</legend>
        <input v-model="pubTime" type="time" placeholder="HH:MM" v-validate="'required'" name="time">
      </fieldset>
      <fieldset>
        <legend>Visibility</legend>
        <label for="live-mode" class="pure-radio">
          <input type="radio" v-model="page.data.live" id="live-mode" name="mode" value="true"> Live
        </label>
        <label for="draft-mode" class="pure-radio">
          <input type="radio" v-model="page.data.live" id="draft-mode" name="mode" value="false"> Draft
        </label>
      </fieldset>
      <button type="submit" @click="save" class="pure-button pure-button-primary"
        :disabled="errors.any() || page.data.key === '' || page.data.title === ''">Save</button>
    </form>
  </div>
</template>

<script>
import { store } from '../main.js'
import router from '../router'
import dateformat from 'dateformat'
import pill from './Pill'

export default {
  data () {
    return {
      menus: [],
      selectedMenus: [],
      templates: [],
      categories: [],
      pubTime: '',
      pubDate: '',
      item: {}
    }
  },
  computed: {
    page () {
      this.item = store.get('item', 'Meta', true)
      return this.item
    }
  },
  components: {
    pill
  },
  mounted () {
    if (store) {
      let localDateTime = dateformat(new Date(this.item.data.published + 'Z'), 'isoDateTime')
      this.pubDate = localDateTime.substring(0, 10)
      this.pubTime = localDateTime.substring(11, 16)
      let pageMenus = this.item.data.menu
      if (typeof pageMenus === 'string') {
        pageMenus = pageMenus.split(',')
      }
      let theme = this.getTheme()
      this.menus = theme.menus
      theme.menus.forEach(menu => {
        this.selectedMenus.push(pageMenus.indexOf(menu) > -1)
      })

      store.save('Theme', 'get', { name: theme.name }, 'Meta').then(response => {
        this.templates = response.data.templates
      })
      store.load('Categories', 'get', 'categories').then(response => {
        this.categories = response.data
      })
    } else {
      router.push('/')
    }
  },
  methods: {
    addTag () {
      let lastTag = this.page.data.tags.slice(-1)[0]
      if (lastTag !== null && lastTag !== '') {
        this.page.data.tags.push(null)
      }
    },
    deleteTag (index) {
      this.page.data.tags.splice(index, 1)
    },
    suggestSlug () {
      if (this.page.data.key !== 'home') {
        this.page.data.key = this.page.data.title.trim().toLowerCase().replace(/\s+/g, '-').replace(/[^-a-z0-9=_.]+/, '')
      }
    },
    getTheme () {
      let project = store.get('settings', 'Meta').find(item => item.key === 'project')
      let theme = store.get('themes', 'Meta').find(item => item.name === project.theme)
      return theme
    },
    save () {
      let nonEmptyTags = []
      this.page.data.tags.forEach((tag) => {
        if (tag !== '') {
          nonEmptyTags.push(tag)
        }
      })
      this.page.data.tags = nonEmptyTags
      this.page.data.published = dateformat(new Date(this.pubDate + 'T' + this.pubTime), 'isoDateTime', true).substring(0, 16) // UTC time
      this.page.data.menu = []
      this.selectedMenus.forEach((value, index) => {
        if (value) {
          this.page.data.menu.push(this.menus[index])
        }
      })
      store.set('item', this.page, 'Meta')
      router.push('Pages')
    }
  }
}
</script>
