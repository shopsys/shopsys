import Register from 'framework/common/utils/Register';

export default class CategoryDescription {
    static init () {
        const $description = $('.js-category-description');
        const $descriptionContentHeight = $('.js-category-description-content').height();
        const $loadMoreButton = $('.js-category-description-load-more');

        if ($description.height() < $descriptionContentHeight) {
            $loadMoreButton.css('display', 'flex');
        }

        $loadMoreButton.click(function () {
            $description.css('max-height', $descriptionContentHeight);
            $(this).hide();
        });
    }
}

(new Register()).registerCallback(CategoryDescription.init, 'CategoryDescription.init');
