import Register from 'framework/common/utils/Register';
import Translator from 'bazinga-translator';
import Window from '../utils/Window';

const PERSONAL_PICKUP_WINDOW_ID = '#js-order-transport-stocks';
const TRANSPORT_PERSONAL_PICKUP_CHECKBOX_CLASS = '.js-transport-personal-pickup-checkbox';
const PERSONAL_PICKUP_STOCK_RADIO_CLASS = '.personal-pickup-stock';

export default class PersonalPickup {

    constructor (
        $personalPickupWindow,
        $transportPersonalPickupCheckboxes
    ) {
        this.$personalPickupWindow = $personalPickupWindow;
        this.cleanPersonalPickupWindowRadioValues();

        const transportPersonalPickupHiddenId = '#' + $transportPersonalPickupCheckboxes.data('hidden-id');
        const stockId = $(transportPersonalPickupHiddenId).val();

        if ($transportPersonalPickupCheckboxes.is(':checked') && stockId == 0) {
            $transportPersonalPickupCheckboxes.prop('checked', false);
        }

        $transportPersonalPickupCheckboxes.change((event) => {
            if ($(event.target).is(':checked')) {
                if ($(transportPersonalPickupHiddenId).val() == 0) {
                    $(event.target).prop('checked', false);
                    this.openPersonalPickupWindow($(event.target));
                }
            } else {
                this.resetPersonalPickupId($(event.target));
            }
        });

        $('.js-payment-transport-change-button').bind('click', (event) => {
            const $group = $(event.target).closest('.js-payment-transport-group');

            this.unsetPersonalPickupInCurrentGroup($group);
            this.unsetPersonalPickupInCurrentGroup($group.nextAll('.js-payment-transport-group'));
        });
    }

    unsetPersonalPickupInCurrentGroup ($group) {
        $group.find('.js-transport-personal-pickup-checkbox:checked').each((index, element) => {
            this.resetPersonalPickupId($(element));
        });
    }

    cleanPersonalPickupWindowRadioValues () {
        this.$personalPickupWindow.filterAllNodes(PERSONAL_PICKUP_STOCK_RADIO_CLASS).prop('checked', false);
        $(PERSONAL_PICKUP_STOCK_RADIO_CLASS).click(function () {
            $('.window-button-continue').removeClass('window-popup__actions__btn--continue--disabled');
        });
    }

    resetPersonalPickupId ($transportCheckbox) {
        const transportPersonalPickupHiddenId = '#' + $transportCheckbox.data('hidden-id');
        $(transportPersonalPickupHiddenId)
            .val(null)
            .trigger('orderRememberData.littleDelayedSaveData');
        this.resetCurrentCheckboxToOrigin($transportCheckbox);
    }

    savePersonalPickupStockId ($transportCheckbox, $window) {
        if ($('.window-popup__actions__btn--continue').hasClass('window-popup__actions__btn--continue--disabled')) {
            return false;
        }

        const transportPersonalPickupHiddenId = '#' + $transportCheckbox.data('hidden-id');
        const $personalPickupStockRadio = $window.filterAllNodes(PERSONAL_PICKUP_STOCK_RADIO_CLASS + ':checked');

        if ($personalPickupStockRadio.length === 0) {
            $transportCheckbox.prop('checked', false).change();
            this.resetPersonalPickupId($transportCheckbox);
            return;
        }

        $(transportPersonalPickupHiddenId)
            .val($personalPickupStockRadio.val())
            .trigger('orderRememberData.littleDelayedSaveData');

        const address = $personalPickupStockRadio.data('stock-name') + ' ' + $personalPickupStockRadio.data('stock-street') + ' ' + $personalPickupStockRadio.data('stock-city');
        this.setCurrentCheckboxDescription($transportCheckbox, address);

        $transportCheckbox.prop('checked', true).change();
    }

    setCurrentCheckboxDescription ($transportCheckbox, description) {
        $transportCheckbox.parents('.box-chooser__item').find('.box-chooser__item__title__description').text(description);
    }

    resetCurrentCheckboxToOrigin ($transportCheckbox) {
        this.setCurrentCheckboxDescription(
            $transportCheckbox,
            $transportCheckbox.parents('.box-chooser__item').find('.box-chooser__item__title__description').data('origin')
        );
    }

    setupRadioPersonalPickupByHiddenStockId ($transportCheckbox, $window) {

        const transportPersonalPickupHiddenId = '#' + $transportCheckbox.data('hidden-id');
        const stockId = $(transportPersonalPickupHiddenId).val();
        const $personalPickupStockRadios = $window.filterAllNodes(PERSONAL_PICKUP_STOCK_RADIO_CLASS);

        $personalPickupStockRadios.each((index, element) => {
            if ($(element).val() === stockId) {
                $(element).prop('checked', true);
            }
        });
    }

    uncheckTransportCheckbox ($transportCheckbox) {
        const transportPersonalPickupHiddenId = '#' + $transportCheckbox.data('hidden-id');
        const stockId = $(transportPersonalPickupHiddenId).val();

        if (stockId === '') {
            $transportCheckbox.prop('checked', false).change();
        }
    }

    openPersonalPickupWindow ($transportCheckbox) {
        const window = new Window({
            content: this.$personalPickupWindow.html(),
            buttonContinue: true,
            buttonClose: true,
            buttonCancel: true,
            textContinue: Translator.trans('Potvrdit'),
            textClose: Translator.trans('Zavřít'),
            textCancel: Translator.trans('Zavřít okno'),
            cssClass: 'window-popup--wide window-popup--personal-pickup',
            cssClassContinue: 'window-popup__actions__btn--continue--disabled',
            eventContinue: () => this.savePersonalPickupStockId($transportCheckbox, $window),
            eventOnLoad: () => this.setupRadioPersonalPickupByHiddenStockId($transportCheckbox, $window),
            eventCancel: () => this.uncheckTransportCheckbox($transportCheckbox),
            eventClose: () => this.uncheckTransportCheckbox($transportCheckbox)
        });
        const $window = window.getWindow();
    }

    static init ($container) {
        const $personalPickupWindow = $container.filterAllNodes(PERSONAL_PICKUP_WINDOW_ID);
        const $transportPersonalPickupCheckboxes = $container.filterAllNodes(TRANSPORT_PERSONAL_PICKUP_CHECKBOX_CLASS);

        // eslint-disable-next-line no-unused-vars
        const personalPickup = new PersonalPickup(
            $personalPickupWindow,
            $transportPersonalPickupCheckboxes
        );
    }
}

(new Register()).registerCallback(PersonalPickup.init, 'PersonalPickup.init');
