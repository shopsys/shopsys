import $ from 'jquery';
import './bootstrap/tooltip';
import Register from '../common/register';
import Translator from 'bazinga-translator';

export default class ProductsPickerWindow {

    constructor ($addButton) {
        const productsPicker = window.parent.ProductsPickerInstances[$addButton.data('product-picker-instance-id')];
        const productId = $addButton.data('product-picker-product-id');

        if (productsPicker.isMainProduct(productId)) {
            this.markAddButtonAsDeny($addButton);
        } else if (productsPicker.hasProduct(productId)) {
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
            .on('click.removeProduct', function () {
                this.onClickOnAddedButton($addButton, originalLabelText, originalIconText);
            })
            .click(function () {
                return false;
            });
    }

    markAddButtonAsDeny ($addButton) {
        $addButton
            .addClass('cursor-help')
            .tooltip({
                title: Translator.trans('Not possible to assign product to itself'),
                placement: 'left'
            })
            .find('.js-products-picker-label').text(Translator.trans('Unable to add'))
            .find('.js-products-picker-icon').removeClass('svg-circle-plus in-icon in-icon--add').addClass('svg-circle-remove in-icon in-icon--denied').end()
            .click(() => false);
    }

    onClickAddButton (event) {
        const productsPicker = window.parent.ProductsPickerInstances[$(event.currentTarget).data('product-picker-instance-id')];
        this.markAddButtonAsAdded($(event.currentTarget));
        $(event.currentTarget).off('click.addProduct');
        productsPicker.addProduct(
            $(event.currentTarget).data('product-picker-product-id'),
            $(event.currentTarget).data('product-picker-product-name')
        );

        return false;
    }

    onClickOnAddedButton ($addButton, originalLabelText, originalIconText) {
        const productsPicker = window.parent.ProductsPickerInstances[$addButton.data('product-picker-instance-id')];
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
            .on('click.addProduct', () => this.onClickAddButton())
            .click(() => false);
    }

    static init () {
        $('.js-products-picker-window-add-product').each(function () {
            // eslint-disable-next-line no-new
            new ProductsPickerWindow($(this));
        });
    }
}

(new Register()).registerCallback(ProductsPickerWindow.init);
