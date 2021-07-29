import Register from 'framework/common/utils/Register';
import Translator from 'bazinga-translator';
import Window from '../utils/Window';

const PERSONAL_PICKUP_WINDOW_ID = '#js-order-transport-stores';
const TRANSPORT_PERSONAL_PICKUP_CHECKBOX_CLASS = '.js-transport-personal-pickup-checkbox';
const PERSONAL_PICKUP_STORE_RADIO_CLASS = '.personal-pickup-store';

export default class PersonalPickup {

    constructor (
        $personalPickupWindow,
        $transportPersonalPickupCheckboxes
    ) {
        this.$personalPickupWindow = $personalPickupWindow;
        this.cleanPersonalPickupWindowRadioValues();

        const transportPersonalPickupHiddenId = '#' + $transportPersonalPickupCheckboxes.data('hidden-id');
        const storeId = $(transportPersonalPickupHiddenId).val();

        if ($transportPersonalPickupCheckboxes.is(':checked') && storeId == 0) {
            $transportPersonalPickupCheckboxes.prop('checked', false);
        }

        $transportPersonalPickupCheckboxes.change((event) => {
            if ($(event.target).is(':checked')) {
                const currentCheckBoxHiddenId = '#' + $(event.target).data('hidden-id');
                if ($(currentCheckBoxHiddenId).val() == 0) {
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
        this.$personalPickupWindow.filterAllNodes(PERSONAL_PICKUP_STORE_RADIO_CLASS).prop('checked', false);
        $(PERSONAL_PICKUP_STORE_RADIO_CLASS).click(function () {
            $('.js-window-button-continue').removeClass('disabled');
        });
    }

    resetPersonalPickupId ($transportCheckbox) {
        const transportPersonalPickupHiddenId = '#' + $transportCheckbox.data('hidden-id');
        $(transportPersonalPickupHiddenId)
            .val(null)
            .trigger('orderRememberData.littleDelayedSaveData');
        this.resetCurrentCheckboxToOrigin($transportCheckbox);
    }

    savePersonalPickupStoreId ($transportCheckbox, $window) {
        if ($('.js-window-button-continue').hasClass('disabled')) {
            return false;
        }

        const transportPersonalPickupHiddenId = '#' + $transportCheckbox.data('hidden-id');
        const $personalPickupStoreRadio = $window.filterAllNodes(PERSONAL_PICKUP_STORE_RADIO_CLASS + ':checked');

        if ($personalPickupStoreRadio.length === 0) {
            $transportCheckbox.prop('checked', false).change();
            this.resetPersonalPickupId($transportCheckbox);
            return;
        }

        $(transportPersonalPickupHiddenId)
            .val($personalPickupStoreRadio.val())
            .trigger('orderRememberData.littleDelayedSaveData');

        const storeName = $personalPickupStoreRadio.data('store-name');
        const storeStreet = $personalPickupStoreRadio.data('store-street');
        const storeCity = $personalPickupStoreRadio.data('store-city');
        const storePostcode = $personalPickupStoreRadio.data('store-postcode');
        const storeOpeningHours = $personalPickupStoreRadio.data('store-openingHours');
        const storeAvailability = $personalPickupStoreRadio.data('store-availability');
        const address = `
            ${storeName} <br>
            ${storeStreet}, ${storePostcode} ${storeCity} <br>
            ${Translator.trans('Otevřeno')}: ${storeOpeningHours} <br>
            ${storeAvailability}
        `;
        this.setCurrentCheckboxDescription($transportCheckbox, address);
        $transportCheckbox.parents('.box-chooser__item').find('.box-chooser__item__title__description').addClass('full-width');

        $transportCheckbox.prop('checked', true).change();
    }

    setCurrentCheckboxDescription ($transportCheckbox, description) {
        $transportCheckbox.parents('.box-chooser__item').find('.box-chooser__item__title__description').html(description);
        $transportCheckbox.parents('.box-chooser__item').find('.js-chooser-delivery-information').hide();
    }

    resetCurrentCheckboxToOrigin ($transportCheckbox) {
        let originData = $transportCheckbox.parents('.box-chooser__item').find('.box-chooser__item__title__description').data('origin');
        let $description = `
            <div class="box-chooser__item__tooltip__wrap">
                <i class="box-chooser__item__tooltip__handler">i</i>
                <div class="box-chooser__item__tooltip">${originData}</div>
            </div>`;
        this.setCurrentCheckboxDescription(
            $transportCheckbox,
            $description
        );
        $transportCheckbox.parents('.box-chooser__item').find('.js-chooser-delivery-information').show();
        $transportCheckbox.parents('.box-chooser__item').find('.box-chooser__item__title__description').removeClass('full-width');
    }

    setupRadioPersonalPickupByHiddenStoreId ($transportCheckbox, $window) {

        const transportPersonalPickupHiddenId = '#' + $transportCheckbox.data('hidden-id');
        const storeId = $(transportPersonalPickupHiddenId).val();
        const $personalPickupStoreRadios = $window.filterAllNodes(PERSONAL_PICKUP_STORE_RADIO_CLASS);

        $personalPickupStoreRadios.each((index, element) => {
            if ($(element).val() === storeId) {
                $(element).prop('checked', true);
            }
        });
    }

    uncheckTransportCheckbox ($transportCheckbox) {
        const transportPersonalPickupHiddenId = '#' + $transportCheckbox.data('hidden-id');
        const storeId = $(transportPersonalPickupHiddenId).val();

        if (storeId === '') {
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
            cssClassContinue: 'disabled',
            eventContinue: () => this.savePersonalPickupStoreId($transportCheckbox, $window),
            eventOnLoad: () => this.setupRadioPersonalPickupByHiddenStoreId($transportCheckbox, $window),
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
