import $ from 'jquery';
import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import Translator from 'bazinga-translator';
import Check from 'icons/tabler/check.svg?raw';
import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';

export default class PriceListProductPickerWindow {
    constructor($addButton) {
        const productsPicker =
            window.parent.PriceListProductPickerInstances[$addButton.data('product-picker-instance-id')];
        const productId = $addButton.data('product-picker-product-id');

        if (productsPicker.hasProduct(productId)) {
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

    onClickAddButton(event) {
        const productsPicker =
            window.parent.PriceListProductPickerInstances[$(event.currentTarget).data('product-picker-instance-id')];
        const basicPriceUrl = $(event.currentTarget).data('product-picker-basic-price-url');
        const $currentTarget = $(event.currentTarget);
        this.markAddButtonAsAdded($currentTarget);
        $currentTarget.off('click.addProduct');

        Ajax.ajax({
            url: basicPriceUrl,
            method: 'POST',
            data: {
                productId: $currentTarget.data('product-picker-product-id'),
                domainId: $currentTarget.data('product-picker-domain-id'),
            },
            success: data => {
                productsPicker.addProduct(
                    $currentTarget.data('product-picker-product-id'),
                    $currentTarget.data('product-picker-product-name'),
                    data.basicPrice,
                    $currentTarget.data('product-picker-product-ean'),
                    $currentTarget.data('product-picker-product-catnum'),
                );
            },
            error: () => {
                // eslint-disable-next-line no-new
                new ModalWindow({
                    content: Translator.trans('Unable to add product'),
                });
            },
        });

        return false;
    }

    onClickOnAddedButton($addButton, originalLabelText, originalIconHtml) {
        const productsPicker =
            window.parent.PriceListProductPickerInstances[$addButton.data('product-picker-instance-id')];
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
        $container.filterAllNodes('.js-price-list-product-picker-window-add-product').each(function () {
            // eslint-disable-next-line no-new
            new PriceListProductPickerWindow($(this));
        });
    }
}

new Register().registerCallback(PriceListProductPickerWindow.init, 'PriceListProductPickerWindow.init');
