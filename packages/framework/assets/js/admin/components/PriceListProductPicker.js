import $ from 'jquery';
import ConfirmWindow from '@shopsys/administration/src/js/utils/confirmWindow';
import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import Translator from 'bazinga-translator';
import { formatPrice } from '../../common/utils/priceFormatter';
import Register from '../../common/utils/Register';
import FormChangeInfo from './FormChangeInfo';

window.PriceListProductPickerInstances = {};

export default class PriceListProductPicker {
    constructor($productsPicker) {
        this.instanceId = Object.keys(window.PriceListProductPickerInstances).length;
        window.PriceListProductPickerInstances[this.instanceId] = this;

        this.$productsPicker = $productsPicker;
        this.$header = $productsPicker.find('.js-price-list-product-picker-header');
        this.$addButton = $productsPicker.find('.js-price-list-product-picker-button-add');
        this.$itemsContainer = $productsPicker.find('.js-price-list-product-picker-items');
        this.productItems = [];

        this.initDomainChangeListener();

        const _this = this;
        this.$addButton.click(() => _this.openProductsPickerWindow());
        this.$itemsContainer.find('.js-price-list-product-picker-item').each(function () {
            _this.initItem($(this));
        });
    }

    initDomainChangeListener() {
        const $domainSelectInput = $('.js-update-domain-id');

        $domainSelectInput.on('change', function () {
            const selectedDomainId = this.value;
            const url = new URL(window.location.href);
            url.searchParams.set('domainId', selectedDomainId);

            if (FormChangeInfo.isInfoShown) {
                ConfirmWindow.show({
                    content: Translator.trans(
                        'Changing the domain will cause the loss of unsaved changes. Do you want to continue?',
                    ),
                    style: null,
                    continueUrl: url.toString(),
                });
            } else {
                window.location.href = url.toString();
            }
        });
    }

    openProductsPickerWindow() {
        const url = this.$productsPicker.data('products-picker-url').replace('__js_instance_id__', this.instanceId);

        const iframeContent = `<iframe src="${url}" style="width: 100%; height: 800px; border: none;"></iframe>`;

        // eslint-disable-next-line no-new
        new ModalWindow({
            content: iframeContent,
            title: Translator.trans('Assign products'),
            size: 'xl',
            buttons: [{ text: Translator.trans('Finish assigning') }],
        });

        return false;
    }

    initItem($item) {
        const inputs = $item.find('input[type=text]');

        if (inputs.length > 0) {
            $(inputs[0]).change(() => {
                this.updateDiscount($item);
            });
        }

        this.updateDiscount($item);
        this.productItems.push($item);
        $item.find('.js-price-list-product-picker-item-button-delete').click(event => {
            event.preventDefault();
            this.removeItem($item);
        });
    }

    removeItem($item) {
        const productId = $item.find('.js-price-list-product-picker-item-input:first').val();
        const productItemIndex = this.findProductItemIndex(productId);

        delete this.productItems[productItemIndex];
        const newProductItems = [];
        for (const key in this.productItems) {
            if (this.productItems[key] !== productItemIndex) {
                newProductItems.push(this.productItems[key]);
            }
        }
        this.productItems = newProductItems;

        $item.remove();
        this.reIndex();
        this.updateHeader();
        FormChangeInfo.showInfo();
    }

    findProductItemIndex(productId) {
        for (const key in this.productItems) {
            if (
                this.productItems[key].find('.js-price-list-product-picker-item-input:first').val() ===
                productId.toString()
            ) {
                return key;
            }
        }

        return null;
    }

    reIndex() {
        const elements = {};

        this.$itemsContainer.find('.js-price-list-product-picker-item-input-reorder').each((_index, element) => {
            const $element = $(element);
            const name = $element.data('name');

            if (elements[name] === undefined) {
                elements[name] = [$element];
            } else {
                elements[name].push($element);
            }
        });

        for (const key in elements) {
            elements[key].forEach((element, index) => {
                const name = element.attr('name');
                const newName = `${name.substr(0, name.lastIndexOf('[', name.lastIndexOf('[') - 1) + 1) + index}][${element.data('name')}]`;
                element.attr('name', newName);
            });
        }
    }

    updateHeader() {
        this.$header.toggle(this.productItems.length !== 0);
    }

    updateDiscount($item) {
        const inputs = $item.find('input[type=text]');
        const prices = $item.find('.js-price-list-product-picker-item-product-price');
        const discounts = $item.find('.js-price-list-product-picker-item-product-price-discount');
        const discountsPercentages = $item.find('.js-price-list-product-picker-item-product-price-discount-percentage');

        if (inputs.length > 0 && prices.length && discounts.length > 0 && discountsPercentages.length > 0) {
            const $input = $(inputs[0]);
            const $basicPrice = $(prices[0]);
            const $discount = $(discounts[0]);
            const $discountPercentage = $(discountsPercentages[0]);

            const inputPrice = parseFloat($input.val().replace(',', '.'));
            const basicDataPrice = $basicPrice.data('price');
            const basicPrice = parseFloat(
                typeof basicDataPrice === 'string' ? basicDataPrice.replace(',', '.') : basicDataPrice,
            );
            const discount = Math.round((basicPrice - inputPrice) * 100) / 100;
            const discountColor = basicPrice > inputPrice ? 'green' : 'red';
            const discountPercentage = Math.floor((discount / (basicPrice === 0 ? 1 : basicPrice)) * 100);

            $discount.text(formatPrice(discount, $basicPrice.data('locale'), $basicPrice.data('currency')));
            $discountPercentage.text(`${discountPercentage}%`);
            $discount.css('color', discountColor);
            $discountPercentage.css('color', discountColor);
        }
    }

    removeItemByProductId(productId) {
        const $item = this.findProductItemByProductId(productId);
        this.removeItem($item);
    }

    findProductItemByProductId(productId) {
        return this.productItems[this.findProductItemIndex(productId)];
    }

    hasProduct(productId) {
        return this.findProductItemIndex(productId) !== null;
    }

    addProduct(productId, productName, productPrice, productEan, productCatnum) {
        const nextIndex = this.$itemsContainer.find('.js-price-list-product-picker-item').length;
        const itemHtml = this.$productsPicker.data('products-picker-prototype').replace(/__name__/g, nextIndex);
        const $item = $($.parseHTML(itemHtml));
        const priceItem = $item.find('.js-price-list-product-picker-item-product-price');
        $item.find('.js-price-list-product-picker-item-product-ean').text(productEan);
        $item.find('.js-price-list-product-picker-item-product-name').text(productName);
        $item.find('.js-price-list-product-picker-item-product-catnum').text(productCatnum);
        priceItem.data('price', productPrice);
        priceItem.text(formatPrice(productPrice, priceItem.data('locale'), priceItem.data('currency')));

        $item.find('.js-price-list-product-picker-item-input').val(productId);
        $item.find('.js-price-list-product-picker-item-price-input').val(productPrice);
        $item.find('.js-price-list-product-picker-item-base-price-input').val(productPrice);

        this.$itemsContainer.append($item);
        this.initItem($item);
        this.updateHeader();
        FormChangeInfo.showInfo();
    }

    static init($container) {
        $container.filterAllNodes('.js-price-list-product-picker').each(function () {
            // eslint-disable-next-line no-new
            new PriceListProductPicker($(this));
        });
    }
}

new Register().registerCallback(PriceListProductPicker.init, 'PriceListProductPicker.init');
