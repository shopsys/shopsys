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
                '<p class="window-popup__in__desc">Odkaz pro vyresetování hesla vám byl odeslán. Instrukce naleznete na své e-mailové adrese <strong>%email%</strong>. Vaše Sconto.</p>', { 'email': data.email });
        } else {
            message = Translator.trans(
                'Bohužel zadaný e-mail <strong>%email%</strong> neexistuje.', { 'email': data.email });
        }
        const $html = '<div>'
            + '<h2 class="window-popup__heading">'
            + Translator.trans('Obnova hesla')
            + '</h2>'
            + message
            + '</div>';
        // eslint-disable-next-line no-new
        new Window({
            content: $html,
            buttonContinue: true,
            cssClass: 'window-popup--wide window-popup--reset-password',
            cssClassContinue: 'window-popup__actions__btn--continue window-popup__actions__btn--continue--outline',
            textContinue: Translator.trans('Potvrdit'),
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
