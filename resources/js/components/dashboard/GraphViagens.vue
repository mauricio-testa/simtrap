<script>

import { Line } from 'vue-chartjs';

export default {

    extends: Line,

    props: ['data'],

    data: () => ({

        labels: [],

        datasets: [
            {
                label: 'Viagens',
                borderColor: '#512da8',
                data: [],
                pointBackgroundColor: '#512da8',
                backgroundColor: '#fff',
                pointRadius: 0,
            },
        ],

        options: {
            maintainAspectRatio: false, 
            legend: {
                display: false
            },
            scales: {
                xAxes: [
                    { 
                        gridLines: { 
                            display: false 
                        }, 
                        ticks: {
                            fontSize: '14',
                            fontFamily: "'Roboto', 'sans-serif'"
                        }
                    }
                ],
                yAxes: [
                    {
                        gridLines: {
                            display: false
                        }, 
                        ticks: {
                            display: false
                        }
                    }
                ]
            },
            tooltips: {
                intersect: false,
                backgroundColor: '#000',
                xPadding: 10,
                yPadding: 10,
                cornerRadius: 2,
                titleMarginBottom: 10,
                bodySpacing: 20,
                displayColors: true
            },
            hover: {
                intersect: false
            },
        }
    }),

    mounted () {

        this.labels = this.data.labels;
        this.datasets[0].data = this.data.data;
        
        this.renderChart({
            labels: this.labels,
            datasets: this.datasets,
        }, this.options)
    }
};
</script>