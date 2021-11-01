<template>
<div>

    <!-- dialog list -->
    <v-dialog v-model="dialogList" max-width="720px" @click:outside="close">
        <v-card>
            <v-card-title>
                <v-row>
                <v-col cols="12" md="8">

                    Lista de Passageiros

                    <!-- @RULE: desabilita botão adicionar se o veículo está lotado -->
                    <v-tooltip right v-if="vagasDisponiveis == 0">
                        <template v-slot:activator="{ on }">
                            <div v-on="on" style="display: inline-block">
                                <v-btn class="ml-4" disabled color="primary" fab small ><v-icon>mdi-plus</v-icon></v-btn>
                            </div>
                        </template>
                        <span>Você não pode adicionar novos passageiros a esta viagem <br>porque o número de passageiros é igual a lotação do veículo</span>
                    </v-tooltip>

                    <v-btn v-else class="ml-4" fab dark small color="primary" @click="addNewPassageiro">
                        <v-icon dark>mdi-plus</v-icon>
                    </v-btn>

                </v-col>
                </v-row>                        
            </v-card-title>
            <v-card-text>
                <div class="d-flex mt-2 mb-8 pr-8">
                    <v-chip class="mr-2" color="indigo darken-3" outlined>
                        <v-icon left>mdi-calendar-range</v-icon>
                        {{viagem.data_formated}}
                    </v-chip>
                    <v-chip class="mr-2" color="deep-purple accent-4" outlined :title="viagem.municipio_nome">
                        <v-icon left>mdi-map-marker</v-icon>
                        {{viagem.municipio_nome | substring}}
                    </v-chip>
                    <v-chip class="mr-2" color="primary" outlined :title="viagem.veiculo_nome">
                        <v-icon left>mdi-car</v-icon>
                        {{viagem.veiculo_nome | substring}} 
                    </v-chip>                    
                    <v-chip v-if="vagasDisponiveis > 0" class="mr-2" color="success" outlined>
                        <v-icon left>mdi-account-group</v-icon>
                        {{ vagasDisponiveis }} vagas
                    </v-chip>
                    <v-chip v-else class="mr-2" color="error" dark>
                        <v-icon left>mdi-alert-circle</v-icon>
                        Lotado!
                    </v-chip>
                    <v-spacer></v-spacer>
                    <v-icon @click="editViagem" class="mr-2">mdi-pencil</v-icon>
                    <v-icon @click="$openPopup('./relatorios/lista/'+viagem.id, 'Lista')">mdi-printer-pos</v-icon>
                </div>
                <v-data-table
                    :headers="headers"
                    :items="lista"
                    hide-default-footer
                    disable-sort
                    no-data-text="Nenhum paciente adicionado na lista ainda."
                    loading-text="Buscando dados..."
                    :loading="loading.list"
                >
                    <template v-slot:item.acompanhante_nome="{ item }">
                        <span v-text="item.acompanhante_nome != null ? 'SIM' : 'NÃO'"></span>
                    </template>
                    <template v-slot:item.compareceu="{ item }">
                        <v-icon color="success" text v-if="item.compareceu == 'SIM'" title="Sim">mdi-check</v-icon>
                        <v-icon color="error" text v-else-if="item.compareceu == 'NAO'" title="Não">mdi-close</v-icon>
                        <v-icon color="warning" text v-else title="Lista de presença ainda não foi preenchida">mdi-clock-outline</v-icon>
                    </template>
                    <!-- table actions -->
                    <template v-slot:item.action="{ item }">
                        <v-icon @click="editItem(item)" class="mr-2">mdi-pencil</v-icon>
                        <v-icon @click="deletePassageiro(item, false)">mdi-delete</v-icon>
                    </template>
                </v-data-table>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="close">Sair</v-btn>
            </v-card-actions>
        </v-card>
     </v-dialog>

    <!-- dialog edit / new paciente -->
    <v-dialog v-model="dialogEditPassageiro" max-width="500px" @click:outside="closeDialogPassageiro">
        <v-form v-model="formPassageiroValid" ref="formEditPassageiro">
            <v-card>
                <v-card-title>
                    <span class="headline">Paciente</span>
                </v-card-title>
                <v-card-text>
                    <v-container>

                        <v-autocomplete
                            v-model="selectedPassageiro.id_paciente"
                            :items="lookupPacientes"
                            :loading="loading.lookup"
                            :search-input.sync="searchPacientes"
                            item-text="nome"
                            item-value="id"
                            label="Selecione um paciente..."
                            :disabled="selectedPassageiroIndex > -1"
                            :rules="[v => !!v || 'Obrigatório!']"
                            no-data-text="Digite algo para pesquisar..."
                            @keyup="search"
                            >
                            <template v-slot:append-outer>
                                <v-icon @click="$openBlank('/cadastros/pacientes')">mdi-plus</v-icon>
                            </template>
                        </v-autocomplete>

                        <v-text-field 
                            label="Local da Consulta" 
                            v-model="selectedPassageiro.consulta_local"
                            :rules="[v => !!v || 'Local é obrigatório']"
                        ></v-text-field>
                        <v-text-field 
                            type="time"
                            label="Horário da consulta" 
                            v-model="selectedPassageiro.consulta_hora"
                        ></v-text-field>
                        <v-text-field label="Observação do Paciente" v-model="selectedPassageiro.consulta_observacao"></v-text-field>

                        <!-- @RULE: não deixa adicionar acompanhante na edição se o veículo está lotado -->
                        <v-tooltip right v-if="!precisaAcompanhanteOriginal && vagasDisponiveis == 0">
                            <template v-slot:activator="{ on }">
                                <div v-on="on" style="display: inline-block">
                                    <v-switch 
                                        v-model="precisaAcompanhante"
                                        label="Precisa acompanhante?"
                                        disabled
                                    ></v-switch>
                                </div>
                            </template>
                            <span>Você não pode adicionar<br> acompanhante a este paciente <br>porque o veículo já está lotado!</span>
                        </v-tooltip>
                        <v-switch 
                            v-else
                            v-model="precisaAcompanhante"
                            label="Precisa acompanhante?"
                        ></v-switch>

                        <v-text-field 
                            v-if="precisaAcompanhante" 
                            v-model="selectedPassageiro.acompanhante_nome" 
                            label="Nome do acompanhante"
                            :rules="[v => !!v && precisaAcompanhante || 'Informe o nome do acompanhante']"
                        ></v-text-field>
                        <v-text-field 
                            v-if="precisaAcompanhante" 
                            v-model="selectedPassageiro.acompanhante_rg" 
                            label="RG do acompanhante"
                            :rules="[v => (v ? (v.length >= 7 && v.length <= 10) : !v) || 'RG deve ter 7 a 10 dígitos!']" 
                        ></v-text-field>
                    </v-container>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="red darken-1" text @click="closeDialogPassageiro">Cancelar</v-btn>
                    <v-btn color="blue darken-1" :loading="loading.edit" text @click="save">Salvar</v-btn>
                </v-card-actions>
            </v-card>
        </v-form>
    </v-dialog>

    <!-- dialog delete passageiro -->
    <v-dialog v-model="dialogDeletePassageiro" max-width="290">
        <v-card>
            <v-card-title class="headline">Confirmar exclusão?</v-card-title>
            <v-card-text>Tem certeza que deseja deletar o passageiro {{selectedPassageiro.paciente_nome}} dessa lista?</v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="dialogDeletePassageiro = false">Cancelar</v-btn>
                <v-btn color="red darken-1" :loading="loading.delete" text @click="deletePassageiro(selectedPassageiroIndex, true)">Excluir</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
    <lookup 
        ref="lookupComponentLista"
        v-on:updateLookup="updateLookup"
    ></lookup>
</div>
</template>
<script>

    export default {
    
        props: {
            dialogList: {
                type: Boolean,
                required: true
            },
            viagem: {
                type: Object,
                required: true
            }
        },

        data: () => ({

            // main data
            lista: [],
            lookupPacientes: [],
            api: window.__routes.api.lista,

            // table column names
            headers: [
                { text: 'Paciente', value: 'paciente_nome'},
                { text: 'Local', value: 'consulta_local'},
                { text: 'Hora', value: 'consulta_hora'},
                { text: 'Acompanhante', value: 'acompanhante_nome'},
                { text: 'Compareceu?', value: 'compareceu'},
                { text: 'Ações', value: 'action'},
            ],

            // state of elements
            loading: {
                edit: false,
                delete: false,
                lookup: false,
                list: false
            },
            dialogEditPassageiro: false,
            dialogDeletePassageiro: false,
            precisaAcompanhante: false,
            formPassageiroValid: false,
            timeoutId: null,
            precisaAcompanhanteOriginal: false,

            // CRUD variables
            searchPacientes: null,
            selectedPassageiroIndex: null,
            selectedPassageiro: {
                id_paciente: null,
                id_viagem: null,
                acompanhante_rg: null,
                acompanhante_nome: null,
                consulta_local: null,
                consulta_hora: null,
                consulta_observacao: null,
            },
            defaultPassageiro: {
                id_paciente: null,
                id_viagem: null,
                acompanhante_rg: null,
                acompanhante_nome: null,
                consulta_local: null,
                consulta_hora: null,
                consulta_observacao: null,
            },
        }),      

        methods: { 
            
            search () {
                // pra não pesquisar se está disable
                if (this.selectedPassageiroIndex > -1) return
                if (this.searchPacientes == null || this.searchPacientes == '') return
                this._debounce();
            },

            getListaPassageiros () {

                this.loading.list = true;
                let vm = this;

                axios
                .get(vm.api+'?viagem='+vm.viagem.id)
                .then(function(response) {
                    vm.lista = response.data
                })
                .catch((error) => console.log(error))
                .finally(() => vm.loading.list = false)
            },

            /*
            * Exclui um registro
            * @param item Obj
            * @param confirm bool
            * Ao clicar nas ações da tabela o parametro confirm vem false e abre o dialog de confirmação
            * Para ficar true é necessário confirmar no dialog que abre posteriormente
            */

            deletePassageiro: function (item, confirm) {
                
                if (!confirm) {
                    this.selectedPassageiro = item;
                    this.selectedPassageiroIndex = this.lista.indexOf(item);
                    this.dialogDeletePassageiro = true
                    return;
                }
                
                let vm = this;
                vm.loading.delete = true;

                axios
                    .delete(vm.api+'/'+vm.selectedPassageiro.id_paciente, {
                        data: {"viagem": vm.selectedPassageiro.id_viagem}, 
                        headers: {"Authorization": "***"}}
                    )
                    .then(function(response){
                        if(response.data.error) {
                            vm.$toast.error('Erro ao deletar: '+response.data.error)
                        }
                        else {
                            vm.$toast.success('Passageiro deletado com sucesso!')
                            vm.lista.splice(vm.selectedPassageiroIndex, 1);
                            vm.dialogDeletePassageiro = false
                        }
                    })
                    .finally(() => vm.loading.delete = false);
            },

            /*
            * Salvar item editado ou novo
            * Se tiver index é edição, senão é novo registro
            */

            save: function () {

                // @RULE: se estiver inserindo e só tem espaço para o paciente, da erro ao ativar acompanhante
                if (this.selectedPassageiroIndex == -1 && this.vagasDisponiveis == 1 && this.precisaAcompanhante) {
                    this.$toast.warning('Existe apenas uma vaga disponível neste veículo. \n Não há lugar para acompanhante.')
                    return;
                }

                // validação regras do formulário
                if (!this.$refs.formEditPassageiro.validate()) return;
                
                let vm = this;
                vm.loading.edit = true;

                if (vm.selectedPassageiroIndex > -1) {
                    axios
                    .put(vm.api+'/'+this.viagem.id, vm.selectedPassageiro)
                    .then(function(response){
                        if(response.data.error) {
                            vm.$toast.error('Erro ao editar: '+response.data.error)
                        }
                        else {
                            Object.assign(vm.lista[vm.selectedPassageiroIndex], vm.selectedPassageiro)
                            vm.dialogEditPassageiro = false;
                            vm.$toast.success('Passageiro salvo com sucesso!')
                        }
                    })
                    .finally(() => vm.loading.edit = false)

                } else {
                    axios
                    .post(vm.api, {
                        'id_paciente'       : vm.selectedPassageiro.id_paciente,
                        'id_viagem'         : vm.viagem.id,
                        'acompanhante_rg'   : vm.selectedPassageiro.acompanhante_rg,
                        'acompanhante_nome' : vm.selectedPassageiro.acompanhante_nome,
                        'consulta_local'    : vm.selectedPassageiro.consulta_local,
                        'consulta_hora'     : vm.selectedPassageiro.consulta_hora,
                        'consulta_observacao'   : vm.selectedPassageiro.consulta_observacao,
                    })
                    .then(function(response){
                        if(response.data.error) {
                            vm.$toast.error('Erro ao cadastrar: '+response.data.error)
                        }
                        else {
                            vm.dialogEditPassageiro = false;
                            vm.getListaPassageiros();
                            vm.$toast.success('Passageiro cadastrado com sucesso!')
                        }
                    })
                    .finally(() => vm.loading.edit = false)
                }
            },

            // vue não permite editar diretamente uma prop. por isso, usa-se emit aqui e prop.sync no componente pai
            close () {
                this.$emit('update:dialogList', false);
            },
            editViagem () {
                this.$emit('editViagem');
            },

            closeDialogPassageiro () {
                this.dialogEditPassageiro = false;
            },

            // executado quando abre o modal
            initialize: function () {
                this.lista = [];
                this.defaultPassageiro.id_viagem = this.viagem.id
                this.getListaPassageiros();
            },

            // $ref do formulario nao está disponivel na criação do componente
            resetPassageiroEditValidation: function () {
                if(typeof this.$refs.formEditPassageiro != "undefined") this.$refs.formEditPassageiro.resetValidation();
            },

            // Abre modal e assinala index nulo e valores padrão para inserir novo registro
            addNewPassageiro: function () {
                this.selectedPassageiroIndex = -1;
                this.precisaAcompanhante = false;
                this.precisaAcompanhanteOriginal = false;
                this.selectedPassageiro = Object.assign({}, this.defaultPassageiro);
                this.lookupPacientes = [];
                this.resetPassageiroEditValidation();
                this.dialogEditPassageiro = true;
            },

            // Abre o modal de edição a partir da coluna "Ações" da tabela
            editItem: function (item) {
                this.selectedPassageiroIndex = this.lista.indexOf(item);
                this.selectedPassageiro = Object.assign({}, item);
                this.precisaAcompanhante = item.acompanhante_nome != null && item.acompanhante_nome != '';
                // variavel pra nao permitir ativação de acompanhante se a lista está cheia
                this.precisaAcompanhanteOriginal = this.precisaAcompanhante;
                this.resetPassageiroEditValidation();
                this.lookupPacientes.push({id: item.id_paciente, nome: item.paciente_nome})
                this.dialogEditPassageiro = true;
            },

            // faz a busca de pacientes no autocomplete
            getPacientesLookup: function () {
                this.$refs.lookupComponentLista.getLookup('PACIENTE', this.searchPacientes);
            },

            // callback da busca de lookup
            updateLookup (datasetName, dataArray) {
                this.lookupPacientes = dataArray;
                this.loading.lookup = false;
            },

            // isso serve pra não fazer request a cada letra digitada, mas sim, após um tempo sem digitar
            _debounce(){
                this.loading.lookup = true;
                if (this.timeoutId !== null) {
                    clearTimeout(this.timeoutId);
                }
                this.timeoutId = setTimeout( _ => {
                    this.getPacientesLookup();
                }, this.$debounceTime);
            },
        },

        computed: {
            vagasDisponiveis () {
                return this.viagem.lotacao - this.lista.reduce((total, element) => 
                    (element.acompanhante_nome != null ? total + 2 : total + 1), 0)
            }
        },

        watch: {
            // quando a prop dialogList é true, está abrindo o dialog, senão está fechando
            dialogList: function(val) {
                if (val) this.initialize();
            },

            precisaAcompanhante: function(val) {
                if (!val) {
                    this.selectedPassageiro.acompanhante_rg = null;
                    this.selectedPassageiro.acompanhante_nome = null;
                }
            }
        },

        filters: {
            substring (str) {
                let size = 14;
                if (str == null) return;
                return str.length > size ? str.substr(0, size)+'...' : str;
            }
        }
    }
</script>