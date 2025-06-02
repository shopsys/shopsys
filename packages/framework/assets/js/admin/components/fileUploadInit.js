import { coloris, init } from '@melloware/coloris';

document.addEventListener('DOMContentLoaded', function () {
    init();
    coloris({
        el: '.js-coloris',
        selectInput: false,
        alpha: false,
        format: 'hex'
    });
});
