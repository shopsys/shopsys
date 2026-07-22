import Register from '../../common/utils/Register';

export default class AdditionalService {
    constructor($container) {
        this.$container = $container;

        $container.filterAllNodes('[data-js-additional-service-use-product-vat-rate]').each((_index, element) => {
            const $useProductVatRateRow = $(element);
            const onUseProductVatRateChange = () => this.toggleVatSelect($useProductVatRateRow);

            $useProductVatRateRow.find('input[type="radio"]').on('change', onUseProductVatRateChange);
            onUseProductVatRateChange();
        });

        const $showInFeedsCheckboxes = $container.filterAllNodes(
            '[data-js-additional-service-show-in-feeds] input[type="checkbox"]',
        );
        const onShowInFeedsChange = () => this.toggleFeedFields($showInFeedsCheckboxes);

        $showInFeedsCheckboxes.on('change', onShowInFeedsChange);
        onShowInFeedsChange();
    }

    toggleVatSelect($useProductVatRateRow) {
        const useProductVatRate = $useProductVatRateRow.find('input[type="radio"]:checked').val() === '1';
        const domainId = $useProductVatRateRow.data('domainId');
        const $vatRow = this.$container.filterAllNodes(
            `[data-js-additional-service-vat][data-domain-id="${domainId}"]`,
        );

        $vatRow.toggle(!useProductVatRate);
    }

    toggleFeedFields($showInFeedsCheckboxes) {
        const shownInFeedsOnAnyDomain = $showInFeedsCheckboxes.filter(':checked').length > 0;

        this.$container.filterAllNodes('[data-js-additional-service-feed-field]').toggle(shownInFeedsOnAnyDomain);
    }

    static init($container) {
        if (
            $container.filterAllNodes(
                '[data-js-additional-service-use-product-vat-rate], [data-js-additional-service-show-in-feeds]',
            ).length > 0
        ) {
            // eslint-disable-next-line no-new
            new AdditionalService($container);
        }
    }
}

new Register().registerCallback(AdditionalService.init, 'AdditionalService.init');
