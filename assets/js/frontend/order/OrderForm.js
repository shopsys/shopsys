import 'framework/common/components';
import Ajax from 'framework/common/utils/Ajax';
import Register from 'framework/common/utils/Register';
import Timeout from 'framework/common/utils/Timeout';
import Window from '../utils/Window';
import Translator from 'bazinga-translator';

export default class OrderForm {

    constructor ($form) {
        this.$form = $form;
        this.$form.data('order-form-instance', this);
        this.$submitContainer = $form.find('#js-order-form-submit-container');
        this.$emailInput = $form.find('#order_personal_info_form_email');
        this.$emailInputLabel = $form.find('#js-user-email-label');
        this.$registerContainerWrap = $form.find('#js-order-form-register-container-wrap');
        this.$registerContainer = $form.find('#js-order-form-register-container');
        this.$registerContainerLabel = this.$registerContainer.find('label.css-checkbox__image');
        this.$registerCheckbox = $form.find('#order_personal_info_form_register');
        this.$passwordContainer = $form.find('#js-order-form-password-container');
        this.$activationInfo = $form.find('#js-order-form-activation-info');
        this.$customerTypeSelector = $form.find('#js-order-form-customer-type-selector');
        this.$customerTypeSelectorHeading = $form.find('#js-order-form-customer-type-selector-heading');
        this.$commonCustomerButton = this.$customerTypeSelector.find('a[data-tab-id="common-customer"]');
        this.$companyCustomerButton = this.$customerTypeSelector.find('a[data-tab-id="company-customer"]');
        this.$loginEmail = $form.find('#js-order-form-login-email');
        this.$loginContainer = $form.find('#js-order-form-login-container');
        this.$loginButton = $form.find('#js-order-form-login-button');
        this.$loginWindowContent = $form.find('#js-order-form-login-window-content');
        this.$detailsContainer = $form.find('#js-order-form-details-container');
        this.lastCheckedEmail = this.$emailInput.val();

        this.customerInfo = $form.data('customer-info');
        this.updateFormOptions();

        this.$loginButton.click((event) => { event.preventDefault(); this.showLoginForm(); });
        this.$registerCheckbox.change(() => this.updatePasswordContainer());
        this.$emailInput
            .keyup(() => this.emailDelayedCheck())
            .change(() => this.emailCheck());
    }

    showLoginForm () {
        const window = new Window({
            content: this.$loginWindowContent.html(),
            textHeading: Translator.trans('Zadejte heslo'),
            cssClassHeading: 'window-popup__heading',
            cssClass: 'window-popup--standard window-popup--password'
        });

        const $loginPasswordInput = window.$window.find('.js-order-form-login-password');
        const $loginSubmitButton = window.$window.find('.js-order-form-login-submit');

        $loginPasswordInput.keypress((event) => {
            if (event.keyCode === 13) {
                this.tryLogin($loginPasswordInput.val());
            }
        });
        $loginSubmitButton.click(() => this.tryLogin($loginPasswordInput.val()));
    }

    tryLogin (password) {
        $('body').addClass('force-overlay');
        $('.js-window-content .js-order-form-login-error').addClass('display-none');
        Ajax.ajax({
            loaderElement: '.js-window-content',
            url: this.$form.data('login-url'),
            type: 'post',
            dataType: 'json',
            data: {
                email: this.$emailInput.val(),
                password: password
            },
            success: (data) => {
                $('body').removeClass('force-overlay');
                if (data.success === true) {
                    window.location = window.location.href;
                } else {
                    $('.js-window-content .js-order-form-login-error')
                        .html('')
                        .append($($.parseHTML('<h2 class="window-popup__heading"/>')).text(data.errorHeader))
                        .append($($.parseHTML('<p/>')).text(data.errorMessage))
                        .removeClass('display-none');
                }
            }
        });
    }

    emailDelayedCheck () {
        Timeout.setTimeoutAndClearPrevious(
            'orderForm.emailCheck',
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

        Ajax.ajaxPendingCall('orderForm.emailCheck', {
            loaderElement: '#order_personal_info_form_save',
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
        this.$registerContainerWrap.toggle(!this.customerInfo.activated);
        this.$registerContainer.toggle(!this.customerInfo.activated);
        this.$customerTypeSelector.toggleClass('display-none', this.customerInfo.exists);
        this.$customerTypeSelectorHeading.toggleClass('display-none', this.customerInfo.exists);
        this.$loginContainer.toggle(this.customerInfo.activated);
        this.$loginEmail.find('input').toggleClass('form-input-success', this.customerInfo.exists);

        if (this.customerInfo.exists) {
            this.$registerContainerLabel.text(Translator.trans('Chci aktivovat uživatelský účet'));
        } else {
            this.$registerContainerLabel.text(Translator.trans('Chci se registrovat s objednávkou'));
        }

        if (this.customerInfo.exists) {
            if (this.customerInfo.isCompanyCustomer) {
                this.$companyCustomerButton.click();
            } else {
                this.$commonCustomerButton.click();
            }
        }

        this.updatePasswordContainer();

        const EmailValidator = new SymfonyComponentValidatorConstraintsEmail();
        const email = this.$emailInput.val() || '';
        const emailsErrors = EmailValidator.validate(email);
        const showFullForm = email.length > 0 && Array.isArray(emailsErrors) && emailsErrors.length === 0;
        if (showFullForm) {
            this.$emailInputLabel.text(Translator.trans('Váš e-mail'));
            this.$detailsContainer
                .slideDown(400)
                .animate(
                    { opacity: 1 },
                    { queue: false, duration: 600 }
                );
        } else {
            this.$detailsContainer
                .hide()
                .css('opacity', 0);
        }
        this.$submitContainer.toggleClass('is-disabled', !showFullForm);
    }

    updatePasswordContainer () {
        const isRegistration = this.$registerCheckbox.is(':checked');
        this.$passwordContainer.toggle(isRegistration && !this.customerInfo.exists);
        this.$activationInfo.toggle(isRegistration && this.customerInfo.exists && !this.customerInfo.activated);
    }

    static init ($container) {
        $container.filterAllNodes('#js-order-form').each(function () {
            // eslint-disable-next-line no-unused-vars
            const orderForm = new OrderForm($(this));
        });
    }
}

(new Register()).registerCallback(OrderForm.init, 'OrderForm.init');
