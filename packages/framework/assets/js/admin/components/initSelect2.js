import Register from '../../common/utils/Register';
import 'select2/dist/js/select2.full';

export function initSelect2 ($container) {
    const select2BottomMaxOffset = 250;

    $container.filterAllNodes('select').select2({
        minimumResultsForSearch: 5,
        width: 'computedstyle'
    }).on('select2:open', function () {
        const $select2Container = $('.select2-container').last();
        const bottom = $select2Container[0].getBoundingClientRect().bottom;
        if (($(window).outerHeight() - bottom) < select2BottomMaxOffset) {
            $select2Container.find('.select2-dropdown').removeClass('select2-dropdown--below').addClass('select2-dropdown--above');
        }
    });

}

(new Register()).registerCallback(initSelect2, 'initSelect2');
