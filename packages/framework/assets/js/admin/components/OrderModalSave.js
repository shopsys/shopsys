import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import { Modal, Toast } from '@tabler/core';
import Translator from 'bazinga-translator';
import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';

/**
 * Handles AJAX-saving of order detail edit modals (personal, addresses, note, tracking, withdrawal).
 *
 * Each modal contains a form whose `data-save-url` points to OrderController::saveSectionAction.
 * On success the controller returns JSON with `viewHtml` (rendered section view) and `summaryHtml`
 * (rendered top summary bar). This class:
 *   1. Closes the modal
 *   2. Replaces the summary bar (#js-order-summary-bar)
 *   3. Replaces the section view (#js-{section}-view, e.g. #js-addresses-view)
 *   4. Shows a success toast
 *
 * The section identifier is derived from the save URL (the part after "/save/").
 *
 * Forms that opt in via `data-reload-on-success` skip the in-place update and trigger a
 * full page reload instead - used when saving may add/remove tabs (e.g. switching status to
 * "withdrawn" reveals the withdrawal tab on the next render).
 */
export default class OrderModalSave {
    static init($container) {
        $container.filterAllNodes('.js-modal-save').on('click', function () {
            const $btn = $(this);
            const $form = $($btn.data('form'));
            const saveUrl = $form.data('save-url');

            $btn.addClass('btn-loading').prop('disabled', true);

            Ajax.ajax({
                url: saveUrl,
                type: 'POST',
                data: $form.serialize(),
                dataType: 'json',
                success: response => {
                    if (response.success) {
                        // Some sections cannot patch the page in place because saving may
                        // structurally change the layout (e.g. adding a tab). The form opts in
                        // via data-reload-on-success and we hard-reload to render the new state.
                        // Forms can also declare data-reload-hash to land the user on a specific
                        // tab after the reload (otherwise the previous hash would re-activate
                        // a tab that no longer reflects the new order state).
                        if ($form.data('reload-on-success')) {
                            const reloadHash = $form.data('reload-hash');

                            if (reloadHash) {
                                window.location.hash = reloadHash;
                            }

                            window.location.reload();

                            return;
                        }

                        const $modal = $btn.closest('.modal');
                        const modalInstance = Modal.getInstance($modal[0]);

                        if (modalInstance) {
                            modalInstance.hide();
                        }

                        if (response.summaryHtml) {
                            const $summary = $('#js-order-summary-bar');
                            $summary.html(response.summaryHtml);
                            new Register().registerNewContent($summary);
                        }

                        if (response.viewHtml) {
                            const section = saveUrl.split('/save/').pop();
                            const $view = $(`#js-${section}-view`);

                            if ($view.length) {
                                $view.html(response.viewHtml);
                                new Register().registerNewContent($view);
                            }
                        }

                        if (response.formHtml) {
                            $form.html(response.formHtml);
                            new Register().registerNewContent($form);
                        }

                        OrderModalSave.showSuccessToast();
                        $btn.removeClass('btn-loading').prop('disabled', false);
                    } else {
                        if (response.formHtml) {
                            $form.html(response.formHtml);
                            new Register().registerNewContent($form);
                        }
                        $btn.removeClass('btn-loading').prop('disabled', false);
                    }
                },
                error: () => {
                    // eslint-disable-next-line no-new
                    new ModalWindow({
                        content: Translator.trans('Error occurred, try again please.'),
                    });
                    $btn.removeClass('btn-loading').prop('disabled', false);
                },
            });
        });
    }

    static showSuccessToast(message) {
        let $container = $('.js-toast-container');

        if (!$container.length) {
            $container = $(
                '<div class="toast-container position-fixed start-50 translate-middle-x js-toast-container"></div>',
            ).appendTo('body');
        }

        const toastHtml = `
            <div class="toast bg-success text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="8000">
                <div class="toast-body d-flex justify-content-between align-items-center">
                    <span>${message || Translator.trans('Saved successfully')}</span>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        const $toast = $(toastHtml).appendTo($container);
        const toast = new Toast($toast[0]);
        toast.show();

        $toast[0].addEventListener('hidden.bs.toast', () => $toast.remove());
    }
}

new Register().registerCallback(OrderModalSave.init, 'OrderModalSave.init');
