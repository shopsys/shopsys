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
            $(this).parents($accordion).toggleClass('is-opened');
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
