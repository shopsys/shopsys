import $ from 'jquery';
import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import Translator from 'bazinga-translator';
import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';
import FormChangeInfo from './FormChangeInfo';

export default class MailTemplate {
    constructor($container) {
        this.$sendMailCheckbox = $container.filterAllNodes('.js-send-mail-checkbox');
        this.$sendMailCheckbox.on('change.requiredFields', this.toggleRequiredFields);
        this.$sendMailCheckbox.trigger('change.requiredFields');

        $container.filterAllNodes('.js-mail-template-open-send-window').on('click', this.openSendWindow);
    }

    toggleRequiredFields(event) {
        const isSendMail = $(event.target).is(':checked');
        const $conditionalFields = $(event.target).closest('.js-mail-template').find('.js-conditional-field');

        $conditionalFields.toggleClass('required', isSendMail);
    }

    openSendWindow(event) {
        event.preventDefault();
        event.stopPropagation();

        const $button = $(event.currentTarget);

        // Prevent multiple clicks while loading
        if ($button.data('loading')) {
            return false;
        }

        if (FormChangeInfo.isInfoShown) {
            // eslint-disable-next-line no-new
            new ModalWindow({
                content: Translator.trans('You have unsaved changes, save them first, please.'),
            });
        } else {
            $button.data('loading', true);

            Ajax.ajax({
                loaderElement: $button,
                url: $button.data('url'),
                type: 'GET',
                success: data => {
                    // Check if a modal is already open and close it
                    $('.modal.show').modal('hide');

                    // Wait for any existing modal to fully close before opening new one
                    setTimeout(() => {
                        const modalInstance = new ModalWindow({
                            content: data,
                            size: 'lg',
                        });

                        // Bind form submission handler after modal is shown
                        modalInstance.element.one('shown.bs.modal', function () {
                            const $form = $(this).find('form');
                            if ($form.length > 0) {
                                $form.on('submit', function (e) {
                                    e.preventDefault();
                                    MailTemplate.submitSendForm($(this));
                                    return false;
                                });
                            }
                        });

                        // Reset loading state when modal is hidden
                        modalInstance.element.one('hidden.bs.modal', () => {
                            $button.data('loading', false);
                        });
                    }, 200);
                },
                error: () => {
                    $button.data('loading', false);
                },
            });
        }

        return false;
    }

    static submitSendForm($form) {
        const $errorsContainer = $('.js-mail-template-send-errors');
        $errorsContainer.hide();

        const $submitButton = $form.find('button[type=submit]');

        Ajax.ajax({
            url: $form.attr('action'),
            data: $form.serialize(),
            type: $form.attr('method'),
            dataType: 'json',
            loaderElement: $submitButton,
            success: data => {
                if (data.result === 'valid') {
                    document.location.reload();
                } else if (data.result === 'invalid') {
                    const $errorsList = $errorsContainer.show().find('ul');
                    $errorsList.find('li').remove();
                    data.errors.forEach(error => {
                        $errorsList.append(`<li>${error}</li>`);
                    });
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
