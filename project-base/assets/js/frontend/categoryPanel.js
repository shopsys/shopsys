import $ from 'jquery';
import Ajax from '../copyFromFw/ajax';
import Register from '../copyFromFw/register';

export default class CategoryPanel {

    onCategoryCollapseControlClick (event) {
        event.stopPropagation();
        event.preventDefault();

        const $categoryCollapseControl = $(event.target);
        const $categoryItem = $categoryCollapseControl.closest('.js-category-item');
        const $categoryList = $categoryItem.find('.js-category-list').first();
        const isOpen = $categoryCollapseControl.hasClass('open');

        if (isOpen) {
            $categoryList.slideUp('fast');
        } else if ($categoryList.length > 0) {
            $categoryList.slideDown('fast');
        } else {
            this.loadCategoryItemContent($categoryItem, $categoryCollapseControl.data('url'));
        }

        $categoryCollapseControl.toggleClass('open', !isOpen);
    }

    loadCategoryItemContent ($categoryItem, url) {
        Ajax.ajax({
            loaderElement: $categoryItem,
            url: url,
            dataType: 'html',
            success: function (data) {
                const $categoryListPlaceholder = $categoryItem.find('.js-category-list-placeholder');
                const $categoryList = $($.parseHTML(data));

                $categoryListPlaceholder.replaceWith($categoryList);
                $categoryList.hide().slideDown('fast');

                (new Register()).registerNewContent($categoryList);
            }
        });
    }

    static init ($container) {
        $container.filterAllNodes('.js-category-collapse-control')
            .on('click', (event) => (new CategoryPanel()).onCategoryCollapseControlClick(event));
    }
}

(new Register()).registerCallback(CategoryPanel.init);
