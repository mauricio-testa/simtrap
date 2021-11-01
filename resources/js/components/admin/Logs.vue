<template>
    <div>
        <v-card class="ma-md-2 px-2 ma-xs-0">

            <!-- table header -->
            <v-card-title>
                <v-row>
                <v-col cols="12" md="4">
                    Logs
                </v-col>
                <v-col cols="12" md="8">
                    <v-form @submit.prevent="search()" class="d-flex">
                    <v-select class="mr-2" :items="filters.context" v-model="get.context" label="Context"></v-select>
                    <v-text-field class="mr-2" type="number" v-model="get.context_id" label="ID"></v-text-field>
                    <v-select class="mr-2" :items="filters.level" v-model="get.level" label="Level"></v-select>
                    <v-select class="mr-2" :items="filters.action" v-model="get.action" label="Action"></v-select>
                    <v-text-field class="mr-2" type="number" v-model="get.id_user" label="User"></v-text-field>
                    <v-icon class="mr-2" @click="search()">mdi-magnify</v-icon>
                    <v-icon class="mr-2" @click="resetSearch()">mdi-close</v-icon>
                    </v-form>
                </v-col>
                </v-row>                        
            </v-card-title>

            <v-row>
                <v-col :cols="this.loading.list ? '12' : '8'">
                    <!-- table  -->
                    <v-data-table
                        :headers="headers"
                        :items="logs"
                        :loading="loading.list"
                        hide-default-footer
                        disable-sort
                        no-data-text="Nenhum dado encontrado."
                        loading-text="Buscando dados..."
                    >
                        <template v-slot:item.action="{ item }">
                            <v-chip dark small :class="getChipClass(item.action)">{{item.action}}</v-chip>
                        </template>

                        <template v-slot:item.level="{ item }">
                            <v-chip dark small :class="getChipClass(item.level)">{{item.level}}</v-chip>
                        </template>
                        
                        <template v-slot:item.see="{ item }">
                            <v-icon outlined @click="explanation(item)" :class="{'blue--text': item.id == selectedItem.id}">mdi-eye</v-icon>
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
                </v-col>
                <v-col cols="4" v-if="logs.length > 0 && !loading.list">
                    <span class="title d-block text-capitalize"> {{selectedItem.context}} #{{selectedItem.context_id}}</span>
                    <span class="caption text--secondary">{{ selectedItem.created_at }}</span>
                    <v-divider></v-divider>
                    <v-chip-group>
                        <v-chip dark small :class="getChipClass(selectedItem.level)">{{selectedItem.level}}</v-chip>
                        <v-chip dark small :class="getChipClass(selectedItem.action)">{{selectedItem.action}}</v-chip>
                        <v-chip dark small>{{selectedItem.name}}</v-chip>
                        <v-chip dark small>Unidade {{selectedItem.id_unidade}}</v-chip>
                    </v-chip-group>
                    <v-divider></v-divider>
                    <div class="subtitle-2 my-3"> {{ selectedItem.message }} </div>
                    <code class="pa-4 pr-8">
                        <span>{{selectedItem.payload | format}}</span>
                    </code>
                    
                </v-col>
            </v-row>
        
    </v-card>
    </div>
</template>

<script>

    export default {
    
        data: () => ({
            
            // main data
            logs: [],
            api: window.__routes.api.log,
            selectedItem: {},
            selectedPayload: {},

            loading: {
                list: false,
            },

            filters: {
                context: ['lista', 'viagens'],
                level: ['CRUD', 'INFO', 'WARNING', 'EXCEPTION'],
                action: ['INSERT', 'UPDATE', 'DELETE', 'EXPORT'],
            },
            get: {
                context: null,
                context_id: null,
                level: null,
                action: null,
                id_user: null
            },
            defaultGet: {
                context: null,
                context_id: null,
                level: null,
                action: null,
                id_user: null
            },

            // table column names
            headers: [
                { text: '#', value: 'id'},
                { text: 'Contexto', value: 'context'},
                { text: 'Level', value: 'level'},
                { text: 'Ação', value: 'action'},
                { text: 'Usuário', value: 'name'},
                { text: 'Mensagem', value: 'message'},
                { text: 'Ver', value: 'see'},
            ],

            // server side pagination
            pagination: {
                current: 1,
                total: 0,
                totalItems: 0,
                perPage: 0,
            },

        }),

        mounted() {

            this.getItems();

        },

        methods: {

            getItems: function() {

                let vm        = this;
                let page      = vm.pagination.current;
                let urlFetch  = this.api+'?page='+page;

                urlFetch+= '&context='+vm.get.context;
                urlFetch+= '&context_id='+vm.get.context_id;
                urlFetch+= '&level='+vm.get.level;
                urlFetch+= '&action='+vm.get.action;
                urlFetch+= '&id_user='+vm.get.id_user;

                vm.loading.list = true;

                axios
                    .get(urlFetch)
                    .then(function(response) {
                        vm.logs                   = response.data.data;
                        vm.pagination.current     = response.data.current_page;
                        vm.pagination.total       = response.data.last_page;
                        vm.pagination.totalItems  = response.data.total;
                        vm.pagination.perPage     = response.data.per_page;
                        if (vm.logs.length > 0) {
                            vm.selectedItem = vm.logs[0]
                        }
                    })
                    .catch(function(error) {
                        vm.$toast.error('Erro ao buscar logs')
                    })
                    .finally(() => { vm.loading.list = false; });
            },

            getChipClass (label) {
                if (label == 'INSERT') return 'teal';
                if (label == 'UPDATE') return 'light-blue';
                if (label == 'DELETE') return 'deep-orange';
                if (label == 'CRUD') return 'blue-grey darken-1';
                if (label == 'EXCEPTION') return 'deep-orange';
                if (label == 'INFO') return 'light-blue';
                if (label == 'WARNING') return 'orange';
            },

            explanation (item) {
                this.selectedItem = item
            },

            // Inicia uma pesquisa
            search: function () {
                this.pagination.current = 1;
                this.getItems();
            },

            // Limpa a pesquisa
            resetSearch: function () {
                this.pagination.current = 1;
                this.get = Object.assign({}, this.defaultGet);
                this.getItems();
            },

            // A cada clique na paginação, recarrega a lista. Se tivre filtro vai persistir
            onPageChange: function () {
                this.getItems();
            },
        },
        filters: {
            format (json) {
                if (!json) return;
                let openKey = json.replace(/{/g,'{\n   ');
                let closeKey = openKey.replace(/}/g,'\n  }');
                let wrap = closeKey.replace(/,/g,', \n  ');
                return wrap;
            }
        }
    }
</script>