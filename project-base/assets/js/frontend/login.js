import $ from 'jquery';
import Ajax from '../copyFromFw/ajax';
import Register from '../copyFromFw/register';
import { createLoaderOverlay, showLoaderOverlay } from '../copyFromFw/loaderOverlay';
import Window from './window';
import Translator from 'bazinga-translator';

export default class Login {

    showWindow (event, login) {
        Ajax.ajax({
            url: $(event.currentTarget).data('url'),
            type: 'POST',
            success: function (data) {
                const $window = new Window({
                    content: data,
                    textHeading: Translator.trans('Login')
                });

                $window.getWindow().on('submit', '.js-front-login-window', login.onSubmit);
            }
        });

        event.preventDefault();
    }

    onSubmit () {
        Ajax.ajax({
            loaderElement: '.js-front-login-window',
            type: 'POST',
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function (data) {
                if (data.success === true) {
                    const $loaderOverlay = createLoaderOverlay('.js-front-login-window');
                    showLoaderOverlay($loaderOverlay);

                    document.location = data.urlToRedirect;
                } else {
                    const $validationErrors = $('.js-window-validation-errors');
                    if ($validationErrors.hasClass('display-none')) {
                        $validationErrors
                            .text(Translator.trans('This account doesn\'t exist or password is incorrect'))
                            .show();
                    }
                }
            }
        });
        return false;
    }

    static init ($container) {
        $container.filterAllNodes('.js-login-button').each(function () {
            const login = new Login();
            $(this).on('click', (event) => login.showWindow(event, login));
        });
    };
};

(new Register()).registerCallback(Login.init);
