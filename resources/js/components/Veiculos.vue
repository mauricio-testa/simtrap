<template>
    <div>
        <v-card class="ma-md-2 px-2 ma-xs-0">
        <!-- table header -->
        <v-card-title>
            <v-row>
                <v-col cols="12" md="8">
                    Veículos
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
            :items="veiculos"
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
                <v-card-text>Tem certeza que deseja deletar o veículo "{{ selectedItem.descricao }}"?</v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="dialogDelete = false">Cancelar</v-btn>
                    <v-btn color="red darken-1" text @click="deleteItem(selectedIndex, true)" :loading="loading.delete">Excluir</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- dialog edit / new -->
        <v-dialog v-model="dialogEdit" max-width="650px">
            <v-form v-model="formValid" ref="formEdit">
                <v-card>
                    <v-card-title>
                        <span class="headline">Veículo</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container>

                            <v-row>
                                <v-col cols="12" sm="12" md="4">
                                <v-text-field 
                                    v-model="selectedItem.descricao"
                                    :rules="[v => !!v || 'Descrição é obrigatório!']"
                                    label="Descrição" 
                                ></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="12" md="4">
                                <v-text-field
                                    v-model="selectedItem.placa"
                                    :rules="placaRules" 
                                    label="Placa" 
                                ></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="12" md="4">
                                <v-select
                                    :items="tipos"
                                    v-model="selectedItem.tipo"
                                    label="Tipo"
                                ></v-select>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12" sm="12" md="3">
                                    <v-text-field label="Lotação" v-model="selectedItem.lotacao" :rules="[v => v > 0 || 'Deve ser maior que 0!']"></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="12" md="4">
                                    <v-text-field label="Ano/Modelo" v-model="selectedItem.ano_modelo" hint="Exemplo: 2013/2014"></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="12" md="5">
                                    <v-text-field label="Marca/Modelo" v-model="selectedItem.marca_modelo" hint="Exemplo: Fiat/Ducato MT TCA AMB"></v-text-field>
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

    export default {

        data: () => ({

            // main data
            veiculos: [],
            api: window.__routes.api.veiculo,

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
                { text: 'Descrição', value: 'descricao'},
                { text: 'Placa', value: 'placa'},
                { text: 'Lotação', value: 'lotacao'},
                { text: 'Tipo', value: 'tipo'},
                { text: 'Ano/Modelo', value: 'ano_modelo'},
                { text: 'Marca/Modelo', value: 'marca_modelo'},
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
                descricao: null,
                placa: null,
                lotacao: null,
                tipo: null,
                ano_modelo: null,
                marca_modelo: null
            },
            defaultItem: {
                descricao: null,
                placa: null,
                lotacao: null,
                tipo: 'PROPRIO',
                ano_modelo: null,
                marca_modelo: null
            },
            searchWord: null,

            // form variables
            tipos: [
                { text: 'Próprio', value: 'PROPRIO'},
                { text: 'Terceirizado', value: 'TERCEIRIZADO'}
            ],
            placaRules: [
                v => (!!v && v.length == 7) || 'Deve ter 7 caracteres!',
                v => /^[A-Za-z0-9]+$/.test(v) || 'Somente letras e números!',
            ],
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
                        vm.veiculos               = response.data.data;
                        vm.pagination.current     = response.data.current_page;
                        vm.pagination.total       = response.data.last_page;
                        vm.pagination.totalItems  = response.data.total;
                        vm.pagination.perPage     = response.data.per_page;
                    
                    })
                    .catch(function(error) {
                        vm.$toast.error('Erro ao buscar veículos')
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
                vm.selectedItem.placa = vm.selectedItem.placa.toUpperCase();
                
                if (this.selectedIndex > -1) {
                axios
                    .put(this.api+'/'+this.selectedItem.id, vm.selectedItem)
                    .then(function(response){
                        if(response.data.error) {
                            vm.$toast.error('Erro ao editar: '+response.data.error)
                        }
                        else {
                            Object.assign(vm.veiculos[vm.selectedIndex], vm.selectedItem);
                            vm.$toast.success('Veículo salvo com sucesso!');
                            vm.dialogEdit = false;
                        }
                    })
                    .finally(() => { vm.loading.edit = false; });

                } else {
                axios
                    .post(this.api, {
                        'descricao'   : vm.selectedItem.descricao,
                        'placa'       : vm.selectedItem.placa,
                        'lotacao'     : vm.selectedItem.lotacao,
                        'ano_modelo'  : vm.selectedItem.ano_modelo,
                        'marca_modelo': vm.selectedItem.marca_modelo,
                        'tipo'        : vm.selectedItem.tipo,
                    })
                    .then(function(response){
                        if(response.data.error) {
                            vm.$toast.error('Erro ao cadastrar: '+response.data.error)
                        }
                        else {
                            // item adicionado vai sempre por último
                            vm.pagination.current = parseInt(vm.pagination.totalItems / vm.pagination.perPage) + 1;
                            vm.getItems();
                            vm.$toast.success('Veículo cadastrado com sucesso!')
                            vm.dialogEdit = false;
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
                    this.selectedIndex = this.veiculos.indexOf(item);
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
                            vm.$toast.success('Veículo deletado com sucesso!')
                            vm.veiculos.splice(vm.selectedIndex, 1)
                            // utilizado para carregar a paginação correta após adicionar
                            vm.pagination.totalItems--;
                            //se for último da paginação e tiver mais paginações, dá reload
                            if (vm.veiculos.length == 0 && vm.pagination.current > 1){
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
                this.selectedIndex = this.veiculos.indexOf(item);
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