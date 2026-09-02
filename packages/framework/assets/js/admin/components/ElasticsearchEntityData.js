import $ from 'jquery';
import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';
import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';

export default class ElasticsearchEntityData {
    static domainSwitchSelector = '[data-js-elasticsearch-entity-data-domain-switch]';

    constructor($container) {
        $container.filterAllNodes('[data-js-elasticsearch-entity-data-open]').on('click', this.openWindow);
    }

    openWindow(event) {
        event.preventDefault();
        event.stopPropagation();

        const $button = $(event.currentTarget);

        if ($button.data('loading')) {
            return false;
        }

        $button.data('loading', true);

        ElasticsearchEntityData.loadContent($button.data('url'), $button, content => {
            const modalInstance = new ModalWindow({
                content,
                size: 'xl',
            });

            ElasticsearchEntityData.registerModalContent(modalInstance);

            modalInstance.element.one('hidden.bs.modal', () => {
                $button.data('loading', false);
            });
        });

        return false;
    }

    static loadContent(url, $loaderElement, successCallback) {
        if (!url) {
            return;
        }

        Ajax.ajax({
            loaderElement: $loaderElement,
            url,
            type: 'GET',
            success: successCallback,
            error: () => {
                $loaderElement.data('loading', false);
            },
        });
    }

    static registerModalContent(modalInstance) {
        new Register().registerNewContent(modalInstance.element);

        modalInstance.element.find(ElasticsearchEntityData.domainSwitchSelector).on('click', event => {
            event.preventDefault();
            event.stopPropagation();

            const $switch = $(event.currentTarget);

            ElasticsearchEntityData.loadContent($switch.attr('href'), $switch, content => {
                modalInstance.element.find('.modal-body').html(content);
                ElasticsearchEntityData.registerModalContent(modalInstance);
            });
        });
    }

    static init($container) {
        // eslint-disable-next-line no-new
        new ElasticsearchEntityData($container);
    }
}

new Register().registerCallback(ElasticsearchEntityData.init, 'ElasticsearchEntityData.init');
