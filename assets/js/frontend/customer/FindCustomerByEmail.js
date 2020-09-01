import 'framework/common/components';
import Ajax from 'framework/common/utils/Ajax';
import { KeyCodes } from 'framework/common/utils/KeyCodes';
import Register from 'framework/common/utils/Register';

export default class FindCustomerByEmail {
    ajaxSubmit () {
        const email = $('#check-existing-email').val();
        Ajax.ajax({
            url: '/customer/find-customer-by-email/',
            type: 'POST',
            data: { email: email },
            dataType: 'json',
            complete: FindCustomerByEmail.onComplete,
            error: FindCustomerByEmail.onError
        });
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
        $container.filterAllNodes('#check-existing-email-submit').click((event) => findCustomerByEmail.ajaxSubmit());
        $container.filterAllNodes('#check-existing-email').keypress(function (event) {
            if (event.keyCode === KeyCodes.ENTER) {
                findCustomerByEmail.ajaxSubmit();
                return false;
            }
        });
    }
}

new Register().registerCallback(FindCustomerByEmail.init, 'FindCustomerByEmail.init');
