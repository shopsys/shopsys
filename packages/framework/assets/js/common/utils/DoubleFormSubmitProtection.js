const doubleFormSubmitProtectionAttribute = 'submit-protection';
const RESET_PROTECTION_TIME = 1500;

export default class DoubleFormSubmitProtection {

    protection (event) {
        const $form = $(event.target);

        if ($form.attr(doubleFormSubmitProtectionAttribute) === 'true') {
            event.stopImmediatePropagation();
            event.preventDefault();
            return;
        }

        $form.attr(doubleFormSubmitProtectionAttribute, true);

        setTimeout(() => {
            $form.attr(doubleFormSubmitProtectionAttribute, false);
        }, RESET_PROTECTION_TIME);
    }
}
