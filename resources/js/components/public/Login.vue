<template>
      <v-container class="fill-height grey lighten-4" fluid>
        <div class="container__center">
            <v-card>
                <div class="primary pa-4 card__top">
                    <img src="/images/favicon.png" class="ma-3" width="30px">
                    <span class="white--text font-weight-bold">SIMTRAP | Login</span>
                </div>

                <v-card-text>                 
                    <v-alert type="error"  v-if="error != ''" :icon="false" dismissible>
                        {{error}}
                    </v-alert>
                    
                    <v-form ref="formLogin">
                        <form action="/login" id="formLogin" method="POST" >
                            <!-- token -->
                            <input type="hidden" name="_token" :value="csrf">

                            <!-- email -->
                            <v-text-field
                                label="Email"
                                name="email"
                                prepend-icon="mdi-account"
                                type="email"
                                required
                                :rules="emailRules"
                                v-on:keyup.enter="submit"
                            />

                            <!-- pass -->
                            <v-text-field
                                id="password"
                                label="Password"
                                name="password"
                                prepend-icon="mdi-lock"
                                type="password"
                                :rules="[v => !!v || 'Senha é obrigatória!']"
                                required
                                v-on:keyup.enter="submit"
                            />

                            <v-btn
                                class="mt-6" 
                                color="primary" 
                                x-large block tile  
                                @click="submit" 
                                :loading="loading">
                                Entrar
                            </v-btn>

                        </form>
                    </v-form>
                </v-card-text>
            </v-card>
        </div>
    </v-container>
</template>

<script>
  export default {
    props: {
      error: String,
    },

    data: () => ({
        csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        emailRules: [
            v => !!v || 'Digite o e-mail',
            v => /.+@.+\..+/.test(v) || 'Este e-mail é inválido',
        ],
        loading: false
    }),

    methods: {
        submit: function () {
            if (this.$refs.formLogin.validate()) {
                this.loading = true
                document.querySelector('#formLogin').submit()
            }
        }
    }
  }
</script>

<style lang="scss" scoped>
.container__center {
    margin: auto; width: 400px;
    .card__top {
        display: flex;
        flex-direction: column;
        align-items: center;
        img, span{
            filter: grayscale(100%) brightness(2) opacity(0.8)
        }
    }
}

</style>