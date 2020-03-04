<template>
  <div>
    <h1>Category Details</h1>
    <form class="pure-form pure-form-stacked" v-on:submit.prevent>
      <fieldset>
        <legend>Title</legend>
        <input type="text" v-model="category.data.title" v-validate="'required|regex:^[^\<\>]*$'" name="title" v-on:keyup="suggestSlug" :class="{ danger: errors.has('title') }">
        <div v-show="errors.has('title')" class="help danger">{{ errors.first('title') }}</div>
      </fieldset>
      <fieldset v-show="category.data.key !== 'home'">
        <legend>URL Slug</legend>
        <input type="text" v-model="category.data.key" v-validate="'required|alpha_dash'" name="slug" :class="{ danger: errors.has('slug') }">
        <div v-show="errors.has('slug')" class="help danger">{{ errors.first('slug') }}</div>
      </fieldset>
      <button type="submit" @click="save" class="pure-button pure-button-primary"
        :disabled="errors.any() || category.data.key === '' || category.data.title === ''">Save</button>
    </form>
  </div>
</template>

<script>
import { store } from '../main.js'
import router from '../router'

export default {
  data () {
    return {}
  },
  computed: {
    category () {
      // Get item and reset it so that it is gone from the store if the user navigates to another page
      return store.get('item', 'Category', true)
    }
  },
  mounted () {
    if (!store) {
      router.push('/')
    }
  },
  methods: {
    suggestSlug () {
      if (this.category.data.key !== 'home') {
        this.category.data.key = this.category.data.title.toLowerCase().replace(/\s+/g, '-').replace(/[^-a-z0-9=_.]+/, '')
      }
    },
    save () {
      store.set('item', this.category, 'Category')
      router.push('Categories')
    }
  }
}
</script>
