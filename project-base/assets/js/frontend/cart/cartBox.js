import Ajax from 'framework/common/utils/ajax';
import Register from 'framework/common/utils/register';

export default class CartBox {
    static reload (event) {
        Ajax.ajax({
            loaderElement: '#js-cart-box',
            url: $(event.currentTarget).data('reload-url'),
            data: { 'isIntentActive': $(event.currentTarget).hasClass('active'), loadItems: true },
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
