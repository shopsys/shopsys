import $ from 'jquery';
import Translator from 'bazinga-translator';
import Check from 'icons/tabler/check.svg?raw';
import Denied from 'icons/tabler/circle-x-filled.svg?raw';
import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';

export default class ProductsPickerWindow {
    constructor($addButton) {
        const productsPicker = window.parent.ProductsPickerInstances[$addButton.data('product-picker-instance-id')];
        const productId = $addButton.data('product-picker-product-id');

        if (productsPicker.isMainProduct(productId)) {
            this.markAddButtonAsDeny($addButton);
        } else if (productsPicker.hasProduct(productId)) {
            this.markAddButtonAsAdded($addButton);
        } else {
            $addButton.on('click.addProduct', event => this.onClickAddButton(event));
        }
    }

    markAddButtonAsAdded($addButton) {
        const originalLabelText = $addButton.find('.js-products-picker-label').text();
        const originalIconHtml = $addButton.find('.js-products-picker-icon').html();
        $addButton
            .addClass('btn-success')
            .find('.js-products-picker-label')
            .text(Translator.trans('Added'))
            .end()
            .find('.js-products-picker-icon')
            .html(Check)
            .end()
            .on('click.removeProduct', () => {
                this.onClickOnAddedButton($addButton, originalLabelText, originalIconHtml);
            })
            .click(() => false);
    }

    markAddButtonAsDeny($addButton) {
        $addButton
            .addClass('cursor-help')
            .tooltip({
                title: Translator.trans('Not possible to assign product to itself'),
                placement: 'left',
            })
            .find('.js-products-picker-label')
            .text(Translator.trans('Unable to add'))
            .end()
            .find('.js-products-picker-icon')
            .html(Denied)
            .end()
            .click(() => false);
    }

    onClickAddButton(event) {
        const productsPicker =
            window.parent.ProductsPickerInstances[$(event.currentTarget).data('product-picker-instance-id')];
        this.markAddButtonAsAdded($(event.currentTarget));
        $(event.currentTarget).off('click.addProduct');

        Ajax.ajax({
            url: $(event.currentTarget).data('product-picker-product-image-url'),
            method: 'POST',
            data: {
                productId: $(event.currentTarget).data('product-picker-product-id'),
            },
            success: data => {
                productsPicker.addProduct(
                    $(event.currentTarget).data('product-picker-product-id'),
                    $(event.currentTarget).data('product-picker-product-name'),
                    data.imageHtml,
                );
            },
            error: () => {
                productsPicker.addProduct(
                    $(event.currentTarget).data('product-picker-product-id'),
                    $(event.currentTarget).data('product-picker-product-name'),
                );
            },
        });

        return false;
    }

    onClickOnAddedButton($addButton, originalLabelText, originalIconHtml) {
        const productsPicker = window.parent.ProductsPickerInstances[$addButton.data('product-picker-instance-id')];
        this.unmarkAddButtonAsAdded($addButton, originalLabelText, originalIconHtml);
        $addButton.off('click.removeProduct');
        productsPicker.removeItemByProductId($addButton.data('product-picker-product-id'));

        return false;
    }

    unmarkAddButtonAsAdded($addButton, originalLabelText, originalIconHtml) {
        $addButton
            .removeClass('btn-success')
            .find('.js-products-picker-label')
            .text(originalLabelText)
            .end()
            .find('.js-products-picker-icon')
            .html(originalIconHtml)
            .end()
            .on('click.addProduct', event => this.onClickAddButton(event))
            .click(() => false);
    }

    static init($container) {
        $container.filterAllNodes('.js-products-picker-window-add-product').each(function () {
            // eslint-disable-next-line no-new
            new ProductsPickerWindow($(this));
        });
    }
}

new Register().registerCallback(ProductsPickerWindow.init, 'ProductsPickerWindow.init');
