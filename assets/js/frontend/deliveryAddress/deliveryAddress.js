import Register from 'framework/common/utils/Register';
import Ajax from 'framework/common/utils/Ajax';
import Window from '../utils/Window';
import Translator from 'bazinga-translator';

export default class DeliveryAddress {
    onRemove ($this, deliveryAddress) {
        const $row = $this.closest('.js-delivery-address-row');
        const $input = $row.find('.js-delivery-address-input');

        // eslint-disable-next-line no-new
        new Window({
            content: Translator.trans('Do you really want to remove this delivery address?'),
            buttonCancel: true,
            buttonContinue: true,
            eventContinue: () => {
                const deliveryAddressId = $input.val();
                if (deliveryAddressId > 0) {
                    Ajax.ajax({
                        overlayDelay: 0,
                        loaderElement: '#js-delivery-address-fields',
                        url: $this.data('href'),
                        type: 'get',
                        success: function () {
                            deliveryAddress.deleteSuccessMessage();
                            $this.closest('.js-delivery-address-row').remove();
                        },
                        error: function () {
                            deliveryAddress.deleteErrorMessage();
                        }
                    });
                }
            }
        });
    }

    deleteSuccessMessage () {
        return new Window({
            content: Translator.trans('Delivery address has been removed.'),
            buttonContinue: false,
            textCancel: Translator.trans('Ok')
        });
    }

    deleteErrorMessage () {
        return new Window({
            content: Translator.trans('Delivery address could not be removed.'),
            buttonContinue: false,
            textCancel: Translator.trans('Ok')
        });
    }

    onChange ($input) {
        const $orderDeliveryAddressFields = $('.js-order-delivery-address-fields');

        if ($orderDeliveryAddressFields !== 'undefined') {
            if ($input.val() == '') {
                $orderDeliveryAddressFields.show();
            } else {
                $orderDeliveryAddressFields.hide();
            }
        }
    }

    static init ($container) {
        const $deliveryAddressItem = $container.filterAllNodes('.js-delivery-address-row');
        const $deliveryAddressRemove = $container.filterAllNodes('.js-delivery-address-remove-button');
        const $deliveryAddressOrderInput = $container.filterAllNodes('.js-delivery-address-input');
        const deliveryAddress = new DeliveryAddress();

        $deliveryAddressRemove.click((event) => deliveryAddress.onRemove($(event.currentTarget), deliveryAddress));
        $deliveryAddressOrderInput.change((event) => deliveryAddress.onChange($(event.currentTarget)));

        $deliveryAddressItem.on('click', function (event) {
            $deliveryAddressItem.removeClass('active');
            $(event.currentTarget).addClass('active');
        });
    }
}

(new Register()).registerCallback(DeliveryAddress.init);
