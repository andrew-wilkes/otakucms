<template>
    <div class="pill">
      <input type="text" class="pure-input-rounded"
      :value="value"
      :placeholder="placeholder"
      v-on:input="updateValue($event.target.value)"
      v-on:keydown.enter.prevent>
      <button type="submit" class="pure-button" v-on:click="deleteTag">x</button>
  </div>
</template>

<script>
  export default {
    props: ['value', 'placeholder'],
    methods: {
      deleteTag () {
        this.$emit('deleteTag')
      },
      updateValue (value) {
        this.$emit('input', value)
      }
    }
  }
</script>

<style>
  .pill {
    display: inline-block;
    margin-left: 20px;
    overflow: hidden;
  }
</style>
