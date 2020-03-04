<template>
  <tree-list topic="Categories" :new-object="newObject" metaPath="Category"></tree-list>
</template>

<script>
import { store } from '../main.js'
import router from '../router'
import treeList from './TreeList'

export default {
  data () {
    return {
      newObject: {
        'id': 0,
        'title': 'New Category',
        'key': 'new-category',
        'parent': 0,
        'depth': 1,
        'count': 0
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
