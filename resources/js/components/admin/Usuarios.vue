<template>
    <div>
        <!-- dialog list -->
        <v-dialog v-model="dialogUsers" max-width="720px" @click:outside="close">
            <v-card>
                <v-card-title>
                    <v-row>
                        <v-col>
                            Usuários da unidade "{{unidade.descricao}}"
                            <v-btn class="ml-4" fab dark small color="primary">
                                <v-icon dark @click="addNew">mdi-plus</v-icon>
                            </v-btn>
                        </v-col>
                    </v-row>                        
                </v-card-title>
                <v-card-text>
                    <v-data-table
                        :headers="headers"
                        :items="usuarios"
                        hide-default-footer
                        disable-sort
                        no-data-text="Nenhum usuário nesta unidade ainda"
                        loading-text="Buscando dados..."
                        :loading="loading.list"
                    >
                        <template v-slot:item.status="{ item }">
                            <v-icon v-if="item.status == '1'" color="success">mdi-check</v-icon>
                            <v-icon v-else color="error">mdi-close</v-icon>
                        </template>
                        <!-- table actions -->
                        <template v-slot:item.action="{ item }">
                            <v-icon class="mr-2" @click="editItem(item)">mdi-pencil</v-icon>
                            <v-icon class="mr-2" @click="deleteItem(item, false)">mdi-delete</v-icon>
                            <v-icon @click="openResetPassword(item)" title="Resetar Senha">mdi-key</v-icon>
                        </template>
                    </v-data-table>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="close">Sair</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- dialog delete -->
        <v-dialog v-model="dialogDelete" max-width="290">
        <v-card>
            <v-card-title class="headline">Confirmar exclusão?</v-card-title>
            <v-card-text>Tem certeza que deseja deletar o usuário "{{ selectedItem.name }}"?</v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="dialogDelete = false">Cancelar</v-btn>
                <v-btn color="red darken-1" text @click="deleteItem(selectedIndex, true)" :loading="loading.delete">Excluir</v-btn>
            </v-card-actions>
        </v-card>
        </v-dialog>

        <!-- dialog reset password -->
        <v-dialog v-model="dialogResetPassword" max-width="290">
            <v-form v-model="formValidResetPassword" ref="formResetPassword">
                <v-card>
                    <v-card-title class="headline">Resetar senha</v-card-title>
                    <v-card-text>
                        <v-text-field 
                            v-model="newPassword" 
                            :label="'Nova Senha para '+selectedItem.name" 
                            required
                            type="password"
                            :rules="[v => !!v || 'Senha é obrigatória']"
                        ></v-text-field>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="blue darken-1" text @click="dialogResetPassword = false">Cancelar</v-btn>
                        <v-btn color="red darken-1" text @click="resetPassword()" :loading="loading.reset">Resetar Senha</v-btn>
                    </v-card-actions>
                </v-card>
            </v-form>
        </v-dialog>

        <!-- dialog edit / new -->
        <v-dialog v-model="dialogEdit" max-width="500px">
            <v-form v-model="formValid" ref="formEdit">
                <v-card>
                    <v-card-title>
                        <span class="headline">Usuário</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-alert type="warning" v-if="selectedItem.level == -1">
                                Este usuário tem nível máximo de permissão!
                            </v-alert>
                            <v-text-field 
                                v-model="selectedItem.name" 
                                label="Nome" 
                                required 
                                :rules="[v => !!v || 'Nome é obrigatório']"
                            ></v-text-field>
                            <v-text-field 
                                v-model="selectedItem.email" 
                                label="Email"
                                type="email" 
                                required 
                                :rules="[v => !!v || 'Email é obrigatório']"
                            ></v-text-field>
                            <v-text-field 
                                v-if="selectedIndex == -1"
                                v-model="selectedItem.password" 
                                label="Senha"
                                type="password" 
                                :rules="[v => !!v || 'Senha é obrigatório!']"
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

    </div>
</template>

<script>

    import {TheMask} from 'vue-the-mask'

    export default {
    
        props: {
            dialogUsers: {
                type: Boolean,
                required: true,
            },
            unidade: {
                type: Object,
                required: true
            }
        },

        data: () => ({

            // main data
            usuarios: [],
            api: window.__routes.api.usuario,

            headers: [
                { text: '#', value: 'id'},
                { text: 'Nome', value: 'name'},
                { text: 'Email', value: 'email'},
                { text: 'Status', value: 'status'},
                { text: 'Ações', value: 'action'},
            ],

            dialogDelete: false,
            dialogEdit: false,
            dialogResetPassword: false,
            formValid: true,
            formValidResetPassword: true,
            loading: {
                list: false,
                edit: false,
                delete: false,
                reset: false,
            },

            // CRUD variables
            selectedIndex: null,
            selectedItem: {},
            defaultItem: {
                status: '1',
                id_unidade: null,
                name: null,
                email: null
            },
            newPassword: null,
        }),

        methods: { 

            initialize () {

                this.usuarios = [];
                this.loading.list = true;
                let vm = this;

                axios
                    .get(vm.api+'?unidade='+vm.unidade.id)
                    .then(function(response) {
                        vm.usuarios = response.data
                    })
                    .catch((error) => console.log(error))
                    .finally(() => vm.loading.list = false)
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
                            Object.assign(vm.usuarios[vm.selectedIndex], vm.selectedItem)
                            vm.$toast.success('Usuário salvo com sucesso!')
                            vm.dialogEdit = false;
                        }
                    })
                    .finally(() => {vm.loading.edit = false;});

                } else {
                    axios
                    .post(this.api, {
                        'name'          : vm.selectedItem.name,
                        'status'        : vm.selectedItem.status,
                        'id_unidade'    : vm.unidade.id,
                        'email'         : vm.selectedItem.email,
                        'password'      : vm.selectedItem.password,
                    })
                    .then(function(response){
                        if(response.data.error) {
                            vm.$toast.error('Erro ao cadastrar: '+response.data.error)
                        }
                        else {
                            vm.initialize();
                            vm.$toast.success('Usuário cadastrado com sucesso!');
                            vm.dialogEdit = false;
                        }
                    })
                    .finally(() => {vm.loading.edit = false;});
                }
            },

            deleteItem: function (item, confirm) {
                
                if (!confirm) {
                    this.selectedItem = item;
                    this.selectedIndex = this.usuarios.indexOf(item);
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
                            vm.$toast.success('Usuário deletado com sucesso!')
                            vm.usuarios.splice(vm.selectedIndex, 1)
                        }
                    })
                .finally(function() {
                    vm.dialogDelete = false;
                    vm.loading.delete = false;
                });
            },

            resetPassword: function () {
                if (!this.$refs.formResetPassword.validate()) return;
                let vm = this;
                axios
                    .put(this.api+'/'+this.selectedItem.id, {
                        password: this.newPassword
                    })
                    .then(function(response){
                        if(response.data.error) {
                            vm.$toast.error('Erro ao resetar senha: '+response.data.error)
                        }
                        else {
                            vm.$toast.success('Senha resetada com sucesso!')
                            vm.dialogResetPassword = false;
                        }
                    })
                    .finally(() => {vm.loading.reset = false;});
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
                this.selectedIndex = this.usuarios.indexOf(item);
                this.selectedItem = Object.assign({}, item);
                this.resetEditValidation();
                this.dialogEdit = true;
            },

            openResetPassword: function (item) {
                this.newPassword = null;
                this.selectedItem = item;
                this.dialogResetPassword = true;
                this.resetPasswordValidation();
            },

            // $ref do formulario nao está disponivel na criação do componente
            resetEditValidation: function () {
                if(typeof this.$refs.formEdit != "undefined") this.$refs.formEdit.resetValidation();
            },

            resetPasswordValidation: function () {
                if(typeof this.$refs.formResetPassword != "undefined") this.$refs.formResetPassword.resetValidation();
            },

            close () {
                this.$emit('update:dialogUsers', false);
            },

        },

        watch: {
            dialogUsers: function(val) {
                if (val) this.initialize();
            }
        }
    }
</script>