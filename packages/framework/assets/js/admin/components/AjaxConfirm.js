import ConfirmWindow from '@shopsys/administration/src/js/utils/confirmWindow';
import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';
import ConfirmDelete from './ConfirmDelete';

export default class AjaxConfirm {
    static bind() {
        const _this = this;
        $(this)
            .off('click.ajaxConfirm')
            .on('click.ajaxConfirm', function () {
                Ajax.ajax({
                    url: $(this).attr('href'),
                    context: this,
                    success: data => {
                        const $directDelete = $('<div>').append($.parseHTML(data)).find('.js-confirm-delete-direct');

                        if ($directDelete.length > 0) {
                            ConfirmWindow.show({
                                content: $directDelete.html(),
                                style: 'danger',
                                continueUrl: $directDelete.data('continue-url'),
                            });

                            return;
                        }

                        // eslint-disable-next-line no-new
                        new ModalWindow({
                            content: data,
                        });

                        // Wait for the modal to be shown, then register the content
                        $(document).on('shown.bs.modal', '.modal', function () {
                            const $modalContent = $(this).find('.modal-body');
                            new Register().registerNewContent($modalContent);

                            const onOpen = $(_this).data('ajax-confirm-on-open');
                            if (onOpen) {
                                // eslint-disable-next-line no-new
                                new ConfirmDelete();
                            }

                            // Remove the event listener to avoid multiple registrations
                            $(document).off('shown.bs.modal', '.modal');
                        });
                    },
                });

                return false;
            });
    }

    static init($container) {
        $container.filterAllNodes('a.js-ajax-confirm').each(AjaxConfirm.bind);
    }
}

new Register().registerCallback(AjaxConfirm.init, 'AjaxConfirm.init');
