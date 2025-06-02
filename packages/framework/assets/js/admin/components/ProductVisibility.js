import 'jquery-hoverintent';
import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';

export default class ProductVisibility {
    constructor ($productVisibility) {
        $productVisibility.on('show.bs.dropdown', function (relatedTarget) {
            Ajax.ajax({
                url: $(this).data('visibility-url'),
                success: (response) => $(relatedTarget.currentTarget).find('.js-product-visibility-content').html(response)
            });
        });
    }

    static init ($container) {
        $container.filterAllNodes('.js-product-visibility').each(function () {
            // eslint-disable-next-line no-new
            new ProductVisibility($(this));
        });
    }
}

new Register().registerCallback(ProductVisibility.init, 'ProductVisibility.init');
