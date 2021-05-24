import Register from 'framework/common/utils/Register';

(function ($) {

    const Shopsys = window.Shopsys || {};
    Shopsys.filterBox = Shopsys.filterBox || {};

    new Register().registerCallback(function ($container) {
        const $selectedFiltersBox = $('#js-selected-filters-box');
        const $filterUncheckButtons = $container.filterAllNodes('.js-selected-filters-box-uncheck');
        const $filterRangeUncheckLinks = $container.filterAllNodes('.js-filter-box-uncheck-range');

        if ($selectedFiltersBox.length > 0) {
            $filterUncheckButtons.each(function () {
                var $filterUncheckButton = $(this);
                var formId = $filterUncheckButton.data('filter-form-id');
                var $formInput = $('[data-filter-name-with-entity-id="' + formId + '"]');

                uncheckCheckboxAndRefreshContent($filterUncheckButton, $formInput);
            });
        }

        if ($filterRangeUncheckLinks.length > 0) {
            $filterRangeUncheckLinks.each(function () {
                uncheckCheckboxRangeAndRefreshContent($(this));
            });
        }

        $('#js-selected-filters-box').toggleClass(
            'display-none',
            $filterRangeUncheckLinks.length === 0 && $filterUncheckButtons.length === 0
        );

        function uncheckCheckboxAndRefreshContent ($filterUncheckButton, $formInput) {
            $filterUncheckButton.click(function () {

                $formInput.prop('checked', false);

                $formInput.change();
            });
        }

        function uncheckCheckboxRangeAndRefreshContent ($filterRangeUncheckButton) {
            $filterRangeUncheckButton.on('click', function () {
                $filterRangeUncheckButton.closest('.js-product-filter');
                const formId = $filterRangeUncheckButton.data('filter-form-id');
                const $rangeFilter = $filterRangeUncheckButton.closest('.js-product-filter').find('.js-range-slider');
                const $inputId = $rangeFilter.data(formId === 'product_filter_form_minimalPrice' ? 'minimum-input-id' : 'maximum-input-id');
                const $formInput = $('#' + $inputId);
                $formInput.val('');
                $formInput.change();
            });
        }
    });

})(jQuery);
