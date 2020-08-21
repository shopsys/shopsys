import Register from 'framework/common/utils/Register';
import {
    addNewItemToCollection,
    removeItemFromCollection
} from 'framework/admin/validation/customization/customizeCollectionBundle';

export default class TransportPackages {

    constructor ($transportPackages) {
        this.$transportPackages = $transportPackages;
        this.itemProrotype = $transportPackages.data('prototype');
        this.$itemsContainer = $transportPackages.find('.js-transport-packages-items');
        this.$infoAboutEmptyCollection = $transportPackages.find('.js-transport-packages-empty');

        this.$transportPackages.on('click', '.js-transport-packages-item-add', (event) => { this.addPackage(event); });
        this.$transportPackages.on('click', '.js-transport-packages-item-remove', (event) => { this.onDeleteClick(event); });

        this.checkEmptyCollection();
    };

    static init ($container) {
        $container.filterAllNodes('.js-transport-packages').each(function () {
            // eslint-disable-next-line no-new
            new TransportPackages($(this));
        });
    }

    onDeleteClick (event) {
        event.preventDefault();

        const $item = $(event.currentTarget).closest('.js-transport-package-item');
        const index = $item.data('index');
        removeItemFromCollection('#' + this.$transportPackages.attr('id'), index);
        $item.remove();

        this.checkEmptyCollection();
    }

    addPackage (event) {
        event.preventDefault();

        const index = this.getNextNewPackageIndex();
        const itemHtml = this.itemProrotype
            .replace(/__name__label__/g, index)
            .replace(/__name__/g, index);
        const $item = $($.parseHTML(itemHtml));

        this.$itemsContainer.append($item);
        (new Register()).registerNewContent($item);

        addNewItemToCollection('#' + this.$transportPackages.attr('id'), index);

        this.checkEmptyCollection();
    }

    checkEmptyCollection () {
        const existsAnyItems = this.$itemsContainer.find('.js-transport-package-item').length > 0;
        this.$infoAboutEmptyCollection.toggle(!existsAnyItems);
    }

    getNextNewPackageIndex () {
        let index = 0;
        while (this.$itemsContainer.find('.js-transport-package-item[data-index=' + index.toString() + ']').length > 0) {
            index++;
        }

        return index;
    }

};

(new Register()).registerCallback(TransportPackages.init, 'TransportPackages.init');
