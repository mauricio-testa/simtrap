<script>

import { Bar } from "vue-chartjs";

export default {

    extends: Bar,

    props: ["data"],

    data: () => ({

        labels: [],

        datasets: [
            {
                label: "Compareceu",
                borderColor: "transparent",
                data: [40, 39, 10, 40, 39, 80, 40],
                pointBackgroundColor: "#00796b",
                backgroundColor: "rgba(0, 121, 107, 0.6)",
                pointRadius: 0,
                stack: '1',
            },
            {
                label: "Não compareceu",
                borderColor: "transparent",
                pointBackgroundColor: "#303f9f",
                backgroundColor: "#E57373",
                data: [20, 19, 16, 30, 30, 20, 10],
                pointRadius: 0,
                stack: '1',
            },
            {
                label: "Não Lançado",
                borderColor: "transparent",
                pointBackgroundColor: "#1976d2",
                data: [60, 69, 30, 70, 133, 90, 70],
                backgroundColor: "#CFD8DC",
                pointRadius: 0,
                stack: '1',
            },
            {
                label: "Pacientes",
                borderColor: "transparent",
                pointBackgroundColor: "#1976d2",
                data: [60, 69, 30, 70, 133, 90, 70],
                backgroundColor: "rgba(25, 118, 210, 0.6)",
                pointRadius: 0,
                stack: '2',
            },
            {
                label: "Acompanhantes",
                borderColor: "transparent",
                pointBackgroundColor: "#1976d2",
                data: [60, 69, 30, 70, 133, 90, 70],
                backgroundColor: "rgba(48, 63, 159, .6)",
                pointRadius: 0,
                stack: '2',
            }
        ],

        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: "bottom",
                usePointStyle: true,
                labels: {
                    fontSize: "14",
                    fontFamily: "'Roboto', 'sans-serif'"
                }
            },
            scales: {
                xAxes: [
                    {
                        gridLines: { display: false },
                        ticks: {
                            fontSize: "14",
                            fontFamily: "'Roboto', 'sans-serif'"
                        },
                    }
                ],
                yAxes: [
                    {
                        gridLines: { display: false },
                        ticks: {
                            fontSize: "14",
                            fontFamily: "'Roboto', 'sans-serif'"
                        },
                        stacked: true
                    }
            ]
            },
            /* only line
            tooltips: {
                //mode: 'index',
                intersect: false,
                backgroundColor: "#000",
                xPadding: 10,
                yPadding: 10,
                cornerRadius: 2,
                titleMarginBottom: 10,
                bodySpacing: 20,
                displayColors: true
            },
            hover: {
                intersect: false
            }
            */
        }
    }),

    mounted() {

        this.labels = this.data.labels;
        this.datasets[0].data = this.data.data.compareceu;
        this.datasets[1].data = this.data.data.nao_compareceu;
        this.datasets[2].data = this.data.data.pendente;
        this.datasets[3].data = this.data.data.pacientes;
        this.datasets[4].data = this.data.data.acompanhantes;

        this.renderChart({
            labels: this.labels,
            datasets: this.datasets
        },this.options);
    }
};
</script>