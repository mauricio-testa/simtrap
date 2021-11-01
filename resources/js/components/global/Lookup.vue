<template>  
</template>

<script>
    export default {
        
        data: () => ({

            lookupLimit: 500
        
        }),

        methods: { 

            getLookup (dataset, search) {

                let api = null;
                let vm  = this;
                let qs  = '?limit='+this.lookupLimit;
                qs += typeof search == 'undefined' || search == null ? '' : '&search='+search;              

                if (dataset == 'VEICULO')   api = window.__routes.api.veiculo;
                if (dataset == 'MOTORISTA') api = window.__routes.api.motorista;
                if (dataset == 'MUNICIPIO') api = window.__routes.api.municipio;
                if (dataset == 'PACIENTE')  api = window.__routes.api.paciente;

                if (dataset == 'PACIENTE') {
                    qs += '&only_active=1'
                }

                axios
                    .get(api+qs)
                    .then(function(response) {

                        if (response.data.error) {
                            console.log('error', response.data.error)
                            vm.$emit('updateLookup', dataset, []);
                        } else {
                            vm.$emit('updateLookup', dataset, response.data.data)
                        }
                    })
                    .catch(function(error) {
                        console.log(error)
                        vm.$emit('updateLookup', dataset, [])
                    })
            },
        },
    }
</script>