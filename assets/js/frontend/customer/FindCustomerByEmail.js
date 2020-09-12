import 'framework/common/components';
import Ajax from 'framework/common/utils/Ajax';
import { KeyCodes } from 'framework/common/utils/KeyCodes';
import Register from 'framework/common/utils/Register';

export default class FindCustomerByEmail {
    ajaxSubmit () {

        const $emailInput = $('#js-check-existing-email');

        if (FindCustomerByEmail.validateEmail($emailInput)) {
            Ajax.ajax({
                url: '/customer/find-customer-by-email/',
                type: 'POST',
                data: { email: $emailInput.val() },
                dataType: 'json',
                complete: FindCustomerByEmail.onComplete,
                error: FindCustomerByEmail.onError
            });
        }
    }

    static validateEmail ($emailInput) {
        const inputText = $emailInput.val();
        const mailFormat = /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/;
        if (inputText.match(mailFormat)) {
            return true;
        } else {
            $emailInput
                .siblings('.js-check-existing-email-error')
                .removeClass('display-none');
            $emailInput
                .addClass('form-input-error')
                .focus();
            return false;
        }
    }

    static onComplete () {
        const $form = $('<form action="' + window.location.href + '" method="post">'
            + '<input type="text" name="flow_order_instance" value="flow_order" />'
            + '<input type="text" name="flow_order_step" value="3" />'
            + '</form>');
        $('body').append($form);
        $form.submit();
    }

    static onError () {
        const $form = $('<form action="' + window.location.href + '" method="post">'
            + '<input type="text" name="flow_order_instance" value="flow_order" />'
            + '<input type="text" name="flow_order_step" value="3" />'
            + '</form>');
        $('body').append($form);
        $form.submit();
    }

    static init ($container) {
        const findCustomerByEmail = new FindCustomerByEmail();
        $container.filterAllNodes('#js-check-existing-email-submit').click((event) => findCustomerByEmail.ajaxSubmit());
        $container.filterAllNodes('#js-check-existing-email').keypress(function (event) {
            if (event.keyCode === KeyCodes.ENTER) {
                findCustomerByEmail.ajaxSubmit();
                return false;
            }
        });
    }
}

new Register().registerCallback(FindCustomerByEmail.init, 'FindCustomerByEmail.init');
