<template>
    <div>
        <v-card class="ma-md-2 px-2 ma-xs-0">
        <!-- table header -->
        <v-card-title>
            <v-row>
            <v-col cols="12" md="8">
                Motoristas
                <v-btn class="ml-4" fab dark small color="primary" @click="addNew">
                <v-icon dark>mdi-plus</v-icon>
                </v-btn>
            </v-col>
            <v-col cols="12" md="4">
                <v-form @submit.prevent="search()">
                <v-text-field 
                    v-model="searchWord" 
                    :prepend-icon="'mdi-magnify'"
                    @click:clear="resetSearch()"
                    :hint="'Digite sua busca e tecle Enter!'"
                    label="Pesquisar"
                    clearable
                    >
                </v-text-field>
                </v-form>
            </v-col>
            </v-row>                        
        </v-card-title>

        <!-- table  -->
        <v-data-table
            :headers="headers"
            :items="motoristas"
            :loading="loading.list"
            hide-default-footer
            disable-sort
            no-data-text="Nenhum dado encontrado."
            loading-text="Buscando dados..."
        >
            <!-- table actions -->
            <template v-slot:item.action="{ item }">
                <v-icon class="mr-2" @click="editItem(item)">mdi-pencil</v-icon>
                <v-icon @click="deleteItem(item, false)">mdi-delete</v-icon>
            </template>
        </v-data-table>

        <div class="text-center py-3">
            <v-pagination
                v-model="pagination.current"
                :length="pagination.total"
                @input="onPageChange"
                circle
            ></v-pagination>
        </div>
        </v-card>

        <!-- dialog delete -->
        <v-dialog v-model="dialogDelete" max-width="290">
        <v-card>
            <v-card-title class="headline">Confirmar exclusão?</v-card-title>
            <v-card-text>Tem certeza que deseja deletar o motorista "{{ selectedItem.nome }}"?</v-card-text>
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
                        <span class="headline">Motorista</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12" sm="12" md="12">
                                <v-text-field 
                                    v-model="selectedItem.nome" 
                                    label="Nome" 
                                    required 
                                    :rules="[v => !!v || 'Nome é obrigatório']"
                                ></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="12" sm="12" md="6">
                                <v-text-field 
                                    v-model="selectedItem.telefone" 
                                    label="Telefone"
                                    v-mask="['(##) ####-####', '(##) #####-####']" 
                                    :rules="[v => (v ? (v.length > 13) : !v) || 'Telefone deve ter 10 ou 11 números']" 
                                ></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="12" md="6">
                                <v-text-field 
                                    v-model="selectedItem.access_key" 
                                    label="Chave de Acesso"
                                    type="number"
                                    :rules="[v => (v ? (v > 1000) : !v) || 'Deve ter no mínimo 4 dígitos']" 
                                    hint="Será utilizada para lançamento da lista de presença. Se você não preencher, será gerada automaticamente"
                                ></v-text-field>
                                </v-col>
                            </v-row>
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

    </div>
</template>

<script>

    import {TheMask} from 'vue-the-mask'

    export default {
    
        components: {TheMask},

        data: () => ({
            
            // main data
            motoristas: [],
            api: window.__routes.api.motorista,

            // state of elements
            dialogDelete: false,
            dialogEdit: false,
            loading: {
                list: false,
                edit: false,
                delete: false
            },
            formValid: true,

            // table column names
            headers: [
                { text: '#', value: 'id'},
                { text: 'Nome', value: 'nome'},
                { text: 'Telefone', value: 'telefone'},
                { text: 'Chave de Acesso', value: 'access_key'},
                { text: 'Ações', value: 'action'},
            ],

            // server side pagination
            pagination: {
                current: 1,
                total: 0,
                totalItems: 0,
                perPage: 0,
            },
            
            // CRUD variables
            selectedIndex: null,
            selectedItem: {
                nome: null,
                telefone: null,
                access_key: null
            },
            defaultItem: {
                nome: null,
                telefone: null,
                access_key: null
            },
            searchWord: null,
            }),

        mounted() {

            this.getItems();

        },

        methods: { 

        getItems: function() {

            let vm        = this;
            let page      = vm.pagination.current;
            let urlFetch  = this.api+'?page='+page;

            if(vm.searchWord != null && vm.searchWord != '') urlFetch+= '&search='+vm.searchWord;

            vm.loading.list = true;

            axios
                .get(urlFetch)
                .then(function(response) {
                    vm.motoristas             = response.data.data;
                    vm.pagination.current     = response.data.current_page;
                    vm.pagination.total       = response.data.last_page;
                    vm.pagination.totalItems  = response.data.total;
                    vm.pagination.perPage     = response.data.per_page;
                    
                })
                .catch(function(error) {
                    vm.$toast.error('Erro ao buscar motoristas')
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
                        Object.assign(vm.motoristas[vm.selectedIndex], vm.selectedItem)
                        vm.$toast.success('Motorista salvo com sucesso!')
                        vm.dialogEdit = false;
                        }
                    })
                    .finally(() => {vm.loading.edit = false;});

                } else {
                axios
                    .post(this.api, {
                        'nome'      : vm.selectedItem.nome,
                        'telefone'  : vm.selectedItem.telefone,
                        'access_key': vm.selectedItem.access_key
                    })
                    .then(function(response){
                        if(response.data.error) {
                            vm.$toast.error('Erro ao cadastrar: '+response.data.error)
                        }
                        else {
                            // item adicionado vai sempre por último
                            vm.pagination.current = parseInt(vm.pagination.totalItems / vm.pagination.perPage) + 1;
                            vm.getItems();
                            vm.$toast.success('Motorista cadastrado com sucesso!');
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
                    this.selectedIndex = this.motoristas.indexOf(item);
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
                            vm.$toast.success('Motorista deletado com sucesso!')
                            vm.motoristas.splice(vm.selectedIndex, 1)
                            // utilizado para carregar a paginação correta após adicionar
                            vm.pagination.totalItems--;
                            //se for último da paginação e tiver mais paginações, dá reload
                            if (vm.motoristas.length == 0 && vm.pagination.current > 1){
                                vm.pagination.current--;
                                vm.getItems()
                            }
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
                this.selectedIndex = this.motoristas.indexOf(item);
                this.selectedItem = Object.assign({}, item);
                this.resetEditValidation();
                this.dialogEdit = true;
            },

            // $ref do formulario nao está disponivel na criação do componente
            resetEditValidation: function () {
                if(typeof this.$refs.formEdit != "undefined") this.$refs.formEdit.resetValidation();
            },

            // Inicia uma pesquisa
            search: function () {
                this.pagination.current = 1;
                this.getItems();
            },

            // Limpa a pesquisa
            resetSearch: function () {
                this.pagination.current = 1;
                this.searchWord = null;
                this.getItems();
            },

            // A cada clique na paginação, recarrega a lista. Se tivre filtro vai persistir
            onPageChange: function () {
                this.getItems();
            },
        }
    }
</script>