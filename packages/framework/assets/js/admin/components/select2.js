import Register from '../../common/utils/register';
import 'select2/dist/js/select2.full';

export function initSelect2 ($container) {
    $container.filterAllNodes('select').select2({
        minimumResultsForSearch: 5,
        width: 'computedstyle'
    });
}

(new Register()).registerCallback(initSelect2);
