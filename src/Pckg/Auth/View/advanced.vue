<template>
    <div class="c-login --advanced flex-tower --gap-sm" :class="!noauth && oauth ? 'flex-grid@sm  --grid-num --x3@md' : ''">

        <div class="--ai-center --jc-center block" v-if="!noauth">
            <div class="text-center block">
                <img class="__logo mb-sm"
                     src="https://startmaestro.cdn.startcomms.com/storage/uploads/backend/commslogo_3_6_4.png"/>
            </div>
            <pckg-auth-full :ref="'login'"></pckg-auth-full>
        </div>

        <div class="color-grayish text-center --ai-center --jc-center font-size-xs block" v-if="!noauth && oauth">OR</div>

        <div class="flex-grid --gap-sm --ai-center --jc-center --grid-num" v-if="oauth">
            <div class="color-grayish text-center --ai-center --jc-center font-size-xs">Login with OAuth provider</div>

            <a href="/oauth/apple" class="flex-row --start p-xs btn-apple" v-if="hasOauth('apple')">
                <div class="--ai-center --jc-center mr-xs" style="flex-basis: 3rem;"><i class="fab fa-fw fa-apple"></i>
                </div>
                <div class="--ai-center --jc-center">Login with <b>Apple</b> account</div>
            </a>

            <a href="/oauth/comms" class="flex-row --start p-xs btn-c" v-if="hasOauth('comms')">
                <div class="--ai-center --jc-center mr-xs" style="flex-basis: 3rem;">
                    <img src="https://startmaestro.cdn.startcomms.com/storage/uploads/backend/commslogo_3_6_4.png"/>
                </div>
                <div class="--ai-center --jc-center">Login with <b>Comms ID</b></div>
            </a>

            <a href="/oauth/facebook" class="flex-row --start p-xs btn-fb" v-if="hasOauth('facebook')">
                <div class="--ai-center --jc-center mr-xs" style="flex-basis: 3rem;"><i
                    class="fab fa-fw fa-facebook"></i></div>
                <div class="--ai-center --jc-center">Login with <b>Facebook</b> account</div>
            </a>

            <a href="/oauth/linkedin" class="flex-row --start p-xs btn-linkedin" v-if="hasOauth('linkedin')">
                <div class="--ai-center --jc-center mr-xs" style="flex-basis: 3rem;"><i
                    class="fab fa-fw fa-linkedin"></i></div>
                <div class="--ai-center --jc-center">Login with <b>LinkedIn</b> account</div>
            </a>

            <a href="/oauth/google" class="flex-row --start p-xs btn-g" v-if="hasOauth('google')">
                <div class="--ai-center --jc-center mr-xs" style="flex-basis: 3rem;"><i class="fab fa-fw fa-google"></i>
                </div>
                <div class="--ai-center --jc-center">Login with <b>Google</b> account</div>
            </a>
        </div>

    </div>
</template>

<style lang="less" scoped>
.btn-fb,
.btn-apple,
.btn-g,
.btn-linkedin,
.btn-c {
    border-radius: 0.25rem;
    color: #fff;
    text-decoration: none;

    i {
        font-size: 1.5em;
    }

    &:hover {
        filter: grayscale(20%);
    }
}

.btn-fb {
    background-color: #3b5998;
}

.btn-g {
    background-color: #4985f4;
}

.btn-apple {
    background-color: #323232;
}

.btn-linkedin {
    background-color: #3d77c9;
}

.btn-c {
    background-color: #0d263d;

    img {
        width: 2.4rem;
        vertical-align: middle;
        margin: 0 .3rem;
    }
}

.__logo {
    max-height: 6.4rem;
    max-width: 6.4rem;
}
</style>

<script>
export default {
    props: {
        oauth: {
            default: true
        },
        noauth: {
            default: false
        }
    },
    methods: {
        hasOauth: function (method) {
            return this.oauth === true || (Array.isArray(this.oauth) && this.oauth.indexOf(method) >= 0);
        }
    }
}
</script>
