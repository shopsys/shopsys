import $ from 'jquery';
import Register from '../../copyFromFw/register';

export default class CategoryDescription {
    static init () {
        const $description = $('.js-category-description');
        const $loadMoreButton = $('.js-category-description-load-more');
        const descriptionHeight = $description.height();

        if (descriptionHeight > 32) {
            $loadMoreButton.show();
            $description.addClass('box-list__description__text--small');
        }

        $loadMoreButton.click(function () {
            $description.removeClass('box-list__description__text--small');
            $loadMoreButton.closest('.js-category-description-load-more').hide();
        });
    }
}

(new Register()).registerCallback(CategoryDescription.init);
