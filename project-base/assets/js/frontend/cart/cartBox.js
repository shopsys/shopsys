import $ from 'jquery';
import Ajax from '../../copyFromFw/ajax';
import Register from '../../copyFromFw/register';

export default class CartBox {
    static reload (event) {
        Ajax.ajax({
            loaderElement: '#js-cart-box',
            url: $(event.currentTarget).data('reload-url'),
            type: 'get',
            success: function (data) {
                $('#js-cart-box').replaceWith(data);

                (new Register()).registerNewContent($('#js-cart-box').parent());
            }
        });

        event.preventDefault();
    }

    static init ($container) {
        $container.filterAllNodes('#js-cart-box').on('reload', CartBox.reload);
    }
}

(new Register()).registerCallback(CartBox.init);
