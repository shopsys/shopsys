import Register from '../../common/utils/Register';
import Translator from 'bazinga-translator';

(function ($) {

    const Shopsys = window.Shopsys || {};
    Shopsys.promoCode = Shopsys.promoCode || {};

    Shopsys.promoCode.PromoCodeGroup = function ($promoCodeGroup) {
        var $rows = $promoCodeGroup.closest('.js-grid-row');
        var $firstRow = $promoCodeGroup.first().closest('.js-grid-row');
        var prefix = $firstRow.filterAllNodes('.js-promo-code-mass').attr('data-promo-code-prefix');

        this.init = function () {
            $rows.addClass('display-none');

            var unpackButtonHtml = '<span class="btn js-promo-code-group-unpack width-80 text-center" data-promo-code-prefix="' + prefix + '">' + Translator.trans('Expand') + '</span>';
            var $parentRow = $('<tr class="table-grid__row js-grid-row background-color-ddd">'
                + '<td colspan="2" class="table-grid__cell">' + Translator.trans('Bulk coupons with prefix') + ' <b>' + prefix + '</b></td>'
                + '<td></td>'
                + '<td class="table-grid__cell">' + unpackButtonHtml + '</td>'
                + '</tr>'
            );

            $parentRow.insertBefore($firstRow);

            $rows = $promoCodeGroup.closest('.js-grid-row');

            $rows.each(function () {
                let $row = $(this);
                $row.filterAllNodes('td').first().css('padding-left', '40px');
            });

            $('.js-promo-code-group-unpack[data-promo-code-prefix="' + prefix + '"]').click(function () {
                $(this).text(function (i, text) {
                    var pack = Translator.trans('Collapse');
                    var unpack = Translator.trans('Expand');
                    return text === unpack ? pack : unpack;
                });
                $rows.toggleClass('display-none');
            });
        };
    };

    (new Register()).registerCallback($container => {
        function arrayUnique (array) {
            return $.grep(array, function (el, index) {
                return index == $.inArray(el, array);
            });
        }

        var prefixJsClasses = [];

        $container.filterAllNodes('.js-promo-code-mass').each(function () {
            var prefixJsClass = $(this).attr('data-promo-code-prefix-js-class');
            if ($(this).attr('data-promo-code-group-enabled') === '1') {
                prefixJsClasses.push(prefixJsClass);
            }
        });

        prefixJsClasses = arrayUnique(prefixJsClasses);

        for (var i = 0; i < prefixJsClasses.length; i++) {
            const $promoCodeGroup = $('.' + prefixJsClasses[i]);
            var promoCodeGroup = new Shopsys.promoCode.PromoCodeGroup($promoCodeGroup);
            promoCodeGroup.init();
        }
    });

})(jQuery);
