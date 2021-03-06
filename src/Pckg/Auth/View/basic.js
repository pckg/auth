import {pckgFormValidator} from "../../../../../htmlbuilder/src/Pckg/Htmlbuilder/public/pckgFormValidator.js";

const Basic = {
    name: 'pckg-auth-full',
    mixins: [pckgFormValidator],
    props: {
        email: {},
        step: {
            type: String,
            default: 'login'
        },
        options: {
            default: function () {
                return {
                    disable: {}
                };
            }
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
            disable: this.options.disable || {}
        };
    },
    mounted: function () {
        let hash = document.URL.substring(document.URL.lastIndexOf("#") + 1);

        if (hash.indexOf('passwordSent') === 0) {
            let parts = hash.split('-');

            this.emailModel = parts[1];
            this.code = parts[2];
            this.executeAction();
        }
    },
    watch: {
        emailModel: function () {
            this.existingUser = false;
        },
        step: function (step) {
            this.myStep = step;
            this.error = '';
        }
    },
    methods: {
        executeAction: function () {
            this.error = '';
            if (this.myStep == 'login') {
                this.loading = true;
                http.post(
                    '/login', {
                        email: this.emailModel,
                        password: this.password,
                        // autologin: this.rememberMe
                    }, function (data) {
                        this.loading = false;
                        if (data.success) {
                            $dispatcher.$emit('auth:user:loggedIn');

                            let url = new URL(window.location.href);
                            let loginredirect = url.searchParams.get('loginredirect');

                            if (loginredirect) {
                                http.redirect(loginredirect);
                                return;
                            } else if (data.redirect && window.location.pathname.indexOf('/basket') === -1) {
                                http.redirect(data.redirect);
                                return;
                            }

                            this.$emit('close');
                            this.visible = false;
                            return;
                        }

                        this.errors.clear();

                        if (data.type === 'activateAccount') {
                            this.setStep('activateAccount');
                            return;
                        }

                        this.error = data.text || 'Unknown error';
                    }.bind(this), function (response) {
                        this.loading = false;
                        http.postError(response);

                        this.errors.clear();
                        $.each(
                            response.responseJSON.descriptions || [], function (name, message) {
                                this.errors.remove(name);
                                this.errors.add({field: name, msg: message});
                            }.bind(this)
                        );
                    }.bind(this)
                );
            }
            if (this.myStep == 'signup') {
                this.loading = true;
                http.post(
                    '/api/auth/signup', {
                        email: this.emailModel,
                        password: this.password,
                        passwordRepeat: this.passwordRepeat,
                    }, function (data) {
                        this.loading = false;
                        if (data.success) {
                            this.setStep('accountCreated');
                            return;
                        }
                    }.bind(this), function (response) {
                        this.loading = false;
                        this.hydrateErrorResponse(response);
                    }.bind(this)
                );
            }
            if (this.myStep === 'accountCreated') {
                this.setStep('login');
            }
            if (['forgottenPassword', 'activateAccount'].indexOf(this.myStep) >= 0) {
                this.loading = true;
                http.post(
                    '/forgot-password', {
                        email: this.emailModel
                    }, function (data) {
                        this.loading = false;
                        if (data.success) {
                            this.setStep('passwordSent');
                            return;
                        }
                    }.bind(this), function (response) {
                        this.loading = false;
                        this.hydrateErrorResponse(response);
                    }.bind(this)
                );
            }
            if (this.myStep == 'passwordSent') {
                this.loading = true;
                http.post(
                    '/password-code', {
                        email: this.emailModel,
                        code: this.code
                    }, function (data) {
                        this.loading = false;
                        if (data.success) {
                            this.setStep('resetPassword');
                            return;
                        }
                    }.bind(this),
                    function (response) {
                        this.loading = false;
                        this.hydrateErrorResponse(response);
                    }.bind(this)
                );
            }
            if (this.myStep == 'resetPassword') {
                this.loading = true;
                http.post(
                    '/reset-password', {
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
                        this.hydrateErrorResponse(response);
                    }.bind(this)
                );
            }
        },
        setStep: function (step) {
            this.myStep = step;
            this.$emit('steps', step);
        }
    },
    computed: {
        isLoggedIn: function () {
            return this.$store.getters.isLoggedIn;
        },
        stepBtnText: function () {
            return {
                login: 'Login',
                forgottenPassword: 'Send security code',
                passwordSent: 'Confirm security code',
                resetPassword: 'Set new password',
                activateAccount: 'Send security code'
            }[this.myStep] || utils.ucfirst(this.myStep);
        }
    }
};

export default Basic;