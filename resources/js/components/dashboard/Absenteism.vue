<template>
  <v-row class="pt-3">
    <v-col>
      <v-sheet elevation="2" class="d-flex py-5 align-center">
        <h1 class="px-5 indigo--text text--darken-2">{{absenteismo.absenteismo_mes}}</h1>
        <div class="d-flex flex-column">
          <span class="subtitle-2">Absenteísmo no mes</span>
          <span class="caption text--secondary">De {{absenteismo.total_mes}} pacientes, {{absenteismo.faltou_mes}} faltaram</span>
        </div>
      </v-sheet>
    </v-col>

    <v-col>
      <v-sheet elevation="2" class="d-flex py-5 align-center">
        <h1 class="px-5 blue--text text--darken-2">{{absenteismo.absenteismo_ano}}</h1>
        <div class="d-flex flex-column">
          <span class="subtitle-2">Absenteísmo no ano</span>
          <span class="caption text--secondary">De {{absenteismo.total_ano}} pacientes, {{absenteismo.faltou_ano}} faltaram</span>
        </div>
      </v-sheet>
    </v-col>
  </v-row>
</template>

<script>
export default {
  data: () => ({
    absenteismo: [],
  }),
  mounted() {
    this.absenteismo = this.$parent.data.absenteismo;
  }
};
</script>