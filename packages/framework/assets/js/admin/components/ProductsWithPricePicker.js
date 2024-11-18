import 'jquery-ui-touch-punch';
import 'jquery-ui/ui/widgets/mouse';
import 'magnific-popup';
import FormChangeInfo from './FormChangeInfo';
import Register from '../../common/utils/Register';
import Window from '../utils/Window';
import Translator from 'bazinga-translator';
import { formatPrice } from '../../common/utils/priceFormatter';

window.ProductsPickerWithPriceInstances = {};

export default class ProductsWithPricePicker {

    constructor ($productsPicker) {
        this.instanceId = Object.keys(window.ProductsPickerWithPriceInstances).length;
        window.ProductsPickerWithPriceInstances[this.instanceId] = this;

        this.$productsPicker = $productsPicker;
        this.$header = $productsPicker.find('.js-products-with-price-picker-header');
        this.$addButton = $productsPicker.find('.js-products-with-price-picker-button-add');
        this.$itemsContainer = $productsPicker.find('.js-products-with-price-picker-items');
        this.productItems = [];

        this.initDomainChangeListener();

        const _this = this;
        this.$addButton.click(() => _this.openProductsPickerWindow());
        this.$itemsContainer.find('.js-products-with-price-picker-item').each(function () {
            _this.initItem($(this));
        });
    }

    initDomainChangeListener () {
        const $domainSelectInput = $('.js-update-domain-id');

        $domainSelectInput.on('change', function () {
            const selectedDomainId = this.value;
            const url = new URL(window.location.href);
            url.searchParams.set('domainId', selectedDomainId);

            if (FormChangeInfo.isInfoShown) {
                // eslint-disable-next-line no-new
                new Window({
                    content: Translator.trans('Changing the domain will cause the loss of unsaved changes. Do you want to continue?'),
                    buttonCancel: true,
                    buttonContinue: true,
                    textContinue: Translator.trans('Yes'),
                    urlContinue: url.toString()
                });
            } else {
                window.location.href = url.toString();
            }
        });
    }

    openProductsPickerWindow () {
        const _this = this;
        $.magnificPopup.open({
            items: { src: _this.$productsPicker.data('products-picker-url').replace('__js_instance_id__', _this.instanceId) },
            type: 'iframe',
            closeOnBgClick: true
        });

        return false;
    }

    initItem ($item) {
        const _this = this;
        const inputs = $item.find('input[type=text]');

        if (inputs.length > 0) {
            $(inputs[0]).change(function () {
                _this.updateDiscount($item);
            });
        }

        _this.updateDiscount($item);
        _this.productItems.push($item);
        $item.find('.js-products-with-price-picker-item-button-delete').click(() => {
            _this.removeItem($item);
        });
    }

    removeItem ($item) {
        const productId = $item.find('.js-products-with-price-picker-item-input:first').val();
        const productItemIndex = this.findProductItemIndex(productId);

        delete this.productItems[productItemIndex];
        const newProductItems = [];
        for (let key in this.productItems) {
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

    findProductItemIndex (productId) {
        for (let key in this.productItems) {
            if (this.productItems[key].find('.js-products-with-price-picker-item-input:first').val() === productId.toString()) {
                return key;
            }
        }

        return null;
    }

    reIndex () {
        const elements = {};

        this.$itemsContainer.find('.js-products-with-price-picker-item-input-reorder').each((index, element) => {
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
                const newName = name.substr(0, name.lastIndexOf('[', name.lastIndexOf('[') - 1) + 1) + index + '][' + element.data('name') + ']';
                element.attr('name', newName);
            });
        }
    }

    updateHeader () {
        this.$header.toggle(this.productItems.length !== 0);
    }

    updateDiscount ($item) {
        const inputs = $item.find('input[type=text]');
        const prices = $item.find('.js-products-with-price-picker-item-product-price');
        const discounts = $item.find('.js-products-with-price-picker-item-product-price-discount');
        const discountsPercentages = $item.find('.js-products-with-price-picker-item-product-price-discount-percentage');

        if (inputs.length > 0 && prices.length && discounts.length > 0 && discountsPercentages.length > 0) {
            const $input = $(inputs[0]);
            const $basicPrice = $(prices[0]);
            const $discount = $(discounts[0]);
            const $discountPercentage = $(discountsPercentages[0]);

            const inputPrice = parseFloat($input.val().replace(',', '.'));
            const basicDataPrice = $basicPrice.data('price');
            const basicPrice = parseFloat(typeof basicDataPrice === 'string' ? basicDataPrice.replace(',', '.') : basicDataPrice);
            const discount = Math.round((basicPrice - inputPrice) * 100) / 100;
            const discountColor = basicPrice > inputPrice ? 'green' : 'red';
            const discountPercentage = Math.floor(discount / (basicPrice === 0 ? 1 : basicPrice) * 100);

            $discount.text(formatPrice(discount, $basicPrice.data('locale'), $basicPrice.data('currency')));
            $discountPercentage.text(discountPercentage + '%');
            $discount.css('color', discountColor);
            $discountPercentage.css('color', discountColor);
        }
    }

    removeItemByProductId (productId) {
        const $item = this.findProductItemByProductId(productId);
        this.removeItem($item);
    }

    findProductItemByProductId (productId) {
        return this.productItems[this.findProductItemIndex(productId)];
    }

    hasProduct (productId) {
        return this.findProductItemIndex(productId) !== null;
    }

    addProduct (productId, productName, productPrice, productEan, productCatnum) {
        const nextIndex = this.$itemsContainer.find('.js-products-with-price-picker-item').length;
        const itemHtml = this.$productsPicker.data('products-picker-prototype').replace(/__name__/g, nextIndex);
        const $item = $($.parseHTML(itemHtml));
        const priceItem = $item.find('.js-products-with-price-picker-item-product-price');
        $item.find('.js-products-with-price-picker-item-product-ean').text(productEan);
        $item.find('.js-products-with-price-picker-item-product-name').text(productName);
        $item.find('.js-products-with-price-picker-item-product-catnum').text(productCatnum);
        priceItem.data('price', productPrice);
        priceItem.text(formatPrice(productPrice, priceItem.data('locale'), priceItem.data('currency')));

        $item.find('.js-products-with-price-picker-item-input').val(productId);
        $item.find('.js-products-with-price-picker-item-price-input').val(productPrice);
        $item.find('.js-products-with-price-picker-item-base-price-input').val(productPrice);

        this.$itemsContainer.append($item);
        this.initItem($item);
        this.updateHeader();
        FormChangeInfo.showInfo();
    }

    static init ($container) {
        $container.filterAllNodes('.js-products-with-price-picker').each(function () {
            // eslint-disable-next-line no-new
            new ProductsWithPricePicker($(this));
        });

        $('.js-products-with-price-picker-close').click(() => {
            window.parent.$.magnificPopup.instance.close();
        });
    }
}

(new Register().registerCallback(ProductsWithPricePicker.init, 'ProductsWithPricePicker.init'));
