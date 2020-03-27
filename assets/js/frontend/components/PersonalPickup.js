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

        $transportPersonalPickupCheckboxes.change((event) => {
            if ($(event.target).is(':checked')) {
                this.openPersonalPickupWindow($(event.target));
            } else {
                this.resetPersonalPickupId($(event.target));
            }
        });

        $('.js-payment-transport-change-button').bind('click', (event) => {
            let $group = $(event.target).closest('.js-payment-transport-group');
            this.unsetPersonalPickupInCurrentGroup($group);
            $group.nextAll('.js-payment-transport-group').each((index, element) => {
                this.unsetPersonalPickupInCurrentGroup($(element));
            });
        });
    }

    unsetPersonalPickupInCurrentGroup ($group) {
        let $transportCheckbox = $group.find('.js-payment-transport-checkbox:checked');
        if ($transportCheckbox.hasClass('js-transport-personal-pickup-checkbox')) {
            this.resetPersonalPickupId($transportCheckbox);
        }
    }

    cleanPersonalPickupWindowRadioValues () {
        this.$personalPickupWindow.filterAllNodes(PERSONAL_PICKUP_STOCK_RADIO_CLASS)
            .each((index, element) => {
                $(element).prop('checked', false);
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

        const transportPersonalPickupHiddenId = '#' + $transportCheckbox.data('hidden-id');
        const $personalPickupStockRadio = $window.filterAllNodes(PERSONAL_PICKUP_STOCK_RADIO_CLASS + ':checked');

        $(transportPersonalPickupHiddenId)
            .val($personalPickupStockRadio.val())
            .trigger('orderRememberData.littleDelayedSaveData');

        const address = $personalPickupStockRadio.data('stock-name') + ' ' + $personalPickupStockRadio.data('stock-street') + ' ' + $personalPickupStockRadio.data('stock-city');
        this.setCurrentCheckboxDescription($transportCheckbox, address);
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
            textContinue: Translator.trans('Vybrat'),
            textClose: Translator.trans('Zavřít'),
            textCancel: Translator.trans('Zavřít'),
            cssClass: 'window-popup--wide',
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
