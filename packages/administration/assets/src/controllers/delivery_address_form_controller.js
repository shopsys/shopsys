import { Controller } from '@hotwired/stimulus';

/*
 * Disables/enables delivery address fields when “same as billing address” changes.
 * Used by deliveryAddressForm.html.twig.
 */
export default class extends Controller {
    static targets = ['sameAsBillingAddress', 'deliveryFields'];

    connect() {
        this.updateDeliveryFieldsState();
    }

    updateDeliveryFieldsState() {
        const disabled = this.sameAsBillingAddressTarget.checked;

        this.deliveryFieldsTarget.classList.toggle('opacity-50', disabled);
        this.deliveryFieldsTarget.querySelectorAll('input, select, textarea').forEach(field => {
            field.disabled = disabled;

            if (field.tomselect) {
                field.tomselect[disabled ? 'disable' : 'enable']();
            }
        });
    }
}
