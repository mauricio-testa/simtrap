<template>
    <v-card>
        <v-card-text>
        <v-simple-table :fixed-header="true" :height="195">
            <template v-slot:default>
            <thead>
                <tr>
                <th>Data</th>
                <th>Destino</th>
                <th>Veículo</th>
                <th>Passag.</th>
                <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="viagem in viagens" :key="viagem.id">
                    <td>{{viagem.data_formated}}</td>
                    <td>{{viagem.municipio_nome}}</td>
                    <td>{{viagem.veiculo}}</td>
                    <td>{{viagem.passageiros}}</td>
                    <td><v-icon 
                        @click="$openPopup('./relatorios/lista/'+viagem.id, 'lista')">mdi-printer-pos
                    </v-icon></td>
                </tr>
            </tbody>
            </template>
        </v-simple-table>
    </v-card-text>
</v-card>
    
</template>

<script>
export default {
    data: () => ({
        viagens: [],
        popupParams: null,
    }),
    mounted () {
        this.viagens = this.$parent.data.proximas_viagens;
    },
  }
</script>