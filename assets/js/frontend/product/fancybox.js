import $ from 'jquery';
import '../components/filterAllNodes';
import 'magnific-popup';
import '@fancyapps/fancybox';
import Register from 'framework/common/utils/register';
import Translator from 'bazinga-translator';

class ProductDetailGallery {
    static init ($container) {
        $container.filterAllNodes('[data-fancybox="products-gallery"]').fancybox({
            thumbs: {
                autoStart: true
            },
            infobar: true,
            arrows: true,
            buttons: [
                'slideShow',
                'fullScreen',
                'zoom',
                'share',
                'arrowLeft',
                'arrowRight',
                'close'
            ],
            animationEffect: 'zoom',
            transitionEffect: 'fade',

            // Internationalization
            // ====================
            lang: 'language',
            i18n: {
                language: {
                    CLOSE: Translator.trans('Zavřít'),
                    NEXT: Translator.trans('Další'),
                    PREV: Translator.trans('Předchozí'),
                    ERROR: Translator.trans('Obsah není možné načíst. <br/> Zkuste to prosím později.'),
                    PLAY_START: Translator.trans('Spustit prezentaci'),
                    PLAY_STOP: Translator.trans('Pozastavit prezentaci"'),
                    FULL_SCREEN: Translator.trans('Na celou obrazovku'),
                    THUMBS: Translator.trans('Náhledy'),
                    DOWNLOAD: Translator.trans('Stáhnout'),
                    SHARE: Translator.trans('Sdílet'),
                    ZOOM: Translator.trans('Zvětšit')
                }
            },

            // Custom options when mobile device is detected
            // =============================================
            mobile: {
                thumbs: {
                    autoStart: false
                },
                buttons: [
                    'slideShow',
                    'share',
                    'close'
                ]
            }
        });

        // show more button
        var $gallery = $('.js-gallery');
        $gallery.filterAllNodes('.js-gallery-item-more').click(function (e) {
            e.preventDefault();
            $(this).addClass('display-none');
            $container.filterAllNodes('.js-gallery-item').removeClass('display-none');
        });
    }
}

new Register().registerCallback(ProductDetailGallery.init);
