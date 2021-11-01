<template>
  <div>
    <v-row>
      <v-col v-for="(stat, key) in stats" :key="key" cols="12" xs="1" sm="6" md="3" >
        <v-card  tile height="90">
          <v-list-item class="pa-0" >
            <v-list-item-icon :class="stat.color" class="d-flex justify-center ma-0" style="width: 91px; height: 91px">
                <v-icon size="42" dark>{{stat.icon}}</v-icon>
                </v-list-item-icon>
            <v-list-item-content class="px-2">
              <v-list-item-title class="display-1 text-center"> {{ stat.value }} </v-list-item-title>
              <v-list-item-subtitle class="text-center pb-2">{{ stat.title }}</v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script>

  export default {
    data: () => ({
      stats: [
        {icon: 'mdi-ambulance', title: 'Listas Cadastradas', 'value' : 0, color: 'blue darken-2'},
        {icon: 'mdi-account-details', title: 'Pacientes em Listas', 'value' : 0, color: 'indigo darken-2'},
        {icon: 'mdi-account-multiple-outline', title: 'Pacientes com Acompanhantes', 'value' : 0, color: 'teal darken-2'},
        {icon: 'mdi-percent', title: 'Média de Passageiros por Viagem', 'value' : 0, color: ' deep-purple darken-2'}
      ]
    }),
    
    mounted() {
      this.stats[0].value = this.$parent.data.statistics.total_viagens;
      this.stats[1].value = this.$parent.data.statistics.total_pacientes_transportados
      this.stats[2].value = this.$parent.data.statistics.indice_pacientes_precisa_acompanhante
      this.stats[3].value = this.$parent.data.statistics.media_passageiro_por_viagem
    },
  }
</script>