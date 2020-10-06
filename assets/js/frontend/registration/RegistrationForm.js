import 'framework/common/components';
import Ajax from 'framework/common/utils/Ajax';
import Register from 'framework/common/utils/Register';
import Timeout from 'framework/common/utils/Timeout';

export default class RegistrationForm {

    constructor ($form) {
        this.$form = $form;
        this.$form.data('registration-form-instance', this);
        this.$emailInput = $form.find('#registration_form_email');
        this.$passwordContainer = $form.find('#js-registration-form-password-container');
        this.$activationInfo = $form.find('#js-registration-form-activation-info');
        this.$customerTypeSelector = $form.find('.js-registration-form-customer-type-selector');
        this.$commonCustomerButton = this.$customerTypeSelector.find('a[data-tab-id="common-customer"]:first');
        this.$companyCustomerButton = this.$customerTypeSelector.find('a[data-tab-id="company-customer"]:first');
        this.lastCheckedEmail = this.$emailInput.val();

        this.customerInfo = $form.data('customer-info');
        this.updateFormOptions();

        this.$emailInput
            .keyup(() => this.emailDelayedCheck())
            .change(() => this.emailCheck());
    }

    emailDelayedCheck () {
        Timeout.setTimeoutAndClearPrevious(
            'registrationForm.emailCheck',
            () => {
                this.emailCheck();
            },
            200
        );
    }

    emailCheck () {
        if (this.lastCheckedEmail === this.$emailInput.val()) {
            return;
        }
        this.lastCheckedEmail = this.$emailInput.val();

        Ajax.ajaxPendingCall('registrationForm.emailCheck', {
            loaderElement: '#registration_form_save',
            url: this.$form.data('email-check-url'),
            type: 'get',
            dataType: 'json',
            data: {
                email: this.lastCheckedEmail
            },
            success: (data) => {
                this.customerInfo = data;
                this.updateFormOptions();
            }
        });
    }

    updateFormOptions () {
        this.$customerTypeSelector.toggle(!this.customerInfo.exists || this.customerInfo.activated);

        if (this.customerInfo.exists && this.customerInfo.activated === false) {
            if (this.customerInfo.isCompanyCustomer) {
                this.$companyCustomerButton.click();
            } else {
                this.$commonCustomerButton.click();
            }
        }

        this.$passwordContainer.toggle(!this.customerInfo.exists || this.customerInfo.activated);
        this.$activationInfo.toggle(this.customerInfo.exists && !this.customerInfo.activated);
    }

    static init ($container) {
        $container.filterAllNodes('form[name=registration_form]').each(function () {
            // eslint-disable-next-line no-unused-vars
            const registrationForm = new RegistrationForm($(this));
        });
    }
}

(new Register()).registerCallback(RegistrationForm.init, 'RegistrationForm.init');
