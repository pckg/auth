<template>
    <div class="pckg-auth-full">
        <div v-html="__('auth.' + myStep + '.intro')" v-if="myStep != 'login' || (email && email.length > 0)"></div>

        <div class="form-group"
             v-if="['login', 'forgottenPassword', 'passwordSent', 'resetPassword'].indexOf(myStep) >= 0">
            <label>{{ __('auth.label.email') }}</label>
            <div v-if="['passwordSent', 'resetPassword'].indexOf(myStep) == -1">
                <input type="email" v-model="emailModel" name="email" @keyup.enter="executeAction" />

                <htmlbuilder-validator-error :bag="errors" name="email"></htmlbuilder-validator-error>
            </div>
            <div v-else>
                {{ emailModel }}
            </div>
        </div>

        <div class="form-group" v-if="['passwordSent'].indexOf(myStep) >= 0">
            <label>{{ __('auth.label.securityCode') }}</label>
            <div>
                <input type="text" v-model="code" name="code" @keyup.enter="executeAction" />

                <htmlbuilder-validator-error :bag="errors" name="code"></htmlbuilder-validator-error>
            </div>
        </div>

        <div class="form-group" v-if="['login'].indexOf(myStep) >= 0">
            <label>{{ __('auth.label.password') }}</label>
            <a class="as-link font-size-xs pull-right" href="#" tabindex="-1" v-if="myStep == 'login'"
               @click.prevent="myStep = 'forgottenPassword'">{{ __('auth.forgottenPassword.question') }}</a>
            <div>
                <input type="password" v-model="password" name="password" @keyup.enter="executeAction"/>

                <htmlbuilder-validator-error :bag="errors" name="password"></htmlbuilder-validator-error>
            </div>
        </div>

        <div class="form-group" v-if="['resetPassword'].indexOf(myStep) >= 0">
            <label>{{ __('auth.label.newPassword') }}</label>
            <div>
                <input type="password" v-model="password" @keyup.enter="executeAction" />
                <div class="help">{{ __('auth.help.newPassword') }}</div>

                <htmlbuilder-validator-error :bag="errors" name="password"></htmlbuilder-validator-error>
            </div>
        </div>

        <div class="form-group" v-if="['resetPassword'].indexOf(myStep) >= 0">
            <label>{{ __('auth.label.repeatPassword') }}</label>
            <div>
                <input type="password" v-model="passwordRepeat" @keyup.enter="executeAction" />

                <htmlbuilder-validator-error :bag="errors" name="passwordRepeat"></htmlbuilder-validator-error>
            </div>
        </div>

        <!--<div class="form-group" v-if="['login'].indexOf(myStep) >= 0">
            <label>{{ __('auth.label.rememberMe') }}</label>
            <pckg-tooltip icon="question-circle"
                          :content="__('auth.help.rememberMe')"></pckg-tooltip>
            <div>
                <d-input-checkbox v-model="rememberMe" :value="1"></d-input-checkbox>
            </div>
        </div>-->

        <div class="alert alert-danger clear-both" v-if="error.length > 0">{{ error }}</div>

        <button class="button btn-block" @click.prevent="executeAction" :key="'btnAction'" :disabled="loading">
            <template v-if="loading"><i class="fa fa-spinner fa-spin"></i></template>
            <template v-else>{{ __('auth.' + myStep + '.btn') }}</template>
        </button>

        <div class="centered margin-top-md margin-bottom-sm">
            <a class="as-link" href="#" v-if="myStep == 'forgottenPassword'"
               @click.prevent="myStep = 'login'" :key="'btnBelowForgottenPassword'">{{ __('auth.login.question')
                }}</a>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'pckg-auth-full',
        mixins: [pckgFormValidator, pckgTranslations],
        props: {
            email: {},
            step: {
                type: String,
                default: 'login'
            }
        },
        data: function () {
            return {
                emailModel: this.email || '',
                password: '',
                passwordRepeat: '',
                // rememberMe: null,
                code: '',
                myStep: this.step,
                error: '',
                loading: false,
                existingUser: false,
            };
        },
        created: function () {
            let hash = document.URL.substring(document.URL.lastIndexOf("#") + 1);

            if (hash == 'loginModal') {
                if (this.isLoggedIn) {
                    http.redirect('/profile');
                    return;
                }

                this.$emit('opened');
                return;
            }
        },
        mounted: function () {
            let hash = document.URL.substring(document.URL.lastIndexOf("#") + 1);

            if (hash.indexOf('passwordSent') === 0) {
                let parts = hash.split('-');

                this.$emit('opened');
                this.myStep = 'passwordSent';
                this.emailModel = parts[1];
                this.code = parts[2];
                this.executeAction();
            }
            $dispatcher.$on('auth:login', this.openLoginModal);
            $dispatcher.$on('auth:forgotenPassword', this.openForgottenPasswordModal);
        },
        beforeDestroy: function () {
            $dispatcher.$off('auth:login', this.openLoginModal);
            $dispatcher.$off('auth:forgotenPassword', this.openForgottenPasswordModal);
        },
        watch: {
            emailModel: function () {
                this.existingUser = false;
            },
            step: function (myStep) {
                this.myStep = step;
                this.error = '';
            }
        },
        methods: {
            executeAction: function () {
                this.error = '';
                if (this.myStep == 'login') {
                    this.loading = true;
                    http.post('/login', {
                        email: this.emailModel,
                        password: this.password,
                        // autologin: this.rememberMe
                    }, function (data) {
                        this.loading = false;
                        if (data.success) {
                            $dispatcher.$emit('auth:user:loggedIn');

                            if (data.redirect && window.location.pathname.indexOf('/basket') === -1) {
                                http.redirect(data.redirect);
                                return;
                            }

                            this.$emit('close');
                            this.visible = false;
                            return;
                        }

                        this.errors.clear();
                        this.error = data.text || 'Unknown error';
                    }.bind(this), function (response) {
                        this.loading = false;
                        http.postError(response);

                        this.errors.clear();
                        $.each(response.responseJSON.descriptions || [], function (name, message) {
                            this.errors.remove(name);
                            this.errors.add({field: name, msg: message});
                        }.bind(this));
                    }.bind(this));
                }
                if (this.myStep == 'signup') {
                    this.myStep = 'forgottenPassword';
                    return;
                    http.post('/signup', {}, function (data) {

                    }.bind(this), function () {

                    });
                }
                if (this.myStep == 'forgottenPassword') {
                    this.loading = true;
                    http.post('/forgot-password', {
                        email: this.emailModel
                    }, function (data) {
                        this.loading = false;
                        if (data.success) {
                            this.myStep = 'passwordSent';
                            return;
                        }
                    }.bind(this), function (response) {
                        this.loading = false;
                        http.postError(response);

                        this.errors.clear();
                        $.each(response.responseJSON.descriptions || [], function (name, message) {
                            this.errors.remove(name);
                            this.errors.add({field: name, msg: message});
                        }.bind(this));
                    }.bind(this));
                }
                if (this.myStep == 'passwordSent') {
                    this.loading = true;
                    http.post('/password-code', {
                            email: this.emailModel,
                            code: this.code
                        }, function (data) {
                            this.loading = false;
                            if (data.success) {
                                this.myStep = 'resetPassword';
                                return;
                            }
                        }.bind(this),
                        function (response) {
                            this.loading = false;
                            http.postError(response);

                            this.errors.clear();
                            $.each(response.responseJSON.descriptions || [], function (name, message) {
                                this.errors.remove(name);
                                this.errors.add({field: name, msg: message});
                            }.bind(this));
                        }.bind(this));
                }
                if (this.myStep == 'resetPassword') {
                    this.loading = true;
                    http.post('/reset-password', {
                            code: this.code,
                            email: this.emailModel,
                            password: this.password,
                            passwordRepeat: this.passwordRepeat
                        }, function (data) {
                            this.loading = false;
                            if (!data.success) {
                                return;
                            }

                            $dispatcher.$emit('auth:user:loggedIn');
                            // $dispatcher.$emit('notification:success', 'Successfully logged in');
                            this.$emit('close');
                            this.visible = false;

                            if (window.location.pathname.indexOf('/basket') < 0) {
                                http.redirect();
                                return;
                            }
                        }.bind(this),
                        function (response) {
                            this.loading = false;
                            http.postError(response);

                            this.errors.clear();
                            $.each(response.responseJSON.descriptions || [], function (name, message) {
                                this.errors.remove(name);
                                this.errors.add({field: name, msg: message});
                            }.bind(this));
                        }.bind(this));
                }
            },
            openLoginModal: function () {
                this.myStep = 'login';
                this.$emit('opened');
            },
            openForgottenPasswordModal: function () {
                this.myStep = 'forgottenPassword';
                this.$emit('opened');
            }
        },
        computed: {
            isLoggedIn: function () {
                return $store.getters.isLoggedIn;
            }
        }
    }
</script>