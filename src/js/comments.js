Vue.use(VeeValidate, { delay: 1000 });

new Vue({
    el: "#comments",
    data: function() {
        return {
            root: { comments: [] }
        }
    },
    methods: {
        getData: function() {
            let _this = this;
            axios.get(rootPath + '/?class=Comments&method=get_status').then(function(response) {
                _this.root = response.data;
                // This updates the root comments object but only parts of the DOM that need to be updated are updated so it is not an expensive operation.
                // But we are mutating a property of a prop object and this is not in the spirit of one-way data flow.
                // An alternative way may be to add a method to the Vue instance that updates the top level comments object.
            })
            .catch( function(error) {
                console.log(error);
            })
        }
    },
    components: {
        'comment': {
            name: 'comment',
            props: ['comment', 'root'],
            data: function() {
                return {
                    avatar: {
                        width: '60px',
                        height: '60px',
                        color: '#ccc'
                    },
                    icon: {
                        width: '24px',
                        height: '24px',
                        color: '#ccc'
                    },
                    T: { /* Translate this text if you need to internationalize this component's output to the browser */
                        comments: 'comments',
                        up: 'Vote up',
                        down: 'Vote down',
                        add: 'Add your comment',
                        edit: 'Edit your comment',
                        report: 'Report this comment to the moderator',
                        unflag: 'Unflag this comment',
                        delete: 'Delete this comment',
                        approve: 'Approve comment',
                        pending: 'Pending moderation',
                        locked: 'Comments are locked on this post.',
                        disabled: 'Comments are disabled on this post.',
                        readMore: 'Read more',
                        expand: 'Expand comments',
                        collapse: 'Collapse coments',
                        rating: 'Rating by readers',
                        useGravatar: 'Use a Gravatar image',
                        addComment: 'Add your comment',
                        urlOpt: 'Website URL (optional)',
                        submit: 'Submit',
                        minutes: 'minutes',
                        hour: 'hour',
                        day: 'day',
                        month: 'month',
                        year: 'year',
                        ago: 'ago',
                        comment: 'Comment',
                        name: 'Name',
                        email: 'Email'
                    },
                    trimLength: 200,
                    messageTimeoutPeriod: 2000,
                    commentForm: {
                        show: false,
                        action: '',
                        comment: {}
                    },
                    message: {},
                    more: false,
                    showAll: false,
                    showTree: true
                }
            },
            methods: {
                voteUp: function() {
                    this.action({ action: 'plus' });
                },
                voteDown: function() {
                    this.action({ action: 'minus' });
                },
                add: function() {
                    this.commentForm.action = 'add';
                    this.commentForm.comment = {};
                    this.commentForm.show = ! this.commentForm.show; // We can toggle this action in case we clicked on the wrong comment node by mistake
                },
                edit: function() {
                    this.commentForm.action = 'update';
                    this.commentForm.comment = this.comment;
                    this.commentForm.show = ! this.commentForm.show;
                },
                deleteComment: function() { // Using the method name of "delete" is not allowed in Vue since it is a javascript keyword
                    this.action({ action: 'delete' });
                },
                report: function() {
                    this.action({ action: 'flag' });                   
                },
                unflag: function() {
                    this.action({ action: 'unflag' });                   
                },
                approve: function() {
                    this.action({ action: 'approve' });                   
                },
                submit: function(data) {
                    this.commentForm.show = false;
                    this.action({ action: this.commentForm.action, comment: data });
                },
                action: function(args) {
                    let _this = this;
                    args.id = this.comment.id;
                    axios.post(rootPath + '/?class=Comments&method=api', args).then(function(response) {
                        _this.root.comments = response.data.comments;
                    })
                    .catch(function(error) {
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
                    }.bind(this), this.messageTimeoutPeriod);
                },
                getAge: function(t) {
                    var age = '';
                    var now = new Date();
                    var diff = now.getTime() - t;
                    var mins = Math.floor(diff / 60000);
                    if (mins < 60) {
                        age = mins + ' ' + this.T.minutes + ' ' + this.T.ago;
                        return age;
                    }
                    var num = Math.floor(mins / 60);
                    if (num < 24) {
                        age = num + ' ' + this.T.hour;
                    } else {
                        var days = Math.floor(num / 24);
                        if (days < 32) {
                            age = days + ' ' + this.T.day;
                            num = days;
                        } else {
                            num = Math.floor(days / 31);
                            if (num < 12) {
                                age = num + ' ' + this.T.month;
                            } else {
                                num = Math.floor(days / 365);
                                age = num + ' ' + this.T.year;
                            }
                        }
                    }

                    if (num > 1)
                        age += 's';
                    return age + ' ' + this.T.ago;
                }
            },
            components: {
                'comment-form': {
                    name: 'comment-form',
                    props: ['enabled', 'cobject', 'T'],
                    data: function() {
                        return {
                        }
                    },
                    computed: {
                        comment: function() {
                            // Prevent mutation of the input comment object since it would affect the displayed comment as edits are made by the user before they are sanitized by the backend server
                            return Object.assign({}, this.cobject);
                        }
                    },
                    template: `
                    <form v-on:submit.prevent v-if="enabled" class="pure-form pure-form-stacked">
                        <fieldset>
                            <label for="commentField">{{T.comment}}</label>
                            <textarea
                                v-model="comment.content"
                                id="commentField"
                                :placeholder="T.addComment + ' ...'"
                                name="comment"
                                v-validate="'required|min:20'"
                                :class="{'input': true, 'danger': errors.has('comment') }">
                                ></textarea>
                            <div v-show="errors.has('comment')" class="help danger">{{ errors.first('comment') }}</div>

                            <label for="nameField">{{T.name}}</label>
                            <input
                                v-model="comment.author"
                                type="text"
                                id="nameField"
                                name="author"
                                v-validate="'required|min:4'"
                                :class="{'input': true, 'danger': errors.has('author') }">
                            <div v-show="errors.has('author')" class="help danger">{{ errors.first('author') }}</div>

                            <label for="emailField">{{T.email}}</label>
                            <input
                                v-model="comment.email"
                                type="email"
                                id="emailField"
                                name="email"
                                v-validate="'required|email'"
                                :class="{'input': true, 'danger': errors.has('email') }">
                            <div v-show="errors.has('email')" class="help danger">{{ errors.first('email') }}</div>

                            <label for="urlField">{{T.urlOpt}}</label>
                            <input
                                v-model="comment.website"
                                type="url"
                                id="urlField"
                                name="url"
                                v-validate="'url'"
                                :class="{'input': true, 'danger': errors.has('url') }">
                            <div v-show="errors.has('url')" class="help danger">{{ errors.first('url') }}</div>

                            <label for="gravatarField">
                                <input v-model="comment.gravatar" type="checkbox" id="gravatarField"> {{ T.useGravatar }}
                            </label>

                            <input
                                class="pure-button pure-button-primary"
                                type="submit"
                                :value="T.submit"
                                :disabled="errors.any() || comment.email == null || comment.author == null || comment.content == null "
                                @click="submit()">

                        </fieldset>
                    </form>`,
                    methods: {
                        submit: function() {
                            this.$emit('comment', this.comment);
                        }
                    }
                }
            },
            computed: {
                author: function() {
                    if (this.comment.website)
                        return '<a href="' + this.comment.website + '" target="_blank">' + this.comment.author + '</a>';
                    else
                        return this.comment.author;
                },
                when: function() {
                    return this.getAge(this.comment.time);
                },
                deleted: function() {
                    return this.comment.deleted ? '#f00' : this.icon.color;
                },
                flagged: function() {
                    return this.comment.flagged ? '#f00' : this.icon.color;
                },
                visible: function() {
                    return this.comment.id == 0 && this.root.unlocked || this.comment.approved && ! this.comment.deleted || this.root.moderator || this.root.ip == this.comment.ip;
                },
                snippet: function() {
                    var snippet = this.comment.content;
                    if (snippet.length > this.trimLength)
                    {
                        snippet = snippet.substring(0, this.trimLength);
                        let wordBoundary = snippet.lastIndexOf(' ');
                        if (wordBoundary)
                            snippet = snippet.substring(0, wordBoundary);
                        this.more = true;
                    }
                    return snippet;
                }
            },
            // Various SVG graphics are embedded in the template for the icons, this allows us to modify their parameters via our data settings
            template: `
            <div>
                <div v-if="comment.id == 0 && ! this.root.unlocked">
                    <span v-if="root.comments.length">{{T.locked}}</span>
                    <span v-else>{{T.disabled}}</span>
                </div>

                <div class="comment" v-if="visible">
                    <div class="column-10">
                        <img v-if="comment.gravatar" :src="comment.avatar" />
                        <i v-else class="avatar"><svg xmlns="http://www.w3.org/2000/svg" :width="avatar.width" :height="avatar.height" :fill="avatar.color" viewBox="0 0 24 24"><path d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z" /></svg></i>
                    </div>
                    <div class="column">
                        <div v-if="comment.id" class="comment-header">
                            <span class="comment-author" v-html="author"></span> <span class="comment-time">{{when}}</span>
                        </div>
                        <div v-if="comment.id" class="comment-content">
                            <span v-if="!showAll">{{snippet}} </span>
                            <span v-if="showAll">{{comment.content}}</span>
                        </div>
                        <a v-if="more && !showAll" @click.prevent="showAll = true" href="#">{{T.readMore}}</a>
                        <div class="comment-footer">
                            <!-- form icon -->
                            <a 
                                :title="T.add"
                                @click="add()"
                                v-if="root.unlocked && comment.ip != root.ip"
                                class="comment-icon"><svg xmlns="http://www.w3.org/2000/svg" :width="icon.width" :height="icon.height" :fill="icon.color" viewBox="0 0 24 24"><path d="M9,22A1,1 0 0,1 8,21V18H4A2,2 0 0,1 2,16V4C2,2.89 2.9,2 4,2H20A2,2 0 0,1 22,4V16A2,2 0 0,1 20,18H13.9L10.2,21.71C10,21.9 9.75,22 9.5,22V22H9M10,16V19.08L13.08,16H20V4H4V16H10M6,7H18V9H6V7M6,11H15V13H6V11Z" /></svg>
                            </a>

                            <!-- pencil icon -->
                            <a
                                :title="T.edit"
                                @click="edit"
                                v-else
                                class="comment-icon"><svg xmlns="http://www.w3.org/2000/svg" :width="icon.width" :height="icon.height" :fill="icon.color" viewBox="0 0 24 24"><path d="M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.12,5.12L18.87,8.87M3,17.25V21H6.75L17.81,9.93L14.06,6.18L3,17.25Z" /></svg>
                            </a>
                            
                            <template v-if="comment.id">

                                <span class="comment-score" :title="T.rating">{{comment.score}}</span>

                                <!-- up icon -->
                                <a
                                    :title="T.up"
                                    @click="voteUp"
                                    v-if="comment.ip != root.ip"
                                    class="comment-icon"><svg xmlns="http://www.w3.org/2000/svg"  :width="icon.width" :height="icon.height" :fill="icon.color" viewBox="0 0 24 24"><path d="M23,10C23,8.89 22.1,8 21,8H14.68L15.64,3.43C15.66,3.33 15.67,3.22 15.67,3.11C15.67,2.7 15.5,2.32 15.23,2.05L14.17,1L7.59,7.58C7.22,7.95 7,8.45 7,9V19A2,2 0 0,0 9,21H18C18.83,21 19.54,20.5 19.84,19.78L22.86,12.73C22.95,12.5 23,12.26 23,12V10.08L23,10M1,21H5V9H1V21Z" /></svg>
                                </a>

                                <!-- down icon -->
                                <a
                                    :title="T.down"
                                    @click="voteDown"
                                    v-if="comment.ip != root.ip"
                                    class="comment-icon"><svg xmlns="http://www.w3.org/2000/svg"  :width="icon.width" :height="icon.height"
                                    :fill="icon.color"
                                    viewBox="0 -24 24 24"><path transform="scale(1,-1)" d="M23,10C23,8.89 22.1,8 21,8H14.68L15.64,3.43C15.66,3.33 15.67,3.22 15.67,3.11C15.67,2.7 15.5,2.32 15.23,2.05L14.17,1L7.59,7.58C7.22,7.95 7,8.45 7,9V19A2,2 0 0,0 9,21H18C18.83,21 19.54,20.5 19.84,19.78L22.86,12.73C22.95,12.5 23,12.26 23,12V10.08L23,10M1,21H5V9H1V21Z" /></svg>
                                </a>

                                <!-- delete icon -->
                                <a
                                    :title="T.delete"
                                    @click="deleteComment"
                                    v-if="root.moderator || comment.ip == root.ip"
                                    class="comment-icon"><svg xmlns="http://www.w3.org/2000/svg" :width="icon.width" :height="icon.height"
                                    :fill="deleted" viewBox="0 0 24 24"><path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z" /></svg>
                                </a>

                                <!-- alert icon -->
                                <a
                                    :title="T.report"
                                    @click="report"
                                    v-if="comment.ip != root.ip"
                                    class="comment-icon"><svg xmlns="http://www.w3.org/2000/svg" :width="icon.width" :height="icon.height"
                                    :fill="flagged" viewBox="0 0 24 24"><path d="M13,14H11V10H13M13,18H11V16H13M1,21H23L12,2L1,21Z" /></svg>
                                </a>

                                <!-- alert icon -->
                                <a
                                    :title="T.unflag"
                                    @click="unflag"
                                    v-if="comment.flagged && root.moderator"
                                    class="comment-icon"><svg xmlns="http://www.w3.org/2000/svg" :width="icon.width" :height="icon.height"
                                    fill="#090" viewBox="0 0 24 24"><path d="M13,14H11V10H13M13,18H11V16H13M1,21H23L12,2L1,21Z" /></svg>
                                </a>

                                <template v-if=" ! comment.approved">
                                    <!-- check mark icon -->
                                    <a
                                        :title="T.approve"
                                        @click="approve"
                                        v-if="root.moderator"
                                        class="comment-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" :width="icon.width" :height="icon.height"
                                        :fill="icon.color" viewBox="0 0 24 24"><path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z" /></svg>
                                    </a>

                                    <span style="color: #f90">{{T.pending}}</span>
                                </template>
                            </template>
                        </div>

                        <div v-show="message.show" class="help danger">{{ message.content }}</div>

                        <comment-form v-on:comment="submit($event)" :enabled="commentForm.show" :cobject="commentForm.comment" :T="T"></comment-form>

                        <div class="comment-thread-header" v-if="comment.comments && comment.comments.length">
                            <!-- menu down icon -->
                            <a
                                :title="T.collapse"
                                @click="showTree = !showTree"
                                v-if="showTree">
                                <svg xmlns="http://www.w3.org/2000/svg" :width="icon.width" :height="icon.height"
                                :fill="icon.color" viewBox="0 0 24 24"><path d="M7,10L12,15L17,10H7Z" /></svg>
                            </a> 
                            
                            <!-- menu right icon -->
                            <a
                                :title="T.expand"
                                @click="showTree = !showTree"
                                v-if="!showTree">
                                <svg xmlns="http://www.w3.org/2000/svg" :width="icon.width" :height="icon.height"
                                :fill="icon.color" viewBox="0 0 24 24"><path d="M10,17L15,12L10,7V17Z" /></svg>
                            </a>                          
                            {{comment.comments.length}} {{T.comments}}
                        </div>
                        <comment v-if="showTree" v-for="comment in comment.comments" :comment="comment" :root="root"></comment>
                    </div>
                </div>
            </div>`
        }
    },
    mounted: function() {
        this.getData();
    }
})
