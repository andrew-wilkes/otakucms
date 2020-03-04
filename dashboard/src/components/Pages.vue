<template>
  <tree-list topic="Pages" :new-object="newObject" metaPath="Meta"></tree-list>
</template>

<script>
import { store } from '../main.js'
import router from '../router'
import treeList from './TreeList'
import dateformat from 'dateformat'

export default {
  data () {
    return {
      newObject: {
        'id': 0,
        'title': 'New Page',
        'key': 'new-page',
        'parent': 0,
        'depth': 1,
        'category': 0,
        'description': '',
        'menu': [],
        'published': dateformat(new Date(), 'isoDateTime', true).substring(0, 16), // UTC time
        'tags': [],
        'template': 'page',
        'live': false
      }
    }
  },
  mounted () {
    if (!store) {
      router.push('/')
    }
  },
  components: {
    treeList
  }
}
</script>
