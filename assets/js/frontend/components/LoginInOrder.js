import Ajax from 'framework/common/utils/Ajax';
import Register from 'framework/common/utils/Register';
import Window from '../utils/Window';

export default class LoginInOrder {

    constructor ($form) {
        this.$form = $form;
        this.$form.submit((event) => {
            this.onSubmit(event);
        });
    }

    onSubmit (event, login) {
        Ajax.ajax({
            url: this.$form.data('login-url'),
            type: 'POST',
            dataType: 'json',
            data: this.$form.serialize(),
            success: LoginInOrder.onLoginResponse
        });

        event.preventDefault();
    }

    static onLoginResponse (data) {
        if (data.success === false) {
            const html = '<div>'
            + '<h2 class="window-popup__heading">'
            + data.errorHeader
            + '</h2>'
            + data.errorMessage
            + '</div>';
            // eslint-disable-next-line no-new
            new Window({
                content: html
            });
        } else {
            window.location = window.location.href;
        }
    }

    static init ($container) {
        $container.filterAllNodes('.js-login-in-order').each(function () {
            // eslint-disable-next-line no-new
            new LoginInOrder($(this));
        });
    }
}

(new Register()).registerCallback(LoginInOrder.init, 'LoginInOrder.init');
