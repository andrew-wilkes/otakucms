Vue.use(VeeValidate, { delay: 1000 });

new Vue({
    el: "#contact",
    data: function() {
        return {
        }
    },
    components: {
        'contact': {
            data: function() {
                return {
                    T: {
                        message: 'Message',
                        addMessage: 'Add your message',
                        name: 'Name',
                        email: 'Email',
                        submit: 'Send'
                    },
                    messageTimeoutPeriod: 2000,
                    enabled: true,
                    message: '',
                    contact: {}
                }
            },
            methods: {
                send: function() {
                    let _this = this;
                    axios.post(rootPath + '/?class=Contact', this.contact).then(function(response) {
                        _this.enabled = false;
                        _this.displayMessage(response.data);
                    })
                    .catch( function(error) {
                        _this.displayMessage(error.response.statusText);
                    })
                },
                displayMessage: function(txt) {
                    this.message = {
                        show: true,
                        content: txt
                    }
                    setTimeout(function() {
                        this.message = {};
                        window.location = './';
                    }.bind(this), this.messageTimeoutPeriod);
                }
            },
            template: `
            <div>
            <form v-on:submit.prevent v-if="enabled" class="pure-form">
                <fieldset>
                    <legend>{{T.message}}</legend>
                    <textarea
                        v-model="contact.message"
                        id="contactField"
                        :placeholder="T.addMessage + ' ...'"
                        name="message"
                        v-validate="'required|min:20'"
                        :class="{'input': true, 'danger': errors.has('message') }"
                        style="width:250px; height: 150px;">
                    </textarea>
                    <span v-show="errors.has('message')" class="help danger">{{ errors.first('message') }}</span>
                </fieldset>
                <fieldset>
                    <legend>{{T.name}}</legend>
                    <input
                        v-model="contact.author"
                        type="text"
                        id="nameField"
                        name="author"
                        v-validate="'required|min:4'"
                        :class="{'input': true, 'danger': errors.has('author') }">
                    <span v-show="errors.has('author')" class="help danger">{{ errors.first('author') }}</span>
                </fieldset>
                <fieldset>
                    <legend>{{T.email}}</legend>
                    <input
                        v-model="contact.email"
                        type="email"
                        id="emailField"
                        name="email"
                        v-validate="'required|email'"
                        :class="{'input': true, 'danger': errors.has('email') }">
                    <span v-show="errors.has('email')" class="help danger">{{ errors.first('email') }}</span>
                </fieldset>
                <fieldset>
                    <input
                        class="pure-button pure-button-primary"
                        type="submit"
                        :value="T.submit"
                        :disabled="errors.any() || contact.email == null || contact.author == null || contact.message == null "
                        @click="send()">
                </fieldset>
            </form>
            <h3 v-show="message.show">{{ message.content }}</h3>
            </div>`
        }
    }
});