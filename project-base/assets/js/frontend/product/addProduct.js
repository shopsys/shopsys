import $ from 'jquery';
import Ajax from '../../copyFromFw/ajax';
import Register from '../../copyFromFw/register';
import Window from '../window';

export default class AddProduct {
    static ajaxSubmit (event) {
        Ajax.ajax({
            url: $(event.target).data('ajax-url'),
            type: 'POST',
            data: $(event.target).serialize(),
            dataType: 'html',
            success: AddProduct.onSuccess,
            error: AddProduct.onError
        });

        event.preventDefault();
    };

    static onSuccess (data) {
        const buttonContinueUrl = $($.parseHTML(data)).filterAllNodes('.js-add-product-url-cart').data('url');
        const isWide = $($.parseHTML(data)).filterAllNodes('.js-add-product-wide-window').data('wide');
        const cssClass = isWide ? 'window-popup--wide' : 'window-popup--standard';

        if (buttonContinueUrl !== undefined) {
            // eslint-disable-next-line no-new
            new Window({
                content: data,
                cssClass: cssClass,
                buttonContinue: true,
                // textContinue: Shopsys.translator.trans('Go to cart'),
                textContinue: 'Go to cart',
                urlContinue: buttonContinueUrl,
                cssClassContinue: 'btn--success'
            });

            $('#js-cart-box').trigger('reload');
        } else {
            // eslint-disable-next-line no-new
            new Window({
                content: data,
                cssClass: cssClass,
                buttonCancel: true,
                // textCancel: Shopsys.translator.trans('Close'),
                textCancel: 'Close',
                cssClassCancel: 'btn--success'
            });
        }
    };

    static onError (jqXHR) {
        // on FireFox abort ajax request, but request was probably successful
        if (jqXHR.status !== 0) {
            // eslint-disable-next-line no-new
            new Window({
                // content: Shopsys.translator.trans('Operation failed')
                content: 'Operation failed'
            });
        }
    };

    static init ($container) {
        $container.filterAllNodes('form.js-add-product').on('submit.addProductAjaxSubmit', AddProduct.ajaxSubmit);
    }
}

new Register().registerCallback(AddProduct.init);
