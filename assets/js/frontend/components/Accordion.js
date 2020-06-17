/**
 * Accordion Component
 */

import Responsive from '../utils/Responsive';
import Register from 'framework/common/utils/Register';

export default class Accordion {

    constructor ($accordion) {
        this.$accordion = $accordion;

        const _this = this;

        this.$accordion.find('.js-accordion-header').on('click', function(e) {
            e.preventDefault();
            const $accordionToggle = $(this).find('.js-accordion-toggle-text');
            const accordionToggleText = $(this).parent('.js-accordion').hasClass('is-opened')
                ? $accordionToggle.data('text-more')
                : $accordionToggle.data('text-less');

            $(this).parents('.js-accordion').toggleClass('is-opened');
            $accordionToggle.text(accordionToggleText);
        })
    }

    toggleContent () {
        console.log('tralala');
    }

    static init ($container) {
        $container.filterAllNodes('.js-accordion').each((index, element) => {
            // eslint-disable-next-line no-new
            new Accordion($(element));
        });
    }
}

(new Register()).registerCallback(Accordion.init, 'Accordion.init');
