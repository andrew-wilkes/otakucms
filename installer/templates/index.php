<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $app->title; ?></title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,300italic,700,700italic">
    <style>
        .help {
            display: block;
            font-size: 0.8em;
            margin-top: -1rem;
        }
        .row.help {
            margin-top: 0;
        }
        .danger, .fail {
            color: #ff3860;
        }
        .input.danger, .input:focus.danger {
            border-color: #ff3860;
        }
        .input {
            margin-bottom: 1rem;
        }
        .pass {
          color: #090;
        }
<?php echo CSS::$milligram; ?>
    </style>
  </head>
  <body>
    <div class="container">
    <h1><?php echo $app->title; ?></h1>
    <hr>
    <div class="row">
      <div class="column" id="app">
        <template v-if="error">
          <h3>{{testName}}</h3>
          <p class="danger">{{error}}</p>
        </template>
        <template v-else>
          <settings v-if="showSettings" v-on:done="processData($event)"></settings>
          <result v-if="showResult" :result="resultData"></result>
        </template>
      </div>
    </div>
  </div>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/vee-validate/2.0.0-beta.25/vee-validate.min.js"></script>
    <script src="https://unpkg.com/vue"></script>
  <script>
    Vue.use(VeeValidate, { delay: 1000 });

    new Vue({
        el: "#app",
        data: function() {
            return {
              showSettings: true,
              showResult: false,
              resultData: null,
              testName: "<?php echo $app->testName; ?>",
              error: "<?php echo $app->error; ?>"
            }
        },
        methods: {
          processData: function(data) {
            showSettings: false;
            this.showResult = true;
            this.resultData = data;
          }
        },
        components: {
            'settings': {
                data: function() {
                    return {
                      showSettings: true,
                  showSpinner: false,
                      config: {},
                      servers: [
                        {
                          id: "windows",
                          label: "IIS (Windows)"
                        },
                        {
                          id: "linux",
                          label: "Apache (most common on Linux servers)"
                        },
                        {
                          id: "nginx",
                          label: "NGINX"
                        }
                      ]
                    }
                },
            methods: {
              submit: function() {
                this.showSettings = false;
                this.showSpinner = true;
                let _this = this;
                        axios.post('', this.config).then( function(response) {
                          _this.showSpinner = false;
                          _this.$emit('done', response.data);
                        })
                        .catch( function(error) {
                            alert(error.response.statusText);
                        })            
              }
            },
          mounted: function() {
            this.config = {
              basePath: '<?php echo $app->get_base_path(); ?>',
              multiSite: false,
              subdomains: '',
              topDomain: '<?php echo $app->domain(); ?>',
              server: '<?php echo $app->get_server_type(); ?>',
              exampleContent: true,
              dashboard: '<?php echo $app->dashboard_name(); ?>',
              classes: 'classes-<?php echo $app->rand_str(4,6); ?>',
              themes: 'themes',
              images: 'images',
              photos: 'photos',
              thumbs: 'thumbs',
              dataFolder: 'data-<?php echo $app->rand_str(4,6); ?>',
              jsFolder: 'js',
              redirect: 'goto'
            };
          },
                template: 
                  `<div>
                  <div v-if="showSpinner">
                    <h2>Installing Your website ...</h2>
                    <div><?php echo SVG::$spinner; ?></div>
                  </div>
                  <div v-if="showSettings">
                    <h2>Website Configuration Settings</h2>
                    <p>Take some time to customize the settings for your website or accept the suggested settings. Finally, click on the button at the bottom of the page to start the install process.</p>
                    <form v-on:submit.prevent>
                        <fieldset>
                        <h3>Website Details</h3>
                          <label for="basePathField">Path to index.php file</label>
                  <input id="basePathField" type="text" name="basePath" v-model="config.basePath"
                  v-validate="'required|min:1'" :class="{'input': true, 'danger': errors.has('basePath') }">
                  <span v-show="errors.has('basePath')" class="help danger">{{ errors.first('basePath') }}</span>

                  <input type="checkbox" id="multiSiteField" name="multiSite" v-model="config.multiSite">
                  <label class="label-inline" for="multiSiteField">Is this website a multiple subdomain site?</label>

                  <div v-show="config.multiSite">
                    <label for="subdomainsField">Allowed sub domains</label>
                    <input type="text" placeholder="sub1,sub2,sub3" id="subdomainsField" name="subdomains"
                    v-model="config.subdomains">
                    <p>Comma-separated list of allowed sub domains for use with multi-site option</p>
                  </div>

                  <label for="topDomainField">Top level domain</label>
                  <input type="text" placeholder="yourdomain" name="topDomain" id="topDomainField"
                  v-model="config.topDomain" v-validate="'required'" :class="{'input': true, 'danger': errors.has('topDomain') }">
                  <p>Domain name minus country code e.g. "mysite.com" becomes "mysite"</p>
                  <span v-show="errors.has('topDomain')" class="help danger">{{ errors.first('topDomain') }}</span>

                  <fieldset>
                      <legend>Type of web server</legend>
                      <template v-for="server in servers">
                      <input type="radio" name="server" :id="server.id" :value="server.id" v-model="config.server">
                      <label class="label-inline" :for="server.id">{{server.label}}</label>
                    </template>
                  </fieldset>

                  <h3>Example Content</h3>
                  <fieldset>
                    <input type="checkbox" id="exampleContentField" name="exampleContent" v-model="config.exampleContent">
                    <label class="label-inline" for="exampleContentField">Install example content?</label>
                  </fieldset>

                  <h3>Folder Names</h3>

                  <p>Here is a chance to customize the locations of files to help with obscuring your site fingerprint.<br>
                  However, Javascript and image folders are easily discovered via URLs in the website code.</p>

                  <label for="dashboardField">Dashboard folder</label>
                  <input type="text" name="dashboard" id="dashboardField"
                  v-model="config.dashboard" v-validate="'required'" :class="{'input': true, 'danger': errors.has('dashboard') }">
                  <span v-show="errors.has('dashboard')" class="help danger">{{ errors.first('dashboard') }}</span>
                  <p>* Make a note of this location since it may not be linked from theme templates.</p>

                  <label for="classesField">Classes (plugins) folder</label>
                  <input type="text" placeholder="Enter folder name" name="classes" id="classesField"
                  v-model="config.classes" v-validate="'required'" :class="{'input': true, 'danger': errors.has('classes') }">
                  <span v-show="errors.has('classes')" class="help danger">{{ errors.first('classes') }}</span>

                  <label for="themesField">Themes folder</label>
                  <input type="text" placeholder="Enter themes folder name" name="themes" id="themesField"
                  v-model="config.themes" v-validate="'required'" :class="{'input': true, 'danger': errors.has('themes') }">
                  <span v-show="errors.has('themes')" class="help danger">{{ errors.first('themes') }}</span>

                  <label for="imagesField">Images folder</label>
                  <input type="text" placeholder="Enter images folder name" name="images" id="imagesField"
                  v-model="config.images" v-validate="'required'" :class="{'input': true, 'danger': errors.has('images') }">
                  <span v-show="errors.has('images')" class="help danger">{{ errors.first('images') }}</span>

                  <label for="thumbsField">Thumbnails folder</label>
                  <input type="text" placeholder="Enter thumbnails folder name" name="thumbs" id="thumbsField"
                  v-model="config.thumbs" v-validate="'required'" :class="{'input': true, 'danger': errors.has('thumbs') }">
                  <span v-show="errors.has('thumbs')" class="help danger">{{ errors.first('thumbs') }}</span>

                  <label for="photosField">Photos folder</label>
                  <input type="text" placeholder="Enter photos folder name" name="photos" id="photosField"
                  v-model="config.photos" v-validate="'required'" :class="{'input': true, 'danger': errors.has('photos') }">
                  <span v-show="errors.has('photos')" class="help danger">{{ errors.first('photos') }}</span>

                  <label for="dataField">Data folder</label>
                  <input type="text" placeholder="Enter data folder name" name="dataFolder" id="dataField"
                  v-model="config.dataFolder" v-validate="'required'" :class="{'input': true, 'danger': errors.has('dataFolder') }">
                  <span v-show="errors.has('dataFolder')" class="help danger">{{ errors.first('dataFolder') }}</span>

                  <label for="jsField">Javascript folder</label>
                  <input type="text" placeholder="Enter javascript folder name" name="jsFolder" id="jsField"
                  v-model="config.jsFolder" v-validate="'required'" :class="{'input': true, 'danger': errors.has('jsFolder') }">
                  <span v-show="errors.has('jsFolder')" class="help danger">{{ errors.first('jsFolder') }}</span>

                  <h3>Miscellaneous Settings</h3>

                  <label for="redirectField">URL redirect stub</label>
                  <input type="text" placeholder="Enter value for URL redirect stub" name="redirect" id="redirectField" v-model="config.redirect">

                  <h3>Email Settings</h3>

<h4>Mail server details</h4>
<label for="hostField">Host</label>
<input type="text" placeholder="smtp.zoho.com" name="host" id="hostField" v-model="config.host">

<label for="portField">Port</label>
<input type="text" placeholder="587" name="port" id="portField" v-model="config.port">

<label for="encryption_methodField">Encryption Method</label>
<select id="encryption_methodField">
<option value="tls">TLS</option>
<option value="ssl">SSL</option>
</select>

<label for="usernameField">Username</label>
<input type="text" placeholder="" name="username" id="usernameField" v-model="config.username">

<label for="passwordField">Password</label>
<input type="text" placeholder="" name="password" id="passwordField" v-model="config.password">

<h4>Default sender and recipient details</h4>
<label for="to_nameField">To name</label>
<input type="text" placeholder="Mailbox" name="to_name" id="to_nameField" v-model="config.to_name">

<label for="to_addressField">To email address</label>
<input type="text" placeholder="mailbox@mysite.com" name="to_address" id="to_addressField" v-model="config.to_address">

<label for="from_nameField">From name</label>
<input type="text" placeholder="Website" name="from_name" id="from_nameField" v-model="config.from_name">

<label for="from_addressField">From email address</label>
<input type="text" placeholder="mailbox@mysite.com" name="from_address" id="from_addressField" v-model="config.from_address">
                  <div class="row column">
                                <input
                                    class="button-primary"
                                    type="submit"
                                    value="Install"
                                    :disabled="errors.any()"
                                    @click="submit()">
                            </div>
                        </fieldset>
                      </form>
                  </div>
                  </div>`
        },
        'result': {
                props: ['result'],
                template:
                  `<div>
                    <h2>Result</h2>
                    <h3 class="danger" v-if="result.error">Error: {{result.error}}</h3>
                    <p class="pass" v-else><strong>Success!</strong></p>
                    <home_button :result="result"></home_button>
                    <ul><li v-for="item in result.log" :class="item.class">{{item.heading}}</li></ul>
                    <home_button :result="result"></home_button>                    
                  </div>`,
                components: {
                  'home_button': {
                    props: ['result'],
                    methods: {
                      home: function() {
                        window.location = this.result.dashboard;                        
                      }
                    },
                    template: '<button ng-if="!result.error" class="button-primary" @click="home()">View Website</button>'
                  }
                }
        }
      }
    });
  </script>
  </body>
</html>

<?php
