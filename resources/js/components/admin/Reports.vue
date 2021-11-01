<template>
    <div>
        <v-row>
            <v-col cols="6">
                <v-card :class="['d-flex align-center pa-5', {'blue-grey lighten-5' : currentReport == report.index}]" 
                    v-for="report in reports" 
                    :key="report.index"
                    elevation="0"
                    @click="currentReport = report.index">
                    <div>
                        <span class="title">{{report.title}}</span>
                        <p class="body-2 mt-1">{{report.text}}</p>
                    </div>
                    <div class="pa-3">
                        <v-icon size="40">mdi-chevron-right</v-icon>
                    </div>
                    <v-divider></v-divider>
                </v-card>
            </v-col>
            <v-col cols="6">

                <v-card v-show="currentReport == 1">
                    <v-card-title>Relatório de Viagens por Paciente</v-card-title>
                    <v-card-text>
                        <v-alert outlined color="blue">
                            Dica! Você também pode gerar este relatório pela lista de pacientes
                        </v-alert>
                            <v-autocomplete
                                v-model="id_paciente"
                                :items="lookupPacientes"
                                :loading="loading.lookupPacientes"
                                :search-input.sync="searchPacientes"
                                item-text="nome"
                                item-value="id"
                                label="Selecione um paciente..."
                                :rules="[v => !!v || 'Obrigatório!']"
                                no-data-text="Digite algo para pesquisar..."
                                @keyup="search"
                                prepend-icon="mdi-account"
                            ></v-autocomplete>
                            <v-btn block color="secondary" dark @click="gerarRelatorioPaciente">Gerar o Relatório</v-btn>
                    </v-card-text>
                </v-card>

                <v-card v-show="currentReport == 2">
                    <v-card-title>Relatório de Viagens</v-card-title>
                    <v-card-text>
                        <v-text-field 
                            v-model="get.start" 
                            label="Data inicial" 
                            type="date" 
                            prepend-icon="mdi-calendar-range"
                        ></v-text-field>
                        
                        <v-text-field 
                            v-model="get.end" 
                            label="Data final" 
                            type="date" 
                            prepend-icon="mdi-calendar-range"
                        ></v-text-field>

                        <v-autocomplete
                            v-model="get.destino"
                            :items="lookupMunicipios"
                            :loading="loading.lookupMunicipios"
                            item-text="nome"
                            item-value="codigo"
                            label="Filtrar por destino"
                            prepend-icon="mdi-map-marker"
                            clearable
                        ></v-autocomplete>

                        <v-autocomplete
                            v-model="get.veiculo"
                            :items="lookupVeiculos"
                            :loading="loading.lookupVeiculos"
                            item-text="descricao"
                            item-value="id"
                            label="Filtrar por veículo"
                            prepend-icon="mdi-car"
                            clearable
                            >
                        </v-autocomplete>

                        <v-autocomplete
                            v-model="get.motorista"
                            :items="lookupMotoristas"
                            :loading="loading.lookupMotoristas"
                            item-text="nome"
                            item-value="id"
                            label="Filtrar por motorista"
                            prepend-icon="mdi-account-tie"
                            clearable
                        ></v-autocomplete>
                        
                        <v-select
                            :items="orderby"
                            v-model="get.order"
                            label="Ordenar por:"
                            prepend-icon="mdi-sort-ascending"
                        ></v-select>

                        <v-btn block color="secondary" @click="gerarRelatorioViagens" dark>Gerar o Relatório</v-btn>
                    </v-card-text>
                </v-card>

                <v-card v-show="currentReport == 3">
                    <v-card-title>Lista de Viagem</v-card-title>
                    <v-card-text>
                        <v-alert outlined color="blue">
                            Para gerar este relatório, utilize o menu "Viagens" e clique no ícone da impressora da viagem que deseja exportar. 
                            Você também pode exportar esse relatório diretamente pela própria tela de adição de passageiros
                        </v-alert>
                        <v-btn block color="secondary" dark href="./viagens">Vamos lá</v-btn>
                    </v-card-text>
                </v-card>

                <v-card v-show="currentReport == 4">
                    <v-card-title>Absenteísmo (Faltas)</v-card-title>
                    <v-card-text>
                        <v-select
                            :items="absenteismo.options.year"
                            v-model="absenteismo.year"
                            label="Ano"
                            prepend-icon="mdi-calendar-range"
                        ></v-select>

                        <v-select
                            :items="absenteismo.options.month"
                            v-model="absenteismo.month"
                            label="Mês"
                            prepend-icon="mdi-calendar-range"
                        ></v-select>
                        
                        <v-select
                            :items="absenteismo.options.order"
                            v-model="absenteismo.order"
                            label="Ordenar por:"
                            prepend-icon="mdi-sort-ascending"
                        ></v-select>

                        <v-btn block color="secondary" @click="gerarRelatorioFaltas" dark>Gerar o Relatório</v-btn>
                    </v-card-text>
                </v-card>

            </v-col>
        </v-row>

        <lookup ref="lookupComponent" v-on:updateLookup="updateLookup"></lookup>
    </div>
</template>
<script>
export default {
    data: () => ({

        reports: [
            {
                index: 1,
                title: 'Relatório de Viagens por Paciente',
                text: 'Selecione um paciente e exporte uma lista de todas as viagens em que o mesmo já foi transportado, contendo data da viagem, destino, veículo utilizado, local, observação e hora da consulta',
            },
            {
                index: 2,
                title: 'Relatório de Viagens',
                text: 'Neste relatório você poderá exportar uma lista de viagens realizadas entre um determinado período, podendo ainda filtrar por motorista, veículo, destino e ordenar o resultado'
            },
            {
                index: 4,
                title: 'Absenteísmo (Faltas)',
                text: 'Extraia uma lista de todos os pacientes que faltaram a alguma viagem. Este relatório inlcui dados do paciente e da viagem também'
            },
            {
                index: 3,
                title: 'Lista de Viagem',
                text: 'Este é o relatório contém no seu cabeçalho dados básicos sobre a viagem e a relação de passageiros cadastrados, bem como acompanhantes, observações e campo para poder marcar a chamada dos passageiros'
            },
        ],
        currentReport: 1,

        lookupPacientes: [],
        lookupMunicipios: [],
        lookupVeiculos: [],
        lookupMotoristas: [],

        loading: {
            lookupPacientes: false,
            lookupMunicipios: false,
            lookupVeiculos: false,
            lookupMotoristas: false
        },

        orderby: [
            { text: 'ID', value: 'id'},
            { text: 'Data', value: 'data_viagem'},
            { text: 'Veículo', value: 'veiculo_nome'},
            { text: 'Motorista', value: 'motorista_nome'},
            { text: 'Destino', value: 'municipio_nome'},
        ],

        absenteismo: {
            options: {
                order: [
                    { text: 'ID', value: 'id'},
                    { text: 'Data', value: 'data_viagem'},
                    { text: 'Veículo', value: 'veiculo_nome'},
                    { text: 'Motorista', value: 'motorista_nome'},
                    { text: 'Município', value: 'municipio_nome'},
                    { text: 'Paciente', value: 'municipio_nome'},
                ],
                year: [
                    { text: '2019', value: 2019},
                    { text: '2020', value: 2020},
                ],
                month: [
                    { text: 'Janeiro', value: 1},
                    { text: 'Fevereiro', value: 2},
                    { text: 'Março', value: 3},
                    { text: 'Abril', value: 4},
                    { text: 'Maio', value: 5},
                    { text: 'Junho', value: 6},
                    { text: 'Julho', value: 7},
                    { text: 'Agosto', value: 8},
                    { text: 'Setembro', value: 9},
                    { text: 'Outubro', value: 10},
                    { text: 'Novembro', value: 11},
                    { text: 'Dezembro', value: 12},
                ]
            },
            order: 'id',
            year: 2020,
            month: 1
        },

        searchPacientes: null,
        timeoutId: null,
        id_paciente: null,

        get: {
            start: null,
            end: null,
            destino: null,
            veiculo: null,
            motorista: null,
            order: 'id'
        }
    }),

    watch: {
        currentReport: function (val) {
            if (this.lookupMunicipios.length > 0 || val != 2) return;
            this.getStaticLookups();
        }
    },

    methods: {

        // relatorio paciente
        gerarRelatorioPaciente () {
            if (this.id_paciente < 1) {
                this.$toast.warning('Por favor, selecione o paciente para o qual deseja gerar o relatório');
                return;
            }
            this.$openPopup('/relatorios/paciente/'+this.id_paciente, 'paciente')
        },

        // relatorio viagens
        gerarRelatorioViagens () {
            let params = '';

            params+= '&start='      +this.sanitize(this.get.start)
            params+= '&end='        +this.sanitize(this.get.end)
            params+= '&veiculo='    +this.sanitize(this.get.veiculo)
            params+= '&motorista='  +this.sanitize(this.get.motorista)
            params+= '&destino='    +this.sanitize(this.get.destino)
            params+= '&order='      +this.get.order

            this.$openPopup('/relatorios/viagens/'+encodeURIComponent(params))
        },

        // relatório absenteísmo
        gerarRelatorioFaltas () {
            let params = '';

            params+= '&month='  +this.sanitize(this.absenteismo.month)
            params+= '&year='   +this.sanitize(this.absenteismo.year)
            params+= '&order='  +this.absenteismo.order

            this.$openPopup('/relatorios/faltas/'+encodeURIComponent(params))
        },

        sanitize (val) {
            return typeof val == 'undefined' || val == '' ? null : val
        },

        // lookups relatorio viagens 
        getStaticLookups () {
            this.loading.lookupVeiculos = true
            this.$refs.lookupComponent.getLookup('VEICULO');
            this.loading.lookupMunicipios = true
            this.$refs.lookupComponent.getLookup('MOTORISTA');
            this.loading.lookupMunicipios = true
            this.$refs.lookupComponent.getLookup('MUNICIPIO');
        },

        // callback lookups
        updateLookup (datasetName, dataArray) {
            if (datasetName == 'PACIENTE') {
                this.lookupPacientes = dataArray;
                this.loading.lookupPacientes = false;
            }
            if (datasetName == 'VEICULO') {
                this.lookupVeiculos = dataArray
                this.loading.lookupVeiculos = false
            }
            if (datasetName == 'MOTORISTA') {
                this.lookupMotoristas = dataArray
                this.loading.lookupMotoristas = false
            }
            if (datasetName == 'MUNICIPIO') {
                this.lookupMunicipios = dataArray
                this.loading.lookupMunicipios = false
            }
        },

        // lookup pacientes
        search () {
            if (this.searchPacientes == null || this.searchPacientes == '') return
            this._debounce();
        },
        _debounce(){
            this.loading.lookupPacientes = true;
            if (this.timeoutId !== null) {
                clearTimeout(this.timeoutId);
            }
            this.timeoutId = setTimeout( _ => {
                this.getPacientesLookup();
            }, this.$debounceTime);
        },
        getPacientesLookup: function () {
            this.$refs.lookupComponent.getLookup('PACIENTE', this.searchPacientes);
        },
    }
}
</script>
<style lang="scss" scoped>
p {
    color: rgba(0,0,0,.7)
}
</style>