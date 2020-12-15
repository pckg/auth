<template>
    <div class="c-login --advanced flex-tower flex-grid@sm --gap-sm --grid-num --x3@md">

        <div class="--center block">
            <div class="text-center display-block">
                <img class="__logo margin-bottom-sm"
                     src="https://startmaestro.cdn.startcomms.com/storage/uploads/backend/commslogo_3_6_4.png"/>
            </div>
            <pckg-auth-full :ref="'login'"></pckg-auth-full>
        </div>

        <div class="color-grayish text-center --center font-size-xs block">OR</div>

        <div class="flex-grid --gap-sm --center --grid-num">
            <div class="color-grayish text-center --center font-size-xs">Login with OAuth provider</div>

            <a href="/oauth/apple" class="flex-row --start padding-xs-g btn-apple">
                <div class="--center margin-right-xs" style="flex-basis: 3rem;"><i class="fab fa-fw fa-apple"></i>
                </div>
                <div class="--center">Login with <b>Apple</b> account</div>
            </a>

            <a href="/oauth/comms" class="flex-row --start padding-xs-g btn-c">
                <div class="--center margin-right-xs" style="flex-basis: 3rem;">
                    <img src="https://startmaestro.cdn.startcomms.com/storage/uploads/backend/commslogo_3_6_4.png"/>
                </div>
                <div class="--center">Login with <b>Comms ID</b></div>
            </a>

            <a href="/oauth/facebook" class="flex-row --start padding-xs-g btn-fb">
                <div class="--center margin-right-xs" style="flex-basis: 3rem;"><i
                    class="fab fa-fw fa-facebook"></i></div>
                <div class="--center">Login with <b>Facebook</b> account</div>
            </a>

            <a href="/oauth/linkedin" class="flex-row --start padding-xs-g btn-linkedin">
                <div class="--center margin-right-xs" style="flex-basis: 3rem;"><i
                    class="fab fa-fw fa-linkedin"></i></div>
                <div class="--center">Login with <b>LinkedIn</b> account</div>
            </a>

            <a href="/oauth/google" class="flex-row --start padding-xs-g btn-g">
                <div class="--center margin-right-xs" style="flex-basis: 3rem;"><i class="fab fa-fw fa-google"></i>
                </div>
                <div class="--center">Login with <b>Google</b> account</div>
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
export default {}
</script>