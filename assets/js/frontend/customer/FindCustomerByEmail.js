import 'framework/common/components';
import Ajax from 'framework/common/utils/Ajax';
import Register from 'framework/common/utils/Register';

export default class FindCustomerByEmail {
    static ajaxSubmit () {
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
        location.reload();
    }

    static onError () {
        location.reload();
    }

    static init ($container) {
        $container.filterAllNodes('#check-existing-email-submit').on('click', FindCustomerByEmail.ajaxSubmit);
    }
}

new Register().registerCallback(FindCustomerByEmail.init, 'FindCustomerByEmail.init');
