import Register from 'framework/common/utils/Register';
import { formatDecimalNumber } from 'framework/common/utils/number';
import parseNumberFixed from '../../frontend/utils/numbers';
import {
    addNewItemToCollection,
    removeItemFromCollection
} from 'framework/admin/validation/customization/customizeCollectionBundle';

export default class TransportPalletPrices {

    constructor ($transportPalletPrices) {
        this.$transportPalletPrices = $transportPalletPrices;
        this.itemProrotype = $transportPalletPrices.data('prototype');
        this.$itemsContainer = $transportPalletPrices.find('.js-transport-pallet-prices-items');

        this.$transportPalletPrices.on('click', '.js-transport-pallet-prices-add-button', (event) => { this.addItem(); event.preventDefault(); });
        this.$transportPalletPrices.on('click', '.js-transport-pallet-prices-delete-button', (event) => { this.onDeleteClick(event); });
        this.$transportPalletPrices.on('change blur', '.js-transport-pallet-prices-price-to-input', (event) => { this.calculatePriceFromForLastItem(); });
        this.$transportPalletPrices.bind('removeAllItems', () => { this.removeAllItems(); });
        this.$transportPalletPrices.bind('reinit', () => { this.checkLastItem(); });

        this.checkLastItem();
    };

    static init ($container) {
        $container.filterAllNodes('.js-transport-pallet-prices').each(function () {
            // eslint-disable-next-line no-new
            new TransportPalletPrices($(this));
        });
    }

    checkLastItem () {
        let $lastItem = this.$itemsContainer.find('.js-transport-pallet-prices-last-item');
        if ($lastItem.length === 0) {
            $lastItem = this.$itemsContainer.find('.js-transport-pallet-prices-item:last');
            if ($lastItem.length === 0) {
                $lastItem = this.addItem();
            }
        }
        $lastItem.addClass('js-transport-pallet-prices-last-item');
        $lastItem.find('.js-transport-pallet-prices-price-to-input').prop('readonly', true);
        $lastItem.find('.js-transport-pallet-prices-price-to-label').removeClass('visibility-hidden');
        $lastItem.find('.js-transport-pallet-prices-delete-button').remove();
        this.calculatePriceFromForLastItem();
    }

    removeAllItems () {
        this.$itemsContainer.find('.js-transport-pallet-prices-item').each((key, item) => {
            const index = $(item).data('index');
            removeItemFromCollection('#' + this.$transportPalletPrices.attr('id'), index);
            $(item).remove();
        });
    }

    onDeleteClick (event) {
        event.preventDefault();

        const $item = $(event.currentTarget).closest('.js-transport-pallet-prices-item');
        const index = $item.data('index');
        removeItemFromCollection('#' + this.$transportPalletPrices.attr('id'), index);
        $item.remove();
        this.calculatePriceFromForLastItem();
    }

    calculatePriceFromForLastItem () {
        let maxPriceTo = 0;
        this.$itemsContainer.find('.js-transport-pallet-prices-item:not(.js-transport-pallet-prices-last-item) .js-transport-pallet-prices-price-to-input').each(function () {
            const priceTo = parseNumberFixed($(this).val());
            if (priceTo !== null && priceTo >= maxPriceTo) {
                maxPriceTo = priceTo + 1;
            }
        });

        this.$itemsContainer.find('.js-transport-pallet-prices-last-item .js-transport-pallet-prices-price-to-input').val(formatDecimalNumber(maxPriceTo));
    }

    addItem (event) {
        const index = this.getNextNewIndex();
        const itemHtml = this.itemProrotype
            .replace(/__name__label__/g, index)
            .replace(/__name__/g, index);
        const $item = $($.parseHTML(itemHtml));

        const $lastItem = this.$itemsContainer.find('.js-transport-pallet-prices-last-item');
        if ($lastItem.length > 0) {
            $item.insertBefore($lastItem);
        } else {
            this.$itemsContainer.append($item);

        }
        (new Register()).registerNewContent($item);

        addNewItemToCollection('#' + this.$transportPalletPrices.attr('id'), index);

        return $item;
    }

    getNextNewIndex () {
        let index = 0;
        while (this.$itemsContainer.find('.js-transport-pallet-prices-item[data-index=' + index.toString() + ']').length > 0) {
            index++;
        }

        return index;
    }

};

(new Register()).registerCallback(TransportPalletPrices.init, 'TransportPalletPrices.init', 110); // Priority has to be higher then priority of transportForm.js
