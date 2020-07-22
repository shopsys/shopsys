import Register from 'framework/common/utils/Register';

export default class PaymentTransportChooser {

    constructor (paymentTransportRelations, $groups) {
        this.$groups = $groups;
        this.$submitButton = $('.js-transport_and_payment_form_save');
        this.paymentTransportRelations = {};

        paymentTransportRelations.forEach(item => {
            if (this.paymentTransportRelations[item.paymentId] === undefined) {
                this.paymentTransportRelations[item.paymentId] = {};
            }
            this.paymentTransportRelations[item.paymentId][item.transportId] = true;
        });

        this.refreshGroupsState();
        this.refreshAvailablePayments();
        $groups.find('.js-payment-transport-checkbox').bind('change', (event) => this.refreshGroupsState());
        $groups.find('.js-order-transport-input').bind('change', (event) => this.refreshAvailablePayments());
        $groups.find('.js-payment-transport-change-button').bind('click', (event) => {
            this.unsetGroup($(event.target).closest('.js-payment-transport-group'));
        });
        this.$submitButton.click((event) => {
            if (this.$submitButton.hasClass('btn--disabled')) {
                event.preventDefault();
            }
        });
    }

    refreshGroupsState () {
        this.uncheckAllFollowingCheckboxes();
        this.hideOtherCheckboxesInGroupWhenOneIsChecked();
        this.toggleGroupsByCheckboxesState();
    }

    uncheckAllFollowingCheckboxes () {
        let hasPreviousGroupSelected = null;
        this.$groups.each((key, group) => {
            if (hasPreviousGroupSelected === false) {
                $(group).find('.js-payment-transport-checkbox')
                    .prop('checked', false)
                    .trigger('orderRememberData.littleDelayedSaveData')
                    .trigger('orderPreview.littleDelayedLoadOrderPreview');
            }
            hasPreviousGroupSelected = $(group).find('.js-payment-transport-checkbox:checked').length > 0;
        });

        this.$submitButton.toggleClass('btn--disabled', hasPreviousGroupSelected === false);
    }

    hideOtherCheckboxesInGroupWhenOneIsChecked () {
        this.$groups.each((key, group) => {
            const $checkboxContainers = $(group).find('.js-payment-transport-checkbox-container');
            if ($(group).find('.js-payment-transport-checkbox:checked').length > 0) {
                $checkboxContainers.each((key, container) => {
                    $(container).toggleClass('is-disabled', $(container).find('.js-payment-transport-checkbox:checked').length === 0);
                });
            } else {
                $checkboxContainers.removeClass('is-disabled');
            }
        });
    }

    toggleGroupsByCheckboxesState () {
        let hideNextGroups = false;
        this.$groups.each((key, group) => {
            const $changeButton = $(group).find('.js-payment-transport-change-button');
            $(group).toggleClass('is-disabled', hideNextGroups);

            if ($(group).find('.js-payment-transport-checkbox:checked').length > 0) {
                $changeButton.removeClass('display-none');
            } else {
                hideNextGroups = true;
                $changeButton.addClass('display-none');
            }
        });
    }

    unsetGroup ($group) {
        $group.find('.js-payment-transport-checkbox:checked')
            .prop('checked', false)
            .trigger('orderRememberData.littleDelayedSaveData')
            .trigger('orderPreview.littleDelayedLoadOrderPreview');
        this.refreshGroupsState();
    }

    refreshAvailablePayments () {
        const checkedTransportIds = this.$groups.find('.js-order-transport-input:checked').toArray().map(checkbox => {
            return $(checkbox).data('id');
        });

        this.$groups.find('.js-order-payment-input').each((key, checkbox) => {
            const $checkbox = $(checkbox);
            const paymentId = $checkbox.data('id');
            if (this.paymentTransportsRelationExists(paymentId, checkedTransportIds)) {
                $checkbox.prop('disabled', false);
                $checkbox.closest('label.box-chooser__item').removeClass('box-chooser__item--inactive');
            } else {
                $checkbox.prop('disabled', true);
                $checkbox.prop('checked', false);
                $checkbox.closest('label.box-chooser__item').addClass('box-chooser__item--inactive');
            }
        });
    }

    paymentTransportsRelationExists (paymentId, transportIds) {
        let exists = null;
        transportIds.forEach(transportId => {
            if (exists !== false && this.paymentTransportRelations[paymentId] !== undefined) {
                if (this.paymentTransportRelations[paymentId][transportId] !== undefined) {
                    exists = true;
                    return;
                }
            }
            exists = false;
        });

        return exists === true;
    }

    static init ($container) {
        $container.filterAllNodes('.js-payment-transport-relations').each(function () {
            // eslint-disable-next-line no-unused-vars
            const paymentTransportChooser = new PaymentTransportChooser(
                $(this).data('relations'),
                $container.filterAllNodes('.js-payment-transport-group')
            );
        });
    }
}

(new Register()).registerCallback(PaymentTransportChooser.init, 'PaymentTransportChooser.init');
