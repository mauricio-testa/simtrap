<template>
      <v-container class="fill-height grey lighten-4" fluid>
        <div class="container__center">
            <v-card>

                <!-- header -->
                <div class="primary pa-4 card__top" v-if="step == 'NAO_AUTENTICADO' || step == 'AUTENTICADO'">
                    <img src="/images/favicon.png" class="ma-3" width="30px">
                    <span class="white--text font-weight-bold">SIMTRAP | Chamada</span>
                </div>

                <!-- já foi lançado -->
                <v-card-text v-if="step == 'LANCADO_ANTERIORMENTE'" class="d-flex flex-column align-center">
                    <v-icon color="warning" size="60">mdi-alert-circle-outline</v-icon>
                    <span class="title mt-2 text-center">A lista de presença desta viagem já foi preenchida.</span>
                    <v-btn color="primary" class="my-4" small @click="fillAgain">Lançar novamente</v-btn>
                </v-card-text>

                <!-- já  -->
                <v-card-text v-if="step == 'NAO_EXPORTADA'" class="d-flex flex-column align-center">
                    <v-icon color="warning" size="60">mdi-alert-circle-outline</v-icon>
                    <span class="title mt-2 text-center">Esta lista ainda não foi exportada. Por favor exporte e depois preencha a lista de presença.</span>
                </v-card-text>

                <!-- a autenticar -->
                <v-card-text v-else-if="step == 'NAO_AUTENTICADO'">
                    <v-alert type="error" v-if="authError != null" :icon="false" dismissible>
                        {{authError}}
                    </v-alert>
                    <v-form ref="formLogin">
                        <form>
                            <input type="hidden" name="_token" :value="csrf">
                            <v-text-field
                                label="Chave de Acesso"
                                prepend-icon="mdi-account"
                                type="number"
                                v-model="access_key"
                                required
                            />
                            <v-btn
                                class="mt-6" 
                                color="primary" 
                                x-large block tile  
                                @click="authenticate" 
                                :loading="loading">
                                Enviar
                            </v-btn>
                        </form>
                    </v-form>
                </v-card-text>

                <!-- viagem vazia -->
                <v-card-text v-else-if="step == 'AUTENTICADO' && lista.length == 0">
                    <v-alert outlined type="warning" :icon="false">Esta viagem não tem nenhum paciente cadastrado</v-alert>
                </v-card-text>

                <!-- chamada -->
                <v-card-text v-else-if="step == 'AUTENTICADO'">
                    <v-alert outlined type="info" :icon="false">Marque os pacientes que compareceram na viagem</v-alert>
                    <v-data-table 
                        :mobile-breakpoint="10"
                        v-model="selected"
                        :headers="headers"
                        :items="lista"
                        item-key="id_paciente"
                        show-select
                        hide-default-footer
                    >
                    </v-data-table>
                    <v-btn
                        class="mt-6" 
                        color="primary" 
                        x-large block tile  
                        @click="submitChamada" 
                        :loading="loading">
                        Enviar
                    </v-btn>
                </v-card-text>

                <!-- concluido -->
                <v-card-text v-else-if="step == 'CONCLUIDO'" class="d-flex flex-column align-center">
                    <v-icon color="success" size="60">mdi-checkbox-marked-circle-outline</v-icon>
                    <span class="title mt-2 text-center">Obrigado</span>
                </v-card-text>
                
            </v-card>
        </div>
    </v-container>
</template>

<script>
  export default {
    props: {
      lista: Array,
      viagem: Object,
      authenticated: Number
    },

    data: () => ({
        csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),

        selected: [],
        step: 'NAO_AUTENTICADO', //'LANCADO_ANTERIORMENTE', 'NAO_AUTENTICADO', 'AUTENTICADO', 'CONCLUIDO'
        access_key: null,
        authError: null,
        loading: false,
        headers: [
          { text: 'Nome', value: 'paciente_nome' },
        ],
    }),

    mounted () {
        // se já foi lançado
        if (this.viagem.status == 'CONCLUIDA') {
            this.step = 'LANCADO_ANTERIORMENTE';
        }

        if (this.viagem.status == 'NOVA') {
            this.step = 'NAO_EXPORTADA';
        }

        // se já está autenticado não pede chave de acesso
        else if (this.authenticated) {
            this.step = 'AUTENTICADO'
        }
    },

    methods: {

        fillAgain () {
            if (this.authenticated) {
                this.step = 'AUTENTICADO'
            } else {
                this.step = 'NAO_AUTENTICADO'
            }
        },
         
        authenticate () {
            let vm = this;
            vm.loading = true;
            axios
                .post('/chamada/authenticate/'+vm.viagem.id, {access_key: vm.access_key})
                .then(function(response){
                    if (response.data.success == 'true') {
                        vm.step = 'AUTENTICADO';
                    } else {
                        vm.authError = response.data.message
                    }
                })
                .finally(() => {
                    vm.loading = false;
                });
        },

        submitChamada () {
            let vm = this;
            vm.loading = true;
            axios
                .post(window.location.href, {lista: JSON.stringify(vm.selected)})
                .then(function(response){
                    vm.step = 'CONCLUIDO';
                })
                .finally(() => {
                    vm.loading = false;
                });
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