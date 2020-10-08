<template>
    <form class="pckg-auth-full" v-if="typeof error !== 'undefined'">

        <div class="display-block bold text-center margin-bottom-sm">
            <template v-if="myStep === 'login'">Login</template>
            <template v-else-if="myStep === 'forgottenPassword'">Forgotten password</template>
            <template v-else-if="myStep === 'passwordSent'">Password sent</template>
            <template v-else-if="myStep === 'resetPassword'">Reset password</template>
            <template v-else-if="myStep === 'signup'">Signup</template>
        </div>

        <div class="form-group"
             v-if="['login', 'forgottenPassword', 'passwordSent', 'resetPassword', 'signup'].indexOf(myStep) >= 0">
            <label>Email</label>
            <div v-if="['passwordSent', 'resetPassword'].indexOf(myStep) == -1">
                <input type="email" v-model="emailModel" name="email" @keyup.enter="executeAction"
                       autocomplete="username" class="form-control" placeholder="your@email.com" />

                <htmlbuilder-validator-error :bag="errors" name="email"></htmlbuilder-validator-error>
            </div>
            <div v-else>
                {{ emailModel }}
            </div>
        </div>

        <div class="form-group" v-if="['passwordSent'].indexOf(myStep) >= 0">
            <label>Security code</label>
            <div>
                <input type="text" v-model="code" name="code" @keyup.enter="executeAction" autocomplete="off"
                       class="form-control" />

                <htmlbuilder-validator-error :bag="errors" name="code"></htmlbuilder-validator-error>
            </div>
        </div>

        <div class="form-group" v-if="['login'].indexOf(myStep) >= 0">
            <label>Password</label>
            <a class="as-link font-size-xs pull-right __help-label-link" href="#" tabindex="-1" v-if="myStep == 'login'"
               @click.prevent="setStep('forgottenPassword')">Forgotten password?</a>
            <div>
                <input type="password" v-model="password" name="password" @keyup.enter="executeAction"
                       autocomplete="current-password" class="form-control" />

                <htmlbuilder-validator-error :bag="errors" name="password"></htmlbuilder-validator-error>
            </div>
        </div>

        <div class="form-group" v-if="['resetPassword', 'signup'].indexOf(myStep) >= 0">
            <label>New password</label>
            <div>
                <input type="password" v-model="password" @keyup.enter="executeAction" autocomplete="new-password"
                       class="form-control" />

                <htmlbuilder-validator-error :bag="errors" name="password"></htmlbuilder-validator-error>
            </div>
        </div>

        <div class="form-group" v-if="['resetPassword', 'signup'].indexOf(myStep) >= 0">
            <label>Repeat password</label>
            <div>
                <input type="password" v-model="passwordRepeat" @keyup.enter="executeAction"
                       autocomplete="new-password" class="form-control" />

                <htmlbuilder-validator-error :bag="errors" name="passwordRepeat"></htmlbuilder-validator-error>
            </div>
        </div>

        <div class="alert alert-danger clear-both" v-if="error.length > 0">{{ error }}</div>

        <button class="btn btn-block btn-primary" @click.prevent="executeAction" :key="'btnAction'" :disabled="loading">
            <template v-if="loading"><i class="fa fa-spinner fa-spin"></i></template>
            <template v-else>{{ stepBtnText }}</template>
        </button>

        <div class="text-center margin-top-md margin-bottom-sm">
            <a class="as-link font-size-xs" href="#" v-if="!disable.signup && myStep == 'login'"
               @click.prevent="setStep('signup')">New user?</a>
            <a class="as-link font-size-xs" href="#" v-if="['forgottenPassword', 'signup'].indexOf(myStep) >= 0"
               @click.prevent="setStep('login')">Existing user?</a>
        </div>
    </form>
</template>

<script src="./basic.js"></script>