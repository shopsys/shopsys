import $ from 'jquery';
import '../../copyFromFw/components';

import Ajax from '../../copyFromFw/ajax';
import { lazyLoadCall } from '../lazyLoadInit';
import Register from '../../copyFromFw/register';
import Translator from 'bazinga-translator';

const optionsDefaults = {
    buttonTextCallback: function (loadNextCount) {
        return Translator.transChoice(
            '{1}Load next %loadNextCount% item|[2,Inf]Load next %loadNextCount% items',
            loadNextCount,
            { 'loadNextCount': loadNextCount }
        );
    }
};

export default class AjaxMoreLoader {

    constructor ($wrapper, options) {
        this.$wrapper = $wrapper;
        this.$loadMoreButton = this.$wrapper.filterAllNodes('.js-load-more-button');
        this.$currentList = this.$wrapper.filterAllNodes('.js-list');
        this.$paginationToItemSpan = this.$wrapper.filterAllNodes('.js-pagination-to-item');

        this.totalCount = this.$loadMoreButton.data('total-count');
        this.pageSize = this.$loadMoreButton.data('page-size');
        this.page = this.$loadMoreButton.data('page');
        this.pageQueryParameter = this.$loadMoreButton.data('page-query-parameter') || 'page';
        this.paginationToItem = this.$loadMoreButton.data('pagination-to-item');
        this.url = this.$loadMoreButton.data('url') || document.location;

        this.options = $.extend({}, optionsDefaults, options);

        this.updateLoadMoreButton();
        this.$loadMoreButton.on('click', () => this.onClickLoadMoreButton(this));
    }

    onClickLoadMoreButton (ajaxMoreLoader) {
        $(this).hide();

        const requestData = {};
        requestData[ajaxMoreLoader.pageQueryParameter] = ajaxMoreLoader.page + 1;

        Ajax.ajax({
            loaderElement: ajaxMoreLoader.$wrapper,
            type: 'GET',
            url: ajaxMoreLoader.url,
            data: requestData,
            success: function (data) {
                const $response = $($.parseHTML(data));
                const $nextItems = $response.find('.js-list > *');
                ajaxMoreLoader.$currentList.append($nextItems);
                ajaxMoreLoader.page++;
                ajaxMoreLoader.paginationToItem += $nextItems.length;
                ajaxMoreLoader.$paginationToItemSpan.text(ajaxMoreLoader.paginationToItem);
                ajaxMoreLoader.updateLoadMoreButton();
                lazyLoadCall(ajaxMoreLoader.$currentList);
                (new Register()).registerNewContent($nextItems);
            }
        });
    };

    updateLoadMoreButton () {
        const remaining = this.totalCount - this.page * this.pageSize;
        const loadNextCount = remaining >= this.pageSize ? this.pageSize : remaining;
        const buttonText = this.options.buttonTextCallback(loadNextCount);

        this.$loadMoreButton
            .val(buttonText)
            .toggle(remaining > 0);
    };

    static init ($container) {
        $container.filterAllNodes('.js-list-with-paginator').each(function () {
            // eslint-disable-next-line no-new
            new AjaxMoreLoader($(this));
        });
    }
}

(new Register()).registerCallback(AjaxMoreLoader.init);
