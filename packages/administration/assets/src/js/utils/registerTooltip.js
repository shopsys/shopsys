import { Tooltip } from '@tabler/core';
import $ from 'jquery';

export default function registerTooltip() {
    $.fn.tooltip = function (options = {}) {
        const defaults = {
            title: '',
            placement: 'top',
        };

        const settings = $.extend({}, defaults, options);

        return this.each(function () {
            this.setAttribute('data-bs-toggle', 'tooltip');
            this.setAttribute('data-bs-title', settings.title);
            this.setAttribute('data-bs-placement', settings.placement);

            new Tooltip(this);
        });
    };
}
