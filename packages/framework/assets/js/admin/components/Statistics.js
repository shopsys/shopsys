import Chart from 'chart.js';
import Register from '../../common/utils/Register';
import Translator from 'bazinga-translator';

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

    static lineChartForCron ($chartCanvas) {
        let originalLineDraw = Chart.controllers.line.prototype.draw;
        Chart.helpers.extend(Chart.controllers.line.prototype, {
            draw: function () {
                originalLineDraw.apply(this, arguments);

                let chart = this.chart;
                let ctx = chart.chart.ctx;

                let xaxis = chart.scales['x-axis-0'];
                let yaxis = chart.scales['y-axis-0'];

                let limits = [];

                let max = [];
                max['value'] = $chartCanvas.data('chart-timeout-secs');
                max['label'] = Translator.trans('Expected maximum duration');
                max['color'] = 'rgb(220, 61, 61)';
                limits.push(max);

                for (let i = 0; i < limits.length; i++) {
                    limits[i].value = yaxis.getPixelForValue(limits[i].value, undefined);
                    ctx.fillStyle = 'rgb(220, 61, 61)';
                    ctx.fillText(limits[i].label, 35, limits[i].value - 5);

                    ctx.save();
                    ctx.beginPath();
                    ctx.moveTo(xaxis.left, limits[i].value);
                    ctx.strokeStyle = limits[i].color;
                    ctx.lineTo(xaxis.right, limits[i].value);
                    ctx.stroke();
                    ctx.restore();
                }
            }
        });

        // eslint-disable-next-line no-new
        new Chart($chartCanvas, {
            type: 'line',
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
        $container.filterAllNodes('.js-cron-line-chart').each(function () {
            // eslint-disable-next-line no-new
            Statistics.lineChartForCron($(this));
        });
    }
}
(new Register()).registerCallback(Statistics.init, 'Statistics.init');
