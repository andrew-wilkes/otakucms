<template>
  <div>
    <h1>Image Management</h1>
    <p>Image management is done in the cloud using <a href="https://cloudinary.com" target="_blank">Cloudinary</a>. <button class="pure-button button-xsmall" @click="openWindow('https://cloudinary.com/console')">Dashboard</button>
    <form class="pure-form pure-form-stacked" v-on:submit.prevent>
      <p class="note">Note that individual images may be uploaded, cropped, and inserted using the in-content region editor when you go to edit web page content.</p>
      <fieldset>
        <legend>Image Upload Settings</legend>
        <label>Cloud Name</label>
        <input type="text" v-model="idata.cloud_name" v-validate="'required|alpha_dash'" name="cloud_name" :class="{ danger: errors.has('cloud_name') }">
        <div v-show="errors.has('cloud_name')" class="help danger">{{ errors.first('cloud_name') }}</div>

        <label>Upload Preset</label>
        <input type="text" v-model="idata.upload_preset" v-validate="'required|alpha_dash'" name="upload_preset" :class="{ danger: errors.has('upload_preset') }">
        <div v-show="errors.has('upload_preset')" class="help danger">{{ errors.first('upload_preset') }}</div>
        <p class="note">The above settings are defined in your Cloudinary account and are required.</p>

        <label>Folder</label>
        <input type="text" v-model="idata.folder" v-validate="'alpha_dash'" name="folder" :class="{ danger: errors.has('folder') }">
        <div v-show="errors.has('folder')" class="help danger">{{ errors.first('folder') }}</div>
        <p class="note">The optional folder name is used to organize uploaded files.</p>

        <label>Maximum Image Width</label>
        <input type="text" v-model="idata.max_image_width" v-validate="'numeric|min_value:16'" name="max_image_width" :class="{ danger: errors.has('max_image_width') }">
        <div v-show="errors.has('max_image_width')" class="help danger">{{ errors.first('max_image_width') }}</div>

        <label>Maximum Image Height</label>
        <input type="text" v-model="idata.max_image_height" v-validate="'numeric|min_value:16'" name="max_image_height" :class="{ danger: errors.has('max_image_height') }">
        <div v-show="errors.has('max_image_height')" class="help danger">{{ errors.first('max_image_height') }}</div>
        <p class="note">Larger images will be resized according to the above dimension limits and their aspect ratio retained before they are saved. Use these settings to avoid saving unnecessarily large images to your account.</p>
      </fieldset>

      <button type="submit" @click="save" class="pure-button pure-button-primary" :class="{ 'button-success': saving }"
        :disabled="errors.any() || idata.cloud_name === '' || idata.upload_preset === ''">Save Settings</button>
    </form>
  </div>
</template>

<script>
import { store } from '../main.js'
import router from '../router'

export default {
  data () {
    return {
      settings: {},
      idata: 2,
      saving: false,
      uploading: false
    }
  },
  mounted () {
    if (store) {
      this.settings = store.get('settings', 'Images')
      this.idata = this.settings.find(item => item.key === 'images')
    } else {
      router.push('/')
    }
  },
  methods: {
    save () {
      this.saving = true
      store.set('settings', this.settings, 'Images')
      store.save('Settings', 'save', this.settings, 'Images').then(
        response => {
          setTimeout(this.saved, 200)
        }
      )
    },
    saved () {
      this.saving = false
    },
    openWindow (url) {
      window.open(url)
    }
  }
}
</script>

<style>
  .button-xsmall {
    font-size: 70%;
  }
  a, a:visited {
    color: #00c;
    text-decoration: none;
  }
  .note {
    font-size: 0.8em;
    font-style: italic;
  }
</style>
