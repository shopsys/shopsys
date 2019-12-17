import ToggleElement from '../common/components/toggleElement';
import Register from '../common/register';

export default class MailTemplate {

    constructor ($container) {
        $container.filterAllNodes('.js-mail-template-toggle-container.js-toggle-container').each(function () {
            const $toggleContainer = $(this);
            const $toggleButton = $toggleContainer.find('.js-toggle-button');

            $toggleContainer.on('showContent.toggleElement', () => $toggleButton.text('-'));
            $toggleContainer.on('hideContent.toggleElement', () => $toggleButton.text('+'));
        });

        $container.filterAllNodes('.js-mail-template-toggle-container.js-toggle-container:has(.js-validation-errors-list:not(.display-none))').each(function () {
            ToggleElement.show($(this));
        });

        $container.filterAllNodes('.js-send-mail-checkbox')
            .on('change.requiredFields', () => this.toggleRequiredFields())
            .trigger('change.requiredFields');
    }

    toggleRequiredFields () {
        const sendMail = $(this).is(':checked');
        $(this).closest('.js-mail-template').find('.js-form-compulsory').toggle(sendMail);
    }

    static init ($container) {
        // eslint-disable-next-line no-new
        new MailTemplate($container);
    }
}

(new Register()).registerCallback(MailTemplate.init);
