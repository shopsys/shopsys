import Chart from 'chart.js';
import Register from '../common/register';

export default class Statistics {

    constructor ($chartCanvas) {
        // eslint-disable-next-line no-new
        new Chart($chartCanvas, {
            type: 'bar',
            data: {
                labels: $chartCanvas.data('chart-labels'),
                datasets: [{
                    data: $chartCanvas.data('chart-values'),
                    backgroundColor: 'rgba(0, 155, 217, 0.2)',
                    borderColor: 'rgb(0, 155, 217)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                },
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: $chartCanvas.data('chart-title')
                }
            }
        });
    }

    static init ($container) {
        $container.filterAllNodes('.js-line-chart').each(function () {
            // eslint-disable-next-line no-new
            new Statistics($(this));
        });
    }
}
(new Register()).registerCallback(Statistics.init);
