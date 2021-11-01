<template>
    <div>
        <v-card class="ma-md-2 px-2 ma-xs-0">
        <!-- table header -->
        <v-card-title>
            <v-row>
            <v-col cols="12" md="8">
                Viagens
                <v-btn class="ml-4" fab dark small color="primary" @click="addNew">
                    <v-icon dark>mdi-plus</v-icon>
                </v-btn>
            </v-col>
            <v-col cols="12" md="4" class="d-flex align-center justify-end">
                <v-form @submit.prevent="search()" style="width: 100%">
                    <v-text-field 
                        v-model="searchWord" 
                        :prepend-icon="'mdi-magnify'"
                        @click:clear="resetSearch()"
                        hint="Pesquise por município, motorista, veículo, data, observação..."
                        label="Pesquisar"
                        clearable
                        >
                    </v-text-field>
                </v-form>
                <!--
                <v-tooltip bottom>
                    <template v-slot:activator="{ on }">
                        <v-btn text class="ml-3" v-on="on"><v-icon dark>mdi-filter</v-icon></v-btn>
                    </template>
                    <span>Busca Avançada</span>
                </v-tooltip>
                -->
            </v-col>
            </v-row>
        </v-card-title>

        <!-- table  -->
        <v-data-table
            :headers="headers"
            :items="viagens"
            :loading="loading.list"
            hide-default-footer
            disable-sort
            no-data-text="Nenhum dado encontrado."
            loading-text="Buscando dados..."
        >
            <template v-slot:item.status="{ item }" @click="expandChip = !expandChip">
                <v-chip small dark v-if="item.status == 'NOVA'" class="deep-orange" title="A viagem ainda não foi exportada">A Exportar</v-chip>
                <v-chip small dark v-if="item.status == 'EXPORTADA'" class="light-blue" title="A viagem foi exportada e aguarda preenchimento da chamada">Exportada</v-chip>
                <v-chip small dark v-if="item.status == 'CONCLUIDA'" class="teal" title="A chamada foi preenchida">Concluída</v-chip>
            </template>
            <!-- table actions -->
            <template v-slot:item.action="{ item }">
                <v-icon @click="editItem(item)" class="mr-2">mdi-pencil</v-icon>
                <v-icon @click="deleteItem(item, false)" class="mr-2">mdi-delete</v-icon>
                <v-icon @click="listPassageiros(item)" class="mr-2" title="Editar passageiros">mdi-view-list</v-icon>
                <v-icon @click="$openPopup('./relatorios/lista/'+item.id, 'Lista')" class="mr-2" title="Exportar lista de viagem">mdi-printer-pos</v-icon>
                <v-icon @click="$openPopup('./chamada/'+item.id, 'Chamada')" title="Preencher chamada">mdi-format-list-checks</v-icon>
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
            <v-card-text>Tem certeza que deseja deletar a viagem para {{selectedViagem.municipio_nome}} em {{selectedViagem.data_formated}}?</v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="dialogDelete = false">Cancelar</v-btn>
                <v-btn color="red darken-1" text @click="deleteItem(selectedIndex, true)" :loading="loading.delete">Excluir</v-btn>
            </v-card-actions>
        </v-card>
        </v-dialog>

        <!-- dialog edit / new -->
        <viagem-edit :viagem="selectedViagem" :dialogEdit.sync="dialogEdit" v-on:callback="callback"></viagem-edit>
        <viagem-list :viagem="selectedViagem" :dialogList.sync="dialogList" v-on:editViagem="dialogEdit = true"></viagem-list>

    </div>
</template>

<script>

    export default {
        
        props: ['default_date'],

        data: () => ({
            
            // main data
            viagens: [],
            api: window.__routes.api.viagem,

            // state of elements
            dialogDelete: false,
            dialogEdit: false,
            dialogList: false,
            loading: {
                list: false,
                delete: false,
            },
            formValid: true,
            popupParams: null,
            expandChip: false,

            // table column names
            headers: [
                { text: '#', value: 'id'},
                { text: 'Data', value: 'data_formated'},
                { text: 'Destino', value: 'municipio_nome'},
                { text: 'Veículo', value: 'veiculo'},
                { text: 'Motorista', value: 'motorista_nome'},
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
            selectedViagem: {
                id: null,
            },
            defaultItem: {
                id: null,
                cod_destino: null,
                data_viagem: null,
                hora_saida: null,
                id_motorista: null,
                id_unidade: null,
                id_veiculo: null,
                observacao: null
            },
            searchWord: null,
        }),

        mounted () {

            this.getItems();
            this.defaultItem.data_viagem = this.default_date;

        },

        methods: { 

        getItems: async function() {

            let vm        = this;
            let page      = vm.pagination.current;
            let urlFetch  = this.api+'?page='+page;

            if (vm.searchWord != null && vm.searchWord != '') urlFetch+= '&search='+vm.searchWord;

            vm.loading.list = true;

            await axios
                .get(urlFetch)
                .then(function(response) {
                    vm.viagens                = response.data.data;
                    vm.pagination.current     = response.data.current_page;
                    vm.pagination.total       = response.data.last_page;
                    vm.pagination.totalItems  = response.data.total;
                    vm.pagination.perPage     = response.data.per_page;
                    
                })
                .catch(function(error) {
                    vm.$toast.error('Erro ao buscar viagens')
                })
                .finally(function() {
                    vm.loading.list = false;
                });
            },

            /*
             * Callback para inserção ou edição de viagem
             * Após a edição, da um reload
             * Após inserção, pega o id da viagem inserida, dá um reload e já entra na edição de passageiros
             * @param id id da ultima viagem inserida
            */
           
            callback: async function (id) {
                
                this.pagination.current = 1;
                await this.getItems();

                // se estiver editando e Lista.vue está aberto, recarrega as informações da viagem 
                // porque se o usuário editar veículo vai afetar o controle de vagas disponíveis / lotação
                if (!id) {
                    if (this.dialogList) {
                        this.selectedViagem = this.viagens.find(
                            (viagem) => viagem.id == this.selectedViagem.id
                        )
                    }
                    return;
                }

                // se estiver inserindo, após inserir abre a tela de edição de passageiros
                let inserted = this.viagens.find((viagem) => viagem.id == id);
                if (typeof inserted === "object") {
                    this.selectedViagem = inserted;
                    this.selectedIndex = this.viagens.indexOf(inserted)
                    this.dialogList = true;
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
                    this.selectedViagem = item;
                    this.selectedIndex = this.viagens.indexOf(item);
                    this.dialogDelete = true
                    return;
                }

                let vm = this;
                vm.loading.delete = true;

                axios
                    .delete(this.api+'/'+this.selectedViagem.id)
                    .then(function(response){
                        if(response.data.error) {
                            vm.$toast.error('Erro ao deletar: '+response.data.error)
                        }
                        else {
                            vm.$toast.success('Viagem deletada com sucesso!')
                            vm.viagens.splice(vm.selectedIndex, 1)
                            // utilizado para carregar a paginação correta após adicionar
                            vm.pagination.totalItems--;
                            //se for último da paginação e tiver mais paginações, dá reload
                            if (vm.viagens.length == 0 && vm.pagination.current > 1){
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
                this.selectedViagem = Object.assign({}, this.defaultItem);
                this.selectedIndex = -1;
                this.dialogEdit = true;
            },

            // Abre o modal de edição a partir da coluna "Ações da tabela"
            editItem: function (item) {
                this.selectedIndex = this.viagens.indexOf(item);
                this.selectedViagem = Object.assign({}, item);
                this.dialogEdit = true;
            },

            listPassageiros: function(item) {
                this.selectedIndex = this.viagens.indexOf(item);
                this.selectedViagem = Object.assign({}, item);
                this.dialogList = true;
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