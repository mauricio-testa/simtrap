<template>
  <div>

    <v-sheet class="headline my-2">Dashboard</v-sheet>
    
    <!-- estatisticas -->
    <dashboard-stats></dashboard-stats>

    <!-- welcome -->
    <div v-if="newUser">
      <v-alert outlined color="blue" class="mt-3">
        Olá! Estamos muito felizes em saber que você está utilizando o sistema SIMTRAP :)
      </v-alert>
      <v-alert outlined color="blue">
        Nós preparamos para você um dashboard com várias informações e estatísticas muito interessantes! <br>
        Porém, ele somente será exibido por completo quando você adicionar uma viagem e, pelo menos, um passageiro nela. 
        Não se esqueça de antes realizar o cadastro dos veículos, motoristas e pacientes! - Utilize os menus da esquerda para acessar eles.<br>
      </v-alert>
      <v-btn block color="secondary" dark href="./viagens">Adicionar a Primeira Viagem</v-btn>
    </div>

    <div v-else>

      <v-row>
        <!--gratico pacientes transportados -->
        <v-col sm="12" md="6">
          <v-sheet class="subtitle-1 my-4">Transporte de Pacientes por Mês nos Últimos 6 Mêses</v-sheet>
          <v-card>
            <v-card-text>
              <dashboard-graph-pacientes :data="data.total_passageiros_mes" style="height: 340px"></dashboard-graph-pacientes>
            </v-card-text>
          </v-card>
          
          <dashboard-absenteism></dashboard-absenteism>

        </v-col>

        <v-col sm="12" md="6">
          <!-- listagem proximas viagens -->
          <v-sheet class="subtitle-1 my-4">Proximas Viagens</v-sheet>
          <dashboard-next-viagens></dashboard-next-viagens>

          <!-- grafico viagens por mes -->
          <v-sheet class="subtitle-1 my-4">Viagens por Mês Neste Ano</v-sheet>
          <v-card>
            <v-card-text>
              <dashboard-graph-viagens :data="data.total_viagens_mes" style="height: 165px"></dashboard-graph-viagens>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- top 5 veiculos, motoristas, destinos -->
      <dashboard-top5></dashboard-top5>

    </div>
  </div>
</template>

<script>
export default {
  props: ["data"],
  computed: {
    newUser() {
      return this.data.statistics.total_pacientes_transportados == 0 || this.data.statistics.total_viagens == 0;
    }
  }
};
</script>