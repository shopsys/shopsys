import $ from 'jquery';
import Translator from 'bazinga-translator';
import { Chart } from 'chart.js/auto';
import Register from '../../common/utils/Register';

const cronTimeoutLinePlugin = {
    id: 'cronTimeoutLine',
    afterDraw: (chart, _args, options) => {
        if (!options?.value) {
            return;
        }

        const { ctx } = chart;
        const xAxis = chart.scales.x;
        const yAxis = chart.scales.y;
        const yPosition = yAxis.getPixelForValue(options.value);

        ctx.save();
        ctx.fillStyle = options.color;
        ctx.fillText(options.label, 35, yPosition - 5);
        ctx.beginPath();
        ctx.moveTo(xAxis.left, yPosition);
        ctx.strokeStyle = options.color;
        ctx.lineTo(xAxis.right, yPosition);
        ctx.stroke();
        ctx.restore();
    },
};

export default class Statistics {
    constructor($chartCanvas) {
        // eslint-disable-next-line no-new
        new Chart($chartCanvas[0], {
            type: 'bar',
            data: {
                labels: $chartCanvas.data('chart-labels'),
                datasets: [
                    {
                        data: $chartCanvas.data('chart-values'),
                        backgroundColor: 'rgba(0, 155, 217, 0.2)',
                        borderColor: 'rgb(0, 155, 217)',
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                scales: {
                    y: {
                        ticks: {
                            beginAtZero: true,
                        },
                    },
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    title: {
                        display: false,
                    },
                },
            },
        });
    }

    static lineChartForCron($chartCanvas) {
        // eslint-disable-next-line no-new
        new Chart($chartCanvas[0], {
            type: 'line',
            data: {
                labels: $chartCanvas.data('chart-labels'),
                datasets: [
                    {
                        data: $chartCanvas.data('chart-values'),
                        backgroundColor: 'rgba(0, 155, 217, 0.2)',
                        borderColor: 'rgb(0, 155, 217)',
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                scales: {
                    y: {
                        ticks: {
                            beginAtZero: true,
                        },
                    },
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    title: {
                        display: true,
                        text: $chartCanvas.data('chart-title'),
                    },
                    cronTimeoutLine: {
                        value: $chartCanvas.data('chart-timeout-secs'),
                        label: Translator.trans('Expected maximum duration'),
                        color: 'rgb(220, 61, 61)',
                    },
                },
            },
            plugins: [cronTimeoutLinePlugin],
        });
    }

    static init($container) {
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
new Register().registerCallback(Statistics.init, 'Statistics.init');
