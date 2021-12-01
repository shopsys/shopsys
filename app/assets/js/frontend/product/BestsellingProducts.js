import Responsive from '../utils/Responsive';
import Register from 'framework/common/utils/Register';

class BestsellingProducts {
    static init () {
        function showItems () {
            $('.js-bestselling-products-load-more').click(function () {
                const $loadMoreButton = $(this);
                const $loadMoreItems = $loadMoreButton.closest('.js-bestselling-products').find('.js-bestselling-product');
                $loadMoreItems.slideDown('fast');
                $loadMoreButton.closest('.js-bestselling-products-load-more-container').slideUp('fast');
            });
        }

        function showItemsInstant () {
            $('.js-bestselling-product').show();
        }

        function showMoreItems () {
            if (window.innerWidth > Responsive.LG) {
                showItems();
            } else {
                showItemsInstant();
            }
        }

        showMoreItems();

        $(window).resize(function (e) {
            showMoreItems();
        });
    }
}

(new Register()).registerCallback(BestsellingProducts.init);
