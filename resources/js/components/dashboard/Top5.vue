<template>
    <div>
        <v-row>
            <v-col xs="12" md="4" v-for="(rank, i) in ranks" :key="i">
                <v-sheet class="subtitle-1 my-4">Top 5 {{rank.title}}</v-sheet>
                <v-card>

                    <v-list :class="rank.color" dark>
                        <v-list-item>
                            <v-list-item-icon>
                                <v-icon size="42">mdi-trophy</v-icon>
                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title>#1 {{rank.rank[0].descricao}}</v-list-item-title>
                                <v-list-item-subtitle>{{rank.rank[0].total_viagens}} viagens</v-list-item-subtitle>
                            </v-list-item-content>
                        </v-list-item>
                    </v-list>

                    <v-list dense v-if="rank.rank.length > 1">
                        <v-list-item v-for="(item, z) in rank.rank" :key="z" :class="{'d-none': z == 0}">
                            <v-list-item-icon>
                                <span class="subtitle-2 text--secondary"># {{z+1}}</span>
                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title>{{item.descricao}}</v-list-item-title>
                                <v-list-item-subtitle>{{item.total_viagens}} viagens</v-list-item-subtitle>
                            </v-list-item-content>
                        </v-list-item>
                    </v-list>

                </v-card>
            </v-col>
        </v-row>
    </div>
</template>
<script>
export default {
    data: () => ({
      ranks: [
        {icon: 'mdi-account', title: 'Veículos', 'value' : 100, color: 'blue darken-2', 
            rank: [{descricao: 'Adicione uma viagem para ver o rank ;)', total_viagens: '0'}]
        },
        {icon: 'mdi-account', title: 'Motoristas', 'value' : 100, color: 'indigo darken-2', 
            rank: [{descricao: 'Adicione uma viagem para ver o rank ;)', total_viagens: '0'}]
        },
        {icon: 'mdi-account', title: 'Destinos', 'value' : 100, color: 'teal darken-2', 
            rank: [{descricao: 'Adicione uma viagem para ver o rank ;)', total_viagens: '0'}]
        },
      ],
    }),
    mounted() {
      this.ranks[2].rank = this.$parent.data.top_destinos
      this.ranks[1].rank = this.$parent.data.top_motoristas
      this.ranks[0].rank = this.$parent.data.top_veiculos
    },
}
</script>