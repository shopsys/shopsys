import $ from 'jquery';
import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';

export default class ImportPriceList {
    constructor($container) {
        const $selectListField = $container.filterAllNodes('.js-import-price-list-select-list');
        $selectListField.on('change', event => this.onSelectPriceList(event));

        if ($selectListField.val() !== '') {
            const $domainIdField = $('[data-js-import-price-list-domain-id]');
            $domainIdField.hide();
        }
    }

    onSelectPriceList(event) {
        const priceListId = event.target.value;
        const $domainIdField = $('[data-js-import-price-list-domain-id]');

        if (priceListId === '') {
            $domainIdField.show();
            $('.js-import-price-list-name').val('');
            $('.js-import-price-list-valid-from').val('');
            $('.js-import-price-list-valid-to').val('');

            return;
        }

        $domainIdField.hide();

        let loadMetadataUrl = $(event.target).data('load-metadata-url');
        loadMetadataUrl = loadMetadataUrl.replace(/\/0\b/, `/${priceListId}`);

        Ajax.ajax({
            url: loadMetadataUrl,
            method: 'POST',
            success: data => {
                $('.js-import-price-list-name').val(data.name);
                $('.js-import-price-list-valid-from').val(data.validFrom);
                $('.js-import-price-list-valid-to').val(data.validTo);
            },
        });
    }

    static init($container) {
        // eslint-disable-next-line no-new
        new ImportPriceList($container);
    }
}

new Register().registerCallback(ImportPriceList.init, 'ImportPriceList.init');
