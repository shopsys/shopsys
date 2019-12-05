import $ from 'jquery';
import Ajax from '../copyFromFw/ajax';
import Register from '../copyFromFw/register';

export default class SearchAutocomplete {

    constructor (props) {
        this.options = {
            minLength: 3,
            requestDelay: 200
        };

        this.$input = null;
        this.$searchAutocompleteResults = null;
        this.requestTimer = null;
        this.resultExists = false;
        this.searchDataCache = {};
    }

    static init () {
        const searchAutocomplete = new SearchAutocomplete();

        searchAutocomplete.$input = $('#js-search-autocomplete-input');
        searchAutocomplete.$searchAutocompleteResults = $('#js-search-autocomplete-results');

        searchAutocomplete.$input.on('keyup paste', (event) => SearchAutocomplete.onInputChange(event, searchAutocomplete));
        searchAutocomplete.$input.on('focus', function () {
            if (searchAutocomplete.resultExists) {
                searchAutocomplete.$searchAutocompleteResults.show();
            }
        });

        $(document).click((event) => SearchAutocomplete.onDocumentClickHideAutocompleteResults(event, searchAutocomplete));
    };

    static onInputChange (event, searchAutocomplete) {
        clearTimeout(searchAutocomplete.requestTimer);

        // on "paste" event the $input.val() is not updated with new value yet,
        // therefore call of search() method is scheduled for later
        searchAutocomplete.requestTimer = setTimeout(() => SearchAutocomplete.search(searchAutocomplete), searchAutocomplete.options.requestDelay);

        // do not propagate change events
        // (except "paste" event that must be propagated otherwise the value is not pasted)
        if (event.type !== 'paste') {
            return false;
        }
    };

    static onDocumentClickHideAutocompleteResults (event, searchAutocomplete) {
        const $autocompleteElements = searchAutocomplete.$input.add(searchAutocomplete.$searchAutocompleteResults);
        if (searchAutocomplete.resultExists && $(event.target).closest($autocompleteElements).length === 0) {
            searchAutocomplete.$searchAutocompleteResults.hide();
        }
    };

    static search (searchAutocomplete) {
        const searchText = searchAutocomplete.$input.val();

        if (searchText.length >= searchAutocomplete.options.minLength) {
            if (searchAutocomplete.searchDataCache[searchText] !== undefined) {
                searchAutocomplete.showResult(searchAutocomplete.searchDataCache[searchText]);
            } else {
                searchAutocomplete.searchRequest(searchText);
            }
        } else {
            searchAutocomplete.resultExists = false;
            searchAutocomplete.$searchAutocompleteResults.hide();
        }
    };

    searchRequest (searchText) {
        const _this = this;
        Ajax.ajaxPendingCall('Shopsys.search.autocomplete.searchRequest', {
            loaderElement: null,
            url: _this.$input.data('autocomplete-url'),
            type: 'post',
            dataType: 'html',
            data: {
                searchText: searchText
            },
            success: function (responseHtml) {
                _this.searchDataCache[searchText] = responseHtml;
                _this.showResult(responseHtml);
            }
        });
    };

    showResult (responseHtml) {
        const $response = $($.parseHTML(responseHtml));

        this.resultExists = $response.find('li').length > 0;

        if (this.resultExists) {
            this.$searchAutocompleteResults.show();
        } else {
            this.$searchAutocompleteResults.hide();
        }

        this.$searchAutocompleteResults.html(responseHtml);
    };
}

(new Register()).registerCallback(SearchAutocomplete.init);
