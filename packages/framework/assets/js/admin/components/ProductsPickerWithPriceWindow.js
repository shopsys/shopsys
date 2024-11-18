import '../../common/bootstrap/tooltip';
import Translator from 'bazinga-translator';
import Register from '../../common/utils/Register';

export default class ProductsPickerWithPriceWindow {

    constructor ($addButton) {
        const productsPicker = window.parent.ProductsPickerWithPriceInstances[$addButton.data('product-picker-instance-id')];
        const productId = $addButton.data('product-picker-product-id');

        if (productsPicker.hasProduct(productId)) {
            this.markAddButtonAsAdded($addButton);
        } else {
            $addButton.on('click.addProduct', (event) => this.onClickAddButton(event));
        }
    }

    markAddButtonAsAdded ($addButton) {
        const originalLabelText = $addButton.find('.js-products-picker-label').text();
        const originalIconText = $addButton.find('.js-products-picker-icon').text();
        $addButton
            .addClass('cursor-auto btn--success').removeClass('btn--plus btn--light')
            .find('.js-products-picker-label').text(Translator.trans('Added')).end()
            .find('.js-products-picker-icon').addClass('svg svg-checked').empty().end()
            .on('click.removeProduct', () => {
                this.onClickOnAddedButton($addButton, originalLabelText, originalIconText);
            })
            .click(function () {
                return false;
            });
    }

    onClickAddButton (event) {
        const productsPicker = window.parent.ProductsPickerWithPriceInstances[$(event.currentTarget).data('product-picker-instance-id')];
        const $currentTarget = $(event.currentTarget);
        this.markAddButtonAsAdded($currentTarget);
        $currentTarget.off('click.addProduct');
        productsPicker.addProduct(
            $currentTarget.data('product-picker-product-id'),
            $currentTarget.data('product-picker-product-name'),
            $currentTarget.data('product-picker-product-price'),
            $currentTarget.data('product-picker-product-ean'),
            $currentTarget.data('product-picker-product-catnum')
        );

        return false;
    }

    onClickOnAddedButton ($addButton, originalLabelText, originalIconText) {
        const productsPicker = window.parent.ProductsPickerWithPriceInstances[$addButton.data('product-picker-instance-id')];
        this.unmarkAddButtonAsAdded($addButton, originalLabelText, originalIconText);
        $addButton.off('click.removeProduct');
        productsPicker.removeItemByProductId($addButton.data('product-picker-product-id'));

        return false;
    }

    unmarkAddButtonAsAdded ($addButton, originalLabelText, originalIconText) {
        $addButton
            .addClass('btn--plus btn--light').removeClass('cursor-auto btn--success')
            .find('.js-products-picker-label').text(originalLabelText).end()
            .find('.js-products-picker-icon').removeClass('svg svg-checked').text(originalIconText).end()
            .on('click.addProduct', (event) => this.onClickAddButton(event))
            .click(() => false);
    }

    static init ($container) {
        $container.filterAllNodes('.js-products-picker-with-price-window-add-product').each(function () {
            // eslint-disable-next-line no-new
            new ProductsPickerWithPriceWindow($(this));
        });
    }
}

(new Register()).registerCallback(ProductsPickerWithPriceWindow.init, 'ProductsPickerWithPriceWindow.init');
