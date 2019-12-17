import { addNewItemToCollection, removeItemFromCollection } from '../common/validation/customizeBundle';
import Register from '../common/register';

export default class EntityUrlsNew {

    constructor ($entityUrls) {
        this.$buttonAdd = $entityUrls.find('.js-entity-url-list-button-add-url');
        this.$newUrlsContainer = $entityUrls.find('.js-entity-url-list-new-urls');
        this.newUrlsId = this.$newUrlsContainer.attr('id');

        this.$buttonAdd.click(() => this.addNewUrl());
        $entityUrls.on('click', '.js-entity-url-list-new-row-delete-button', (event) => this.onClickRemoveNewUrl(event));
    }

    addNewUrl () {
        const prototype = this.$newUrlsContainer.data('new-url-prototype');
        const index = this.getNextNewUrlIndex();
        const newUrl = prototype.replace(/__name__/g, index);
        const $newUrl = $($.parseHTML(newUrl));

        this.$newUrlsContainer.append($newUrl);

        (new Register()).registerNewContent($newUrl);
        addNewItemToCollection('#' + this.newUrlsId, index);
    }

    getNextNewUrlIndex () {
        let index = 0;
        while (this.$newUrlsContainer.find('.js-entity-url-list-new-row[data-index=' + index.toString() + ']').length > 0) {
            index++;
        }

        return index;
    }

    onClickRemoveNewUrl (event) {
        const $row = $(event.currentTarget).closest('.js-entity-url-list-new-row');
        const index = $row.data('index');
        removeItemFromCollection('#' + this.newUrlsId, index);
        $row.remove();
    }

    static init ($container) {
        $container.filterAllNodes('.js-entity-url-list').each(function () {
            // eslint-disable-next-line no-new
            new EntityUrlsNew($(this));
        });
    }
}

(new Register()).registerCallback(EntityUrlsNew.init);
