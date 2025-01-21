import Ajax from '../../common/utils/Ajax';
import Register from '../../common/utils/Register';

export default class Search {
    static init($container) {
        if ($container.is('body') === false) {
            return;
        }
        let timeout;
        const $searchInput = $container.filterAllNodes('.js-search-input');
        const $searchResults = $container.filterAllNodes('.js-search-results');
        const $searchResultsCloseButton = $container.filterAllNodes('.js-search-results__close');
        const $searchIcon = $container.filterAllNodes('.js-search-icon');
        const $searchElement = $container.filterAllNodes('.js-header-search');

        $container.on('keydown', function (event) {
            if (
                (event.key === 'Tab' || event.key === 'ArrowDown' || event.key === 'ArrowUp') &&
                $searchResults.is(':visible')
            ) {
                event.preventDefault();
                const focusableElements = $searchResults.filterAllNodes(
                    'div.web__header__search--results--container a',
                );
                $searchResults
                    .filterAllNodes('div.web__header__search--results--container div.result--item')
                    .removeClass('focused');
                const focusable = Array.from(focusableElements);
                const currentIndex = focusable.indexOf(document.activeElement);

                let nextIndex;

                const isUpward = event.key === 'ArrowUp' || (event.shiftKey && event.key === 'Tab');
                console.log("🚀 -> file: search.js:31 -> Search -> isUpward:", isUpward)

                if (isUpward) {
                    nextIndex = (currentIndex - 1 + focusable.length) % focusable.length;
                } else {
                    nextIndex = (currentIndex + 1) % focusable.length;
                }

                if (nextIndex > -1) {
                    focusable[nextIndex].focus();
                    $(focusable[nextIndex]).closest('div.result--item').addClass('focused');
                }

                return;
            }

            if (event.key === 'Escape' && $searchResults.is(':visible')) {
                event.preventDefault();
                $searchResults.hide();
                return;
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
            $searchResultsCloseButton.hide();
        });

        $(document).on('click', function (event) {
            if (!$(event.target).closest('.js-search-results').length) {
                Search.closeResults($searchResults, $searchInput);
                $searchResultsCloseButton.hide();
            }
        });

        $searchInput.on('input', function () {
            const $input = $(this);
            clearTimeout(timeout);
            if ($input.val().length > 0) {
                $searchResultsCloseButton.show();
            } else {
                $searchResultsCloseButton.hide();
            }
            timeout = setTimeout(function () {
                if ($input.val().length >= 3) {
                    Search.findResultsByInput($input, $searchResults);
                }
                if ($input.val().length < 3) {
                    Search.clearResults($searchResults);
                }
            }, 500);
        });
    }

    static closeResults($searchResults, $searchInput) {
        Search.clearResults($searchResults);
        $searchInput.val('');
    }

    static clearResults($searchResults) {
        $searchResults.find('.js-search-results__window').text('');
        $searchResults.find('.js-search-results__search').text('');
        $searchResults.hide();
    }

    static findResultsByInput($searchInput, $searchResults) {
        const value = $searchInput.val();
        Ajax.ajax({
            url: $searchInput.data('search-callback-url'),
            loaderElement: 'none',
            type: 'GET',
            data: {
                search: value,
            },
            success: function (results) {
                Search.showResults(value, results, $searchResults);
            },
        });
    }

    static showResults(search, results, $searchResults) {
        const $htmlResult = $($.parseHTML(results));
        $searchResults.find('.js-search-results__window').html($htmlResult);
        $searchResults.find('.js-search-results__search').text(search);
        $searchResults.show();
    }
}
new Register().registerCallback(Search.init, 'Search.init');
