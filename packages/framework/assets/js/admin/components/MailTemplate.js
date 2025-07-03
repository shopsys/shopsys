import Translator from 'bazinga-translator';
import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';
import Window from '../utils/Window';
import FormChangeInfo from './FormChangeInfo';

export default class MailTemplate {
    constructor($container) {
        $container.filterAllNodes('.js-send-mail-checkbox').on('change.requiredFields', this.toggleRequiredFields);
        $container.filterAllNodes('.js-mail-template-open-send-window').on('click', this.openSendWindow);
        $container
            .filterAllNodes('#mail_template_send_form_save')
            .closest('form')
            .submit(function () {
                MailTemplate.submitSendForm($(this));
                return false;
            });
    }

    toggleRequiredFields(event) {
        const sendMail = $(event.target).is(':checked');
        $(event.target).closest('.js-mail-template').find('.js-form-compulsory').toggle(sendMail);
    }

    openSendWindow(event) {
        const $button = $(event.target);
        if (FormChangeInfo.isInfoShown) {
            // eslint-disable-next-line no-new
            new Window({
                content: Translator.trans('You have unsaved changes, save them first, please.'),
            });
        } else {
            Ajax.ajax({
                loaderElement: $button,
                url: $button.data('url'),
                type: 'GET',
                success: data => {
                    // eslint-disable-next-line no-new
                    new Window({
                        content: data,
                        wide: true,
                    });
                },
            });
        }

        return false;
    }

    static submitSendForm($form) {
        const $errorsContainer = $('.js-mail-template-send-errors');
        $errorsContainer.hide();
        Ajax.ajax({
            url: $form.attr('action'),
            data: $form.serialize(),
            type: $form.attr('method'),
            dataType: 'json',
            loaderElement: $form,
            success: data => {
                if (data.result === 'valid') {
                    document.location.reload();
                } else if (data.result === 'invalid') {
                    const $errorsList = $errorsContainer.show().find('ul');
                    $errorsList.find('li').remove();
                    for (const i in data.errors) {
                        $errorsList.append(`<li>${data.errors[i]}</li>`);
                    }
                }
            },
        });
    }

    static init($container) {
        // eslint-disable-next-line no-new
        new MailTemplate($container);
    }
}

new Register().registerCallback(MailTemplate.init, 'MailTemplate.init');
