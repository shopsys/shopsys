import Register from '../../common/utils/Register';

export default class MailTemplate {

    constructor ($container) {
        $container.filterAllNodes('.js-send-mail-checkbox').on('change.requiredFields', this.toggleRequiredFields);
    }

    toggleRequiredFields (event) {
        const sendMail = $(event.target).is(':checked');
        $(event.target).closest('.js-mail-template').find('.js-form-compulsory').toggle(sendMail);
    }

    static init ($container) {
        // eslint-disable-next-line no-new
        new MailTemplate($container);
    }
}

(new Register()).registerCallback(MailTemplate.init, 'MailTemplate.init');
