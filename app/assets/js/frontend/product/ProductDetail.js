import '../components/filterAllNodes';
import 'magnific-popup';
import 'slick-carousel';
import Register from 'framework/common/utils/Register';

class ProductDetail {

    static init ($container) {
        $container.filterAllNodes('.js-gallery-main-image').click(function (event) {
            const $slides = $('.js-gallery .js-gallery-slide-link');
            $slides.filter(':first').trigger('click', event);

            return false;
        });

        const $gallery = $('.js-gallery');

        if ($gallery.length === 0) {
            return;
        }

        $gallery.magnificPopup({
            type: 'image',
            delegate: '.js-gallery-slide-link',
            gallery: {
                enabled: true,
                navigateByImgClick: true,
                preload: [0, 1]
            }
        });
    }
}

new Register().registerCallback(ProductDetail.init);
