import 'framework/common/components';
import Ajax from 'framework/common/utils/Ajax';
import Register from 'framework/common/utils/Register';
import Window from '../utils/Window';
import Translator from 'bazinga-translator';

export default class ResetCustomerpassword {
    resetPassword (event) {
        const email = $('.id__front_login_form_email').val();

        Ajax.ajax({
            url: '/customer/reset-password-ajax/',
            type: 'POST',
            data: { email: email },
            dataType: 'json',
            success: ResetCustomerpassword.onSuccess,
            error: ResetCustomerpassword.onError
        });
        event.preventDefault();
    }

    static onSuccess (data) {
        let message = '';
        if (data.success === true) {
            message = Translator.trans(
                'Odkaz pro vyresetování hesla byl zaslán na e-mail %email%.', { 'email': data.email });
        } else {
            message = Translator.trans(
                'Bohužel zadaný e-mail %email% neexistuje.', { 'email': data.email });
        }
        const $html = '<div>'
            + '<h3>'
            + Translator.trans('Obnova hesla')
            + '</h3>'
            + message
            + '</div>';
        // eslint-disable-next-line no-new
        new Window({
            content: $html
        });
    }

    static onError () {
        // eslint-disable-next-line no-new
        new Window({
            content: Translator.trans('Při resetování hesla došlo k chybě, zkuste to prosím znovu.')
        });
    }

    static init ($container) {
        $container.filterAllNodes('#reset-customer-password').on('click', (event) => (new ResetCustomerpassword()).resetPassword(event));
    }
}

new Register().registerCallback(ResetCustomerpassword.init, 'ResetCustomerpassword.init');
