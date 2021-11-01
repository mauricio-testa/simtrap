<template>
    <div>
        <v-navigation-drawer v-model="drawer" :clipped="$vuetify.breakpoint.lgAndUp" color="grey lighten-4" app>

            <v-list-item two-line class="py-3">
                <v-list-item-content>
                    <v-list-item-title>{{unidade.descricao}}</v-list-item-title>
                    <v-list-item-subtitle class="pt-1">{{unidade.nome}} - {{unidade.uf}}</v-list-item-subtitle>
                </v-list-item-content>
            </v-list-item>

            <v-divider></v-divider>
            
            <v-list dense>
                <template v-for="(item, i) in menus">

                    <!-- heading -->
                    <v-row
                        v-if="item.heading"
                        :key="item.heading"
                        align="center"
                        >
                        <v-col cols="6">
                            <v-subheader v-if="item.heading">
                            {{ item.heading }}
                            </v-subheader>
                        </v-col>
                    </v-row>

                    <!-- items -->
                    <v-list-item v-else
                        :key="i"
                        link
                        :href="item.url"
                        :class="{'v-list-item--active' : item.url == currentLocation}"
                    >
                        <v-list-item-action>
                            <v-icon>{{ item.icon }}</v-icon>
                        </v-list-item-action>
                        <v-list-item-content>
                            <v-list-item-title>
                            {{ item.text }}
                            </v-list-item-title>
                        </v-list-item-content>
                    </v-list-item> 

                </template>
            </v-list>
        </v-navigation-drawer>

        <!-- heading -->
        <v-app-bar :clipped-left="$vuetify.breakpoint.lgAndUp" app color="primary" dark dense>
            <v-app-bar-nav-icon @click.stop="drawer = !drawer" />
                <v-toolbar-title style="width: 300px" class="ml-0 pl-4">
                    <span class="hidden-sm-and-down">SIMTRAP </span>
                </v-toolbar-title>

            <v-spacer/>

            <div class="text-center">
                <v-menu
                    v-model="menu"
                    :close-on-content-click="false"
                    :nudge-width="240"
                    offset-y
                >
                    <template v-slot:activator="{ on }">
                        <v-btn icon large v-on="on">
                                <v-avatar size="32px" item color="red">
                                    <span class="white--text">{{ avatar }}</span>
                                </v-avatar>
                            </v-btn>
                    </template>

                    <v-card>

                        <v-list>
                            <v-list-item>
                                <v-list-item-avatar color="red">
                                    <span class="white--text">{{ avatar }}</span>
                                </v-list-item-avatar>
                                <v-list-item-content>
                                    <v-list-item-title>{{user.nome}}</v-list-item-title>
                                    <v-list-item-subtitle>Logado</v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                        </v-list>

                        <v-divider></v-divider>

                        <v-card-actions>
                            <v-btn color="primary" text @click="dialogResetPassword=true">alterar senha</v-btn>
                            <v-spacer></v-spacer>
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn v-on="on" color="primary" text @click="logout" >
                                        <v-icon class="ml-2">mdi-logout</v-icon>
                                    </v-btn>
                                </template>
                                <span>Logout</span>
                            </v-tooltip>
                        </v-card-actions>

                    </v-card>
                </v-menu>
            </div>
        </v-app-bar>

        <v-dialog v-model="dialogResetPassword" max-width="290">
            <v-form v-model="formValidResetPassword" ref="formResetPassword">
                <v-card>
                    <v-card-text>
                        <v-text-field 
                            v-model="newPassword" 
                            label="Digite a nova senha" 
                            type="password"
                            :rules="passwordRules"
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

    </div>
</template>

<script>
    export default {
        props: {
            menus: Array,
            user: Object,
            unidade: Object,
        },

        data: () => ({
            dialog: false,
            drawer: null,
            menu: false,

            dialogResetPassword: false,
            newPassword: null,
            formValidResetPassword: true,

            loading: {
                reset: false,
            },
        }),

        methods: {
            open: function(url){
                window.location = url
            },

            logout: function() {
                event.preventDefault();
                document.getElementById('logout-form').submit();
            },

            resetPassword: function() {
                if (!this.$refs.formResetPassword.validate()) return;
                let vm = this;
                axios
                    .put(window.__routes.api.usuario+'/'+this.user.id, {
                        password: vm.newPassword
                    })
                    .then(function(response){
                        if(response.data.error) {
                            vm.$toast.error('Erro ao resetar senha: '+response.data.error)
                        }
                        else {
                            vm.$toast.success('Senha alterada com sucesso!')
                            vm.dialogResetPassword = false;
                            vm.newPassword = null;
                        }
                    })
                    .finally(() => {vm.loading.reset = false;});
            }
        },

        computed: {

            // remover ultimo caracter se for /. Porque sim
            currentLocation () {
                let url = window.location.href;
                let lastChar = url.substr(url.length -1, url.length);
                if (lastChar == '/') return url.substr(0, url.length -1)
                return url;
            },

            avatar: function () {

                try {
                    let ar = this.user.nome.split(' ');
                    let avatar = ar[0].substr(0,1)
                    if (ar.length > 1) {
                        avatar += ar[ar.length-1].substr(0,1)
                    }
                    return avatar.toUpperCase();
                    
                } catch (error) {
                    return '  ';
                }    
            },
            passwordRules: function() {
                const rules = [];
                rules.push(v => (v || '').length > 5 || 'Senha deve conter 6 caracteres no mínimo')
                rules.push(v => (/[0-9]/).test(v) || 'Senha deve conter pelo menos um número')
                rules.push(v => (/[a-zA-Z]/).test(v) || 'Senha deve conter pelo menos uma letra')
                return rules;
            }
        }
    }
</script>