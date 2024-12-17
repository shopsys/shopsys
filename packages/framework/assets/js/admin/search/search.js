import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';

export default class Search {
    static init ($container) {
        let timeout;
        const $searchInput = $container.filterAllNodes('.js-search-input');
        const $searchResults = $container.filterAllNodes('.js-search-results');
        const $searchResultsCloseButton = $container.filterAllNodes('.js-search-results__close');
        const $searchIcon = $container.filterAllNodes('.js-search-icon');
        const $searchElement = $container.filterAllNodes('.js-header-search');

        $container.on('keydown', function (event) {
            if (event.key === 'Tab' && $searchResults.is(':visible')) {
                event.preventDefault();
                const focusableElements = $searchResults.filterAllNodes('table a');
                $searchResults.filterAllNodes('table tr').removeClass('focused');
                const focusable = Array.from(focusableElements);
                const currentIndex = focusable.indexOf(document.activeElement);

                let nextIndex;

                if (event.shiftKey) {
                    nextIndex = (currentIndex - 1 + focusable.length) % focusable.length;
                } else {
                    nextIndex = (currentIndex + 1) % focusable.length;
                }

                if (nextIndex > -1) {
                    focusable[nextIndex].focus();
                    $(focusable[nextIndex]).closest('tr').addClass('focused');
                }
            }
        });

        $searchIcon.on('click', function () {
            if ($searchElement.hasClass('active')) {
                $searchElement.removeClass('active');
            } else {
                $searchElement.addClass('active');
                $searchInput.focus();
            }
        });

        $searchResultsCloseButton.on('click', function (event) {
            event.preventDefault();
            Search.closeResults($searchResults, $searchInput);
        });

        $(document).on('click', function (event) {
            if (!$(event.target).closest('.js-search-results').length) {
                Search.closeResults($searchResults, $searchInput);
            }
        });

        $searchInput.on('input', function () {
            const $input = $(this);
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                if ($input.val().length >= 3) {
                    Search.findResultsByInput($input, $searchResults);
                }
            }, 500);
        });
    }

    static closeResults ($searchResults, $searchInput) {
        $searchResults.find('.js-search-results__window').text('');
        $searchResults.find('.js-search-results__search').text('');
        $searchResults.hide();
        $searchInput.val('');
    }

    static findResultsByInput ($searchInput, $searchResults) {
        const value = $searchInput.val();
        Ajax.ajax({
            url: $searchInput.data('search-callback-url'),
            type: 'GET',
            data: {
                search: value
            },
            success: function (results) {
                Search.showResults(value, results, $searchResults);
            }
        });
    }

    static showResults (search, results, $searchResults) {
        const $htmlResult = $($.parseHTML(results));
        $searchResults.find('.js-search-results__window').html($htmlResult);
        $searchResults.find('.js-search-results__search').text(search);
        $searchResults.show();

    }
}
new Register().registerCallback(Search.init, 'Search.init');
