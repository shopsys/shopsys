import $ from 'jquery';
import Register from '../copyFromFw/register';

/*!
 *
 * Inspired: Responsive and Mobile-Friendly Tooltip
 * https://osvaldas.info/elegant-css-and-jquery-tooltip-responsive-mobile-friendly
 *
 */

const initTooltip = function (target, tooltip) {
    if ($(window).width() < tooltip.outerWidth() * 1.5) {
        tooltip.css('max-width', $(window).width() / 2);
    } else {
        tooltip.css('max-width', 340);
    }

    let posLeft = target.offset().left + (target.outerWidth() / 2) - (tooltip.outerWidth() / 2);
    let posTop = target.offset().top - tooltip.outerHeight() - 20;

    if (posLeft < 0) {
        posLeft = target.offset().left + target.outerWidth() / 2 - 20;
        tooltip.addClass('left');
    } else {
        tooltip.removeClass('left');
    }

    if (posLeft + tooltip.outerWidth() > $(window).width()) {
        // posLeft = target.offset().left - tooltip.outerWidth() + target.outerWidth() / 2 + 20;
        tooltip.addClass('right');
    } else {
        tooltip.removeClass('right');
    }

    if (posTop < 0) {
        // posTop = target.offset().top + target.outerHeight();
        tooltip.addClass('top');
    } else {
        tooltip.removeClass('top');
    }
};

export default function responsiveTooltip () {
    const targets = $('.form-error__icon');
    let target = false;
    let tooltip = false;

    targets.bind('mouseenter', function () {
        target = $(this);
        tooltip = $('.form-error__list');
        initTooltip(target, tooltip);
        $(window).resize(() => initTooltip(target, tooltip));

    });
}

(new Register()).registerCallback(responsiveTooltip);
