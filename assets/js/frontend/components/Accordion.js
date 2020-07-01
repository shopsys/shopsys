import Responsive from '../utils/Responsive';
import Register from 'framework/common/utils/Register';

export default class Accordion {

    constructor ($accordion) {
        this.$accordion = $accordion;

        const _this = this;

        this.$accordion.find('.js-accordion-header').on('click', function (e) {
            e.preventDefault();
            _this.toggleAccordionContent($(this));
        });

        if (this.$accordion.hasClass('js-accordion-closed-on-mobile') && Responsive.isMobileVersion()) {
            this.$accordion.removeClass('is-opened');
        }
    }

    toggleAccordionContent ($accordion) {
        const $accordionToggle = $accordion.find('.js-accordion-toggle-text');
        const accordionToggleText = $accordion.parent('.js-accordion').hasClass('is-opened')
            ? $accordionToggle.data('text-more')
            : $accordionToggle.data('text-less');

        $accordion
            .parents('.js-accordion')
            .toggleClass('is-opened');

        $accordionToggle.text(accordionToggleText);
    }

    static init ($container) {
        $container.filterAllNodes('.js-accordion').each((index, element) => {
            // eslint-disable-next-line no-new
            new Accordion($(element));
        });
    }
}

(new Register()).registerCallback(Accordion.init, 'Accordion.init');
