import $ from 'jquery';
import Translator from 'bazinga-translator';
import Register from '../../common/utils/Register';

($ => {
    const Shopsys = window.Shopsys || {};
    Shopsys.promoCode = Shopsys.promoCode || {};

    Shopsys.promoCode.PromoCodeGroup = function ($promoCodeGroup) {
        let $rows = $promoCodeGroup.closest('.js-grid-row');
        const $firstRow = $promoCodeGroup.first().closest('.js-grid-row');
        const prefix = $firstRow.filterAllNodes('.js-promo-code-mass').attr('data-promo-code-prefix');

        this.init = () => {
            $rows.addClass('d-none');

            const unpackButtonHtml = `<span class="btn btn-sm js-promo-code-group-unpack" data-promo-code-prefix="${prefix}">${Translator.trans('Expand')}</span>`;
            const $parentRow = $(
                '<tr class="js-grid-row">' +
                    '<td colspan="3">' +
                    Translator.trans('Bulk coupons with prefix') +
                    ' <b>' +
                    prefix +
                    '</b></td>' +
                    '<td></td>' +
                    '<td class="text-end">' +
                    unpackButtonHtml +
                    '</td>' +
                    '</tr>',
            );

            $parentRow.insertBefore($firstRow);

            $rows = $promoCodeGroup.closest('.js-grid-row');

            $rows.each(function () {
                const $row = $(this);
                $row.filterAllNodes('td').first().css('padding-left', '40px');
            });

            $(`.js-promo-code-group-unpack[data-promo-code-prefix="${prefix}"]`).click(function () {
                $(this).text((_i, text) => {
                    const pack = Translator.trans('Collapse');
                    const unpack = Translator.trans('Expand');
                    return text === unpack ? pack : unpack;
                });
                $rows.toggleClass('d-none');
            });
        };
    };

    new Register().registerCallback($container => {
        function arrayUnique(array) {
            return $.grep(array, (el, index) => index === $.inArray(el, array));
        }

        let prefixJsClasses = [];

        $container.filterAllNodes('.js-promo-code-mass').each(function () {
            const prefixJsClass = $(this).attr('data-promo-code-prefix-js-class');
            if ($(this).attr('data-promo-code-group-enabled') === '1') {
                prefixJsClasses.push(prefixJsClass);
            }
        });

        prefixJsClasses = arrayUnique(prefixJsClasses);

        for (let i = 0; i < prefixJsClasses.length; i++) {
            const $promoCodeGroup = $(`.${prefixJsClasses[i]}`);
            const promoCodeGroup = new Shopsys.promoCode.PromoCodeGroup($promoCodeGroup);
            promoCodeGroup.init();
        }
    });
})($);
