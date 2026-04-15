import ConfirmWindow from '@shopsys/administration/src/js/utils/confirmWindow';
import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import { Modal } from '@tabler/core';
import Translator from 'bazinga-translator';
import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';

const WITHDRAWN_STATUS_TYPE = 'withdrawn';

export default class OrderStatusDropdown {
    static init($container) {
        $container.filterAllNodes('.js-order-status-dropdown .dropdown-item').on('click', function (event) {
            event.preventDefault();

            const $item = $(this);

            if ($item.hasClass('active')) {
                return;
            }

            const statusId = String($item.data('status-id'));
            const statusType = $item.data('status-type');
            const statusName = $.trim($item.text());
            const $dropdown = $item.closest('.js-order-status-dropdown');

            ConfirmWindow.show({
                content: Translator.trans('Do you really want to change order status to <strong>%status%</strong>?', {
                    status: statusName,
                }),
                style: 'warning',
                continueEvent: () => {
                    OrderStatusDropdown.changeStatus($dropdown, $item, statusId, statusType);
                },
            });
        });
    }

    static changeStatus($dropdown, $item, statusId, statusType) {
        // The "withdrawn" status requires the withdrawal request payload to be filled in
        // through OrderStatusFormType. Skip the instant change endpoint and open the
        // dedicated modal so the form-level validation runs before persistence.
        if (statusType === WITHDRAWN_STATUS_TYPE) {
            OrderStatusDropdown.openWithdrawalModal(statusId);

            return;
        }

        $item.addClass('disabled');

        Ajax.ajax({
            url: $dropdown.data('save-url'),
            type: 'POST',
            data: {
                statusId: statusId,
                routeCsrfToken: $dropdown.data('csrf-token'),
            },
            dataType: 'json',
            success: response => {
                if (response.success) {
                    location.reload();

                    return;
                }

                if (response.requiresWithdrawalModal) {
                    OrderStatusDropdown.openWithdrawalModal(statusId);
                    $item.removeClass('disabled');

                    return;
                }

                // eslint-disable-next-line no-new
                new ModalWindow({
                    content: Translator.trans('Status change failed. Please try again.'),
                });
                $item.removeClass('disabled');
            },
            error: () => {
                // eslint-disable-next-line no-new
                new ModalWindow({
                    content: Translator.trans('Error occurred, try again please.'),
                });
                $item.removeClass('disabled');
            },
        });
    }

    static openWithdrawalModal(statusId) {
        const modalElement = document.getElementById('withdrawalEditModal');

        if (!modalElement) {
            // eslint-disable-next-line no-new
            new ModalWindow({
                content: Translator.trans('Withdrawal request data is required to switch the order to this status.'),
            });

            return;
        }

        const $statusInput = $('#js-withdrawal-modal-form').find('[data-js-order-status-select]');

        if ($statusInput.length) {
            // Triggering "change" lets OrderWithdrawal reveal the required-fields group when
            // the order is being switched into the withdrawn state from another status.
            $statusInput.val(statusId).trigger('change');
        }

        Modal.getOrCreateInstance(modalElement).show();
    }
}

new Register().registerCallback(OrderStatusDropdown.init, 'OrderStatusDropdown.init');
