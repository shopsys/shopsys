import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import Ajax from 'framework/common/utils/Ajax';
import Register from 'framework/common/utils/Register';

export default class McpManualToken {
    constructor($container) {
        $container.filterAllNodes('.js-mcp-open-manual-token-window').on('click', this.openWindow);
    }

    openWindow(event) {
        event.preventDefault();
        event.stopPropagation();

        const $button = $(event.currentTarget);

        if ($button.data('loading')) {
            return false;
        }

        $button.data('loading', true);

        Ajax.ajax({
            loaderElement: $button,
            url: $button.data('url'),
            type: 'GET',
            success: data => {
                const modalInstance = new ModalWindow({
                    content: data,
                    size: 'lg',
                    closeOnBackdropAndEscape: false,
                });

                new Register().registerNewContent(modalInstance.element);

                modalInstance.element.one('shown.bs.modal', function () {
                    const $form = $(this).find('form');

                    if ($form.length > 0) {
                        McpManualToken.bindExpirationPreset($form);

                        $form.on('submit', function (submitEvent) {
                            submitEvent.preventDefault();
                            McpManualToken.submitForm($(this), modalInstance);

                            return false;
                        });
                    }
                });

                modalInstance.element.one('hidden.bs.modal', () => {
                    $button.data('loading', false);
                });
            },
            error: () => {
                $button.data('loading', false);
            },
        });

        return false;
    }

    static bindExpirationPreset($form) {
        const $expirationPreset = $form.find('.js-mcp-manual-token-expiration-preset');
        const $customExpirationRow = $form.find('[data-js-mcp-manual-token-custom-expiration]');
        const $customExpirationInput = $customExpirationRow.find('input');

        if ($expirationPreset.length === 0 || $customExpirationRow.length === 0 || $customExpirationInput.length === 0) {
            return;
        }

        const toggleCustomExpiration = () => {
            const isCustomSelected = $expirationPreset.val() === 'custom';

            $customExpirationRow.toggle(isCustomSelected);
            $customExpirationInput.prop('disabled', !isCustomSelected);
        };

        $expirationPreset.on('change', toggleCustomExpiration);
        toggleCustomExpiration();
    }

    static submitForm($form, modalInstance) {
        const $errorsContainer = $('.js-mcp-manual-token-errors');
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
                    modalInstance.element.find('.modal-body').html(data.content);
                    modalInstance.element.one('hidden.bs.modal', () => {
                        document.location.reload();
                    });
                    new Register().registerNewContent(modalInstance.element);
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
        new McpManualToken($container);
    }
}

new Register().registerCallback(McpManualToken.init, 'McpManualToken.init');
