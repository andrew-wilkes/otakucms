<template>
  <div>
    <h1>{{ topic }}</h1>
    <div><a @click="addItem()" title="Add a new item"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#FFA500"><path d="M17,13H13V17H11V13H7V11H11V7H13V11H17M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" /></svg></a></div>
    <p class="note">Indent levels determine parent-child relationships. List order determines the position in website menus.</p>

    <draggable class="tree" v-model="items" @end="ended()">
      <div v-for="(item, index) in items" class="tree-row" v-if="item.id">
        <div class="tree-indent" :style="indent(item.depth - 1)">
          <!-- Left arrow -->
          <a @click.stop="moveUp(item)"><svg v-if="item.depth > 1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#ccc"><path d="M20,10V14H11L14.5,17.5L12.08,19.92L4.16,12L12.08,4.08L14.5,6.5L11,10H20Z" /></svg></a>
          <!-- Right arrow -->
          <a @click.stop="moveDown(index, item)"><svg v-if="getPreviousSiblingId(index, item.parent)" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="-24 0 24 24" fill="#ccc"><path transform="scale(-1,1)" d="M20,10V14H11L14.5,17.5L12.08,19.92L4.16,12L12.08,4.08L14.5,6.5L11,10H20Z" /></svg></a>
        </div>
        <div class="tree-item">
        <a v-if="topic === 'Pages'" :href="'/' + (item.key === 'home' ? '' : item.key)" target="_blank" title="View the page and edit the content regions">{{ item.title }}</a>
        <span v-else>{{ item.title }}</span>
        </div>
        <div class="tools">
          <div class="tool">
            <a @click="editItemMeta(index, item)" title="Edit item meta data"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#ccc"><path d="M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.12,5.12L18.87,8.87M3,17.25V21H6.75L17.81,9.93L14.06,6.18L3,17.25Z" /></svg></a>
          </div>
          <div class="tool">
            <a @click="deleteItem(index, item)" title="Delete item"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#ccc"><path d="M13.46,12L19,17.54V19H17.54L12,13.46L6.46,19H5V17.54L10.54,12L5,6.46V5H6.46L12,10.54L17.54,5H19V6.46L13.46,12Z" /></svg></a>
          </div>
          <div class="tool drag-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#ccc"><path d="M16,20H20V16H16M16,14H20V10H16M10,8H14V4H10M16,8H20V4H16M10,14H14V10H10M4,14H8V10H4M4,20H8V16H4M10,20H14V16H10M4,8H8V4H4V8Z" /></svg>
          </div>
        </div>
      </div>
    </draggable>

    <p class="note">Drag and drop parent items to re-arrange the ordering. Use the arrows to change the parent-child relationships. Press the X to delete the item and move it's children up. Press the pencil icon to edit meta data. Click on a page name to open the page view for region editing of content and image insertion etc.</p>
  </div>
</template>

<script>
import { store } from '../main.js'
import router from '../router'
import draggable from 'vuedraggable'
import alertify from 'alertify.js'

export default {
  data () {
    return {
      items: []
    }
  },
  props: ['topic', 'newObject', 'metaPath'],
  components: {
    draggable,
    alertify
  },
  mounted () {
    if (store) {
      store.load(this.topic, 'get', this.topic).then(
        response => {
          this.items = response.data
          let itemToSave = store.get('item', this.topic)
          if (itemToSave) {
            this.items[itemToSave.index] = itemToSave.data
            store.set('item', false, this.topic)
            this.save()
          }
        }
      )
    }
  },
  methods: {
    editItemMeta (index, item) {
      store.set('item', { index, data: item }, this.topic)
      router.push(this.metaPath)
    },
    addItem () {
      this.newObject.id = this.getNextId()
      // Create a new memory location for the new item so that there is no mutation between newly added items
      this.items.splice(1, 0, Object.assign({}, this.newObject))
      this.rebuild()
    },
    getNextId () {
      let id = 0
      this.items.forEach(item => {
        if (item.id > id) {
          id = item.id
        }
      })
      return id + 1
    },
    deleteItem (index, item) {
      if (confirm('Are you sure that you want to delete this item?')) {
        // Reassign parent of any children
        this.items.forEach(ob => {
          if (ob.parent === item.id) {
            ob.parent = item.parent
          }
        })
        this.items.splice(index, 1)
        this.rebuild()
      }
    },
    ended () { // Drag ended
      this.rebuild()
    },
    moveUp (item) {
      item.depth--
      let parent = this.items.find(ob => {
        return item.parent === ob.id
      })
      item.parent = parent.parent
      this.rebuild()
    },
    moveDown (index, item) {
      item.depth++
      item.parent = this.getPreviousSiblingId(index, item.parent)
      this.rebuild()
    },
    rebuild () {
      this.items = [this.items[0]].concat(this.getChildren(this.items[0], this.items))
      this.save()
    },
    getChildren (parent, items, depth = 0) {
      var children = []
      if (depth++ < 100) { // Protection from an infinite loop (since this is a recursive function)
        items.forEach(item => {
          if (item.parent === parent.id) {
            item.depth = parent.depth + 1
            children.push(item)
            Array.prototype.push.apply(children, this.getChildren(item, items, depth)) // Credit: https://davidwalsh.name/merge-arrays-javascript
          }
        })
      }
      return children
    },
    save () {
      this.saving = true
      store.save(this.topic, 'save', this.items, this.topic).then(
        response => {
          alertify.log('Saved')
        }
      )
    },
    indent (depth) {
      return 'border-left: ' + (depth * 20) + 'px solid #666'
    },
    getPreviousSiblingId (index, parent) {
      // Return true if an item with the same parent preceeds the indexed item
      var parentId = false
      var i = index
      do {
        i--
        if (this.items[i].parent === parent) {
          parentId = this.items[i].id
        }
      } while (i !== 0 && parentId === false)
      return parentId
    }
  }
}
</script>

<style>
  .saved {
    color: #0c0;
    font-weight: bold;
    margin-left: 45%;
  }
  .tree {
    display: table;
    border-spacing: 0 10px;
    width: 100%;
  }
  .tree-row {
    width: 100%;
    height: 3em;
    background-color: #333;
    color: #ccc;
    display: table-row;
  }
  .tree-indent {
    display: table-cell;
    vertical-align: middle;
    padding-right: 20px;
    padding-left: 20px;
    box-sizing: border-box;
    width: 200px;
  }
  .tree-item {
    display: table-cell;
    vertical-align: middle;
  }
  .tree-row .tool {       
    margin-right: 20px;
    display: inline-block;
  }
  .tree-row .tools {
    display: table-cell;
    vertical-align: middle;
    text-align: right;
    padding-left: 20px;
  }
  .tree-indent:hover, .tree-row .tools:hover, .drag-icon:hover {
    cursor: grab;
  }
  a {
    cursor: pointer;
  }
  .tree-row .tools a, .tree-item a {
    color: #ccc !important;
    display: block;
    line-height: 3em;
  }
  .tree-row svg {
    vertical-align: middle;
  }
</style>
