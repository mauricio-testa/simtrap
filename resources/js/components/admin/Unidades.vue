<template>
    <div>
        <v-card class="ma-md-2 px-2 ma-xs-0">
        <!-- table header -->
        <v-card-title>
            <v-row>
            <v-col cols="12" md="8">
                Unidades
                <v-btn class="ml-4" fab dark small color="primary" @click="addNew">
                <v-icon dark>mdi-plus</v-icon>
                </v-btn>
            </v-col>
            </v-row>                        
        </v-card-title>

        <!-- table  -->
        <v-data-table
            :headers="headers"
            :items="unidades"
            :loading="loading.list"
            disable-sort
            no-data-text="Nenhum dado encontrado."
            loading-text="Buscando dados..."
        >
            <template v-slot:item.status="{ item }">
                <v-icon v-if="item.status == '1'" color="success">mdi-check</v-icon>
                <v-icon v-else color="error">mdi-close</v-icon>
            </template>
            <!-- table actions -->
            <template v-slot:item.action="{ item }">
                <v-icon class="mr-2" @click="editItem(item)">mdi-pencil</v-icon>
                <v-icon class="mr-2" @click="deleteItem(item, false)">mdi-delete</v-icon>
                <v-icon @click="openUsers(item)" title="Usuários da Unidade">mdi-account</v-icon>
            </template>
        </v-data-table>

        </v-card>

        <!-- dialog delete -->
        <v-dialog v-model="dialogDelete" max-width="290">
        <v-card>
            <v-card-title class="headline">Confirmar exclusão?</v-card-title>
            <v-card-text>Tem certeza que deseja deletar a unidade "{{ selectedItem.descricao }}"?</v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="dialogDelete = false">Cancelar</v-btn>
                <v-btn color="red darken-1" text @click="deleteItem(selectedIndex, true)" :loading="loading.delete">Excluir</v-btn>
            </v-card-actions>
        </v-card>
        </v-dialog>

        <!-- dialog edit / new -->
        <v-dialog v-model="dialogEdit" max-width="500px">
            <v-form v-model="formValid" ref="formEdit">
                <v-card>
                    <v-card-title>
                        <span class="headline">Unidade</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-text-field 
                                v-model="selectedItem.descricao" 
                                label="Descrição" 
                                required 
                                :rules="[v => !!v || 'Descrição é obrigatório']"
                            ></v-text-field>
                            <v-autocomplete
                                v-model="selectedItem.id_municipio"
                                :items="lookupMunicipios"
                                :loading="loading.lookupMunicipios"
                                item-text="nome"
                                item-value="codigo"
                                label="Município da Unidade"
                                :rules="[v => !!v || 'Município é obrigatório']"
                            ></v-autocomplete>
                            <v-text-field 
                                v-model="selectedItem.responsavel" 
                                label="Responsável" 
                            ></v-text-field>
                            <v-switch 
                                v-model="selectedItem.status"
                                :label="selectedItem.status == '1' ? 'Ativo' : 'Inativo'"
                            ></v-switch>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="red darken-1" text @click="dialogEdit = false">Cancelar</v-btn>
                        <v-btn color="blue darken-1" text @click="save" :loading="loading.edit">Salvar</v-btn>
                    </v-card-actions>
                </v-card>
            </v-form>
        </v-dialog>

        <lookup ref="lookupComponent" v-on:updateLookup="updateLookup"></lookup>
        <admin-usuario :unidade="selectedItem" :dialogUsers.sync="dialogUsers"></admin-usuario>

    </div>
</template>

<script>

    import {TheMask} from 'vue-the-mask'

    export default {
    
        components: {TheMask},

        data: () => ({
            
            // main data
            unidades: [],
            api: window.__routes.api.unidade,

            // state of elements
            dialogDelete: false,
            dialogEdit: false,
            dialogUsers: false,
            loading: {
                list: false,
                edit: false,
                delete: false,
                lookupMunicipios: false
            },
            formValid: true,
            lookupMunicipios: [],

            // table column names
            headers: [
                { text: '#', value: 'id'},
                { text: 'Descrição', value: 'descricao'},
                { text: 'Município', value: 'municipio_nome'},
                { text: 'Active', value: 'status'},
                { text: 'Data Criação', value: 'created_at'},
                { text: 'Ações', value: 'action'},
            ],
            
            // CRUD variables
            selectedIndex: null,
            selectedItem: {
                status: null,
                id_municipio: null,
                descricao: null
            },
            defaultItem: {
                status: '1',
                id_municipio: null,
                descricao: null
            },
        }),

        mounted() {

            this.getItems();

        },

        methods: { 

            updateLookup (datasetName, dataArray) {
                this.lookupMunicipios = dataArray;
                this.loading.lookupMunicipios = false
            },

            getItems: function() {

                let vm        = this;
                let urlFetch  = this.api;
                vm.loading.list = true;

                axios
                    .get(urlFetch)
                    .then(function(response) {
                        vm.unidades = response.data;
                    })
                    .catch(function(error) {
                        vm.$toast.error('Erro ao buscar unidades')
                    })
                    .finally(() => {vm.loading.list = false;});
            },

            /*
            * Salvar item editado ou novo
            * Se tiver index é edição, senão é novo registro
            */

            save: function () {

                if (!this.$refs.formEdit.validate()) return;
                let vm = this;
                vm.loading.edit = true;
                
                if (this.selectedIndex > -1) {
                axios
                    .put(this.api+'/'+this.selectedItem.id, vm.selectedItem)
                    .then(function(response){
                        if(response.data.error) {
                            vm.$toast.error('Erro ao editar: '+response.data.error)
                        }
                        else {
                            Object.assign(vm.unidades[vm.selectedIndex], vm.selectedItem)
                            vm.$toast.success('Unidade salva com sucesso!')
                            vm.dialogEdit = false;
                        }
                    })
                    .finally(() => {vm.loading.edit = false;});

                } else {
                axios
                    .post(this.api, {
                        'descricao'     : vm.selectedItem.descricao,
                        'status'        : vm.selectedItem.status,
                        'id_municipio'  : vm.selectedItem.id_municipio,
                        'responsavel'   : vm.selectedItem.responsavel,
                    })
                    .then(function(response){
                        if(response.data.error) {
                            vm.$toast.error('Erro ao cadastrar: '+response.data.error)
                        }
                        else {
                            vm.getItems();
                            vm.$toast.success('Unidade cadastrada com sucesso!');
                            vm.dialogEdit = false;
                        }
                    })
                    .finally(() => {vm.loading.edit = false;});
                }
            },

            /*
            * Exclui um registro
            * @param item Obj
            * @param confirm bool
            * Ao clicar nas ações da tabela o parametro confirm vem false e abre o dialog de confirmação
            * Para ficar true é necessário confirmar no dialog que abre posteriormente
            */

            deleteItem: function (item, confirm) {
                
                if (!confirm) {
                    this.selectedItem = item;
                    this.selectedIndex = this.unidades.indexOf(item);
                    this.dialogDelete = true
                    return;
                }

                let vm = this;
                vm.loading.delete = true;

                axios
                    .delete(this.api+'/'+this.selectedItem.id)
                    .then(function(response){
                        if(response.data.error) {
                            vm.$toast.error('Erro ao deletar: '+response.data.error)
                        }
                        else {
                            vm.$toast.success('Unidade deletada com sucesso!')
                            vm.unidades.splice(vm.selectedIndex, 1)
                        }
                    })
                .finally(function() {
                    vm.dialogDelete = false;
                    vm.loading.delete = false;
                });
            },

            // Abre modal e assinala index nulo e valores padrão para inserir novo registro
            addNew: function () {
                this.selectedItem = Object.assign({}, this.defaultItem);
                this.resetEditValidation();
                this.selectedIndex = -1;
                this.dialogEdit = true;
            },

            // Abre o modal de edição a partir da coluna "Ações da tabela"
            editItem: function (item) {
                this.selectedIndex = this.unidades.indexOf(item);
                this.selectedItem = Object.assign({}, item);
                this.resetEditValidation();
                this.dialogEdit = true;
            },

            // $ref do formulario nao está disponivel na criação do componente
            resetEditValidation: function () {
                if(typeof this.$refs.formEdit != "undefined") this.$refs.formEdit.resetValidation();
            },

            // abre o dialog de usuarios
            openUsers: function (item) {
                this.selectedItem = item;
                this.dialogUsers = true;
            }
        },

        watch: {
            dialogEdit: function(val) {
                if (this.lookupMunicipios.length == 0 && val) {
                    this.loading.lookupMunicipios = true
                    this.$refs.lookupComponent.getLookup('MUNICIPIO');
                }
            }
        }
    }
</script>