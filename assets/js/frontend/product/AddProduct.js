import 'framework/common/components';
import Ajax from 'framework/common/utils/Ajax';
import Register from 'framework/common/utils/Register';
import Window from '../utils/Window';
import Translator from 'bazinga-translator';

export default class AddProduct {
    static ajaxSubmit (event) {
        Ajax.ajax({
            url: $(event.target).data('ajax-url'),
            type: 'POST',
            data: $(event.target).serialize(),
            dataType: 'html',
            success: AddProduct.onSuccess,
            error: AddProduct.onError,
            context: this
        });

        event.preventDefault();
    }

    static onSuccess (data) {
        const buttonContinueUrl = $($.parseHTML(data)).filterAllNodes('.js-add-product-url-cart').data('url');
        const isWide = $($.parseHTML(data)).filterAllNodes('.js-add-product-wide-window').data('wide');
        const cssClass = isWide ? 'window-popup--wide' : 'window-popup--cart';
        const input = $(this).find('input.js-spinbox-input');
        const diffOfSpinMaxAndAddedQuantity = input.data('spinbox-max') - input.val();

        if (buttonContinueUrl !== undefined) {
            // eslint-disable-next-line no-new
            new Window({
                content: data,
                cssClass: cssClass,
                buttonContinue: true,
                textContinue: Translator.trans('Do košíku'),
                urlContinue: buttonContinueUrl,
                buttonCancel: true,
                textCancel: Translator.trans('Back to shop'),
                cssClassContinue: 'btn--success'
            });

            if (diffOfSpinMaxAndAddedQuantity >= 0) {
                input.data('spinbox-max', diffOfSpinMaxAndAddedQuantity);
            }
            input.val(input.data('spinbox-min'));
            $('#js-cart-box').trigger('reload');
        } else {
            // eslint-disable-next-line no-new
            new Window({
                content: data,
                cssClass: cssClass,
                buttonCancel: true,
                textCancel: Translator.trans('Close'),
                cssClassCancel: 'btn--success'
            });
        }
    }

    static onError (jqXHR) {
        // on FireFox abort ajax request, but request was probably successful
        if (jqXHR.status !== 0) {
            // eslint-disable-next-line no-new
            new Window({
                content: Translator.trans('Operation failed')
            });
        }
    }

    static init ($container) {
        $container.filterAllNodes('form.js-add-product').on('submit.addProductAjaxSubmit', AddProduct.ajaxSubmit);
    }
}

new Register().registerCallback(AddProduct.init, 'AddProduct.init');
