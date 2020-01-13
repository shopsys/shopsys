import Ajax from 'framework/common/utils/ajax';
import Register from 'framework/common/utils/register';
import Responsive from '../utils/responsive';

export default class CategoryPanel {

    onCategoryCollapseControlClick (event) {
        event.stopPropagation();
        event.preventDefault();

        const $categoryCollapseControl = $(event.target);
        const $categoryItem = $categoryCollapseControl.closest('.js-category-item');
        const $categoryList = $categoryItem.find('.js-category-list').first();
        const isOpen = $categoryCollapseControl.hasClass('open');

        if (!$categoryItem.hasClass('intented')) {

            if (isOpen) {
                $categoryList.slideUp('fast');
                $categoryItem.removeClass('active');
                $categoryCollapseControl.removeClass('open');
            } else if ($categoryList.length > 0) {
                $categoryList.slideDown('fast');
                $categoryItem.addClass('active');
                $categoryCollapseControl.addClass('open');
            } else {
                this.loadCategoryItemContent($categoryItem, $categoryCollapseControl.data('url'));
                $categoryItem.addClass('active');
                $categoryCollapseControl.addClass('open');
            }
        }
    }

    loadCategoryItemContent ($categoryItem, url) {
        Ajax.ajax({
            loaderElement: $categoryItem.find('.js-category-list-placeholder'),
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

        if (!Responsive.isDesktopVersion()) {
            $container.filterAllNodes('.js-category-collapse-control').each((index, element) => {
                if ($(element).parent().siblings('.js-category-list-placeholder').length === 0) {
                    $(element).addClass('open');
                }
            });
        }
    }
}

(new Register()).registerCallback(CategoryPanel.init);
