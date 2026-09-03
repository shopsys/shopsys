import Register from '../../common/utils/Register';
import FormChangeInfo from './FormChangeInfo';

export default class EntityUrlsNew {
    constructor($entityUrls) {
        $entityUrls.on('click', '.js-entity-url-list-button-add-url', event => this.onClickAddNewUrl(event));
        $entityUrls.on('click', '.js-entity-url-list-new-row-delete-button', event => this.onClickRemoveNewUrl(event));
    }

    onClickAddNewUrl(event) {
        const $newUrlsContainer = $(event.currentTarget)
            .closest('.js-entity-url-list-container')
            .find('.js-entity-url-list-new-urls');

        const prototype = $newUrlsContainer.data('new-url-prototype');
        const index = this.getNextNewUrlIndex($newUrlsContainer);
        const newUrl = prototype.replace(/__name__/g, index);
        const $newUrl = $($.parseHTML(newUrl));

        $newUrlsContainer.append($newUrl);

        new Register().registerNewContent($newUrl);
        FormChangeInfo.showInfo();
    }

    getNextNewUrlIndex($newUrlsContainer) {
        let index = 0;
        while ($newUrlsContainer.find(`.js-entity-url-list-new-row[data-index=${index.toString()}]`).length > 0) {
            index++;
        }

        return index;
    }

    onClickRemoveNewUrl(event) {
        const $row = $(event.currentTarget).closest('.js-entity-url-list-new-row');
        FormChangeInfo.showInfo();
        $row.remove();
    }

    static init($container) {
        $container.filterAllNodes('.js-entity-url-list').each(function () {
            // eslint-disable-next-line no-new
            new EntityUrlsNew($(this));
        });
    }
}

new Register().registerCallback(EntityUrlsNew.init, 'EntityUrlsNew.init');
