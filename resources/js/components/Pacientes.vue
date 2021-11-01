<template>
    <div>
        <v-card class="ma-md-2 px-2 ma-xs-0">
        <!-- table header -->
        <v-card-title>
            <v-row>
            <v-col cols="12" md="8">
                Pacientes
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
            :items="pacientes"
            :loading="loading.list"
            hide-default-footer
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
                <v-icon @click="$openPopup('/relatorios/paciente/'+item.id, 'paciente')">mdi-printer-pos</v-icon>
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
            <v-card-text>Deseja mesmo deletar o paciente "{{ selectedItem.nome }}"?</v-card-text>
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
                        <span class="headline">Paciente</span>
                        <v-spacer></v-spacer>
                        <v-switch 
                            class="pr-4"
                            v-model="selectedItem.status"
                            :label="selectedItem.status == '1' ? 'Ativo' : 'Inativo'"
                        ></v-switch>
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12" sm="12" md="6">
                                <v-text-field 
                                    v-model="selectedItem.nome" 
                                    label="Nome" 
                                    required 
                                    :rules="[v => !!v || 'Nome é obrigatório']"
                                    prepend-icon="mdi-account"
                                ></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="12" md="6">
                                <v-text-field 
                                    v-model="selectedItem.rg"
                                    :rules="[v => (v ? (v.length >= 7 && v.length <= 10) : !v) || 'Deve ter 7 a 10 caracteres!']" 
                                    label="RG"
                                    prepend-icon="mdi-account-details"
                                ></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="12" sm="12" md="6">
                                <v-text-field 
                                    v-mask="['(##) ####-####', '(##) #####-####']" 
                                    v-model="selectedItem.telefone" 
                                    :rules="[v => (v ? (v.length > 13) : !v) || 'Telefone deve ter 10 ou 11 números']" 
                                    label="Telefone"
                                    prepend-icon="mdi-phone"
                                ></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="12" md="6">
                                <v-text-field 
                                    type="number"
                                    v-model="selectedItem.sus" 
                                    :rules="[v => (v ? (v < 999999999999999) : !v) || 'SUS deve ter até 15 algarismos']"
                                    label="Cartão SUS" 
                                    prepend-icon="mdi-account-details-outline"
                                ></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="12" sm="12" md="12">
                                <v-text-field 
                                    v-model="selectedItem.endereco" 
                                    label="Endereço" 
                                    prepend-icon="mdi-map-marker"
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
            pacientes: [],
            api: window.__routes.api.paciente,

            // state of elements
            dialogDelete: false,
            dialogEdit: false,
            formValid: true,
            loading: {
                list: false,
                edit: false,
                delete: false
            },

            // table column names
            headers: [
                { text: '#', value: 'id'},
                { text: 'Nome', value: 'nome'},
                { text: 'RG', value: 'rg'},
                { text: 'Telefone', value: 'telefone'},
                { text: 'Endereço', value: 'endereco'},
                { text: 'Status', value: 'status'},
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
                sus: null,
                rg: null,
                endereco: null,
                status: 1
            },
            defaultItem: {
                nome: null,
                telefone: null,
                sus: null,
                rg: null,
                endereco: null,
                status: 1
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
                        vm.pacientes             = response.data.data;
                        vm.pagination.current     = response.data.current_page;
                        vm.pagination.total       = response.data.last_page;
                        vm.pagination.totalItems  = response.data.total;
                        vm.pagination.perPage     = response.data.per_page;
                        
                    })
                    .catch(function(error) {
                        vm.$toast.error('Erro ao buscar pacientes')
                    })
                    .finally(() => { vm.loading.list = false; });
            },

            /*
            * Salvar item editado ou novo
            * Se tiver index é edição, senão é novo registro
            */

            save: function () {

                if (!this.$refs.formEdit.validate()) return;
                let vm = this;
                vm.loading.edit = true;

                if (vm.selectedItem.rg != null)
                vm.selectedItem.rg = vm.selectedItem.rg.toUpperCase();
                
                if (this.selectedIndex > -1) {
                axios
                    .put(this.api+'/'+this.selectedItem.id, vm.selectedItem)
                    .then(function(response){
                        if(response.data.error) {
                        vm.$toast.error('Erro ao editar: '+response.data.error)
                        }
                        else {
                        Object.assign(vm.pacientes[vm.selectedIndex], vm.selectedItem)
                        vm.dialogEdit = false;
                        vm.$toast.success('Paciente salvo com sucesso!')
                        }
                    })
                    .finally(() => { vm.loading.edit = false; });

                } else {
                axios
                    .post(this.api, {
                        'nome'      : vm.selectedItem.nome,
                        'telefone'  : vm.selectedItem.telefone,
                        'rg'        : vm.selectedItem.rg,
                        'sus'       : vm.selectedItem.sus,
                        'endereco'  : vm.selectedItem.endereco,
                        'status'    : vm.selectedItem.status
                    })
                    .then(function(response){
                        if(response.data.error) {
                            vm.$toast.error('Erro ao cadastrar: '+response.data.error)
                        }
                        else {
                            // item adicionado vai sempre por último
                            vm.pagination.current = parseInt(vm.pagination.totalItems / vm.pagination.perPage) + 1;
                            vm.getItems();
                            vm.dialogEdit = false;
                            vm.$toast.success('Paciente cadastrado com sucesso!')
                        }
                    })
                    .finally(() => { vm.loading.edit = false; });
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
                    this.selectedIndex = this.pacientes.indexOf(item);
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
                            vm.$toast.success('paciente deletado com sucesso!')
                            vm.pacientes.splice(vm.selectedIndex, 1)
                            // utilizado para carregar a paginação correta após adicionar
                            vm.pagination.totalItems--;
                            //se for último da paginação e tiver mais paginações, dá reload
                            if (vm.pacientes.length == 0 && vm.pagination.current > 1){
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
                this.selectedIndex = this.pacientes.indexOf(item);
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