import Register from '../../common/register';
import 'select2';

export function initSelect2 ($container) {
    $container.filterAllNodes('select').select2({
        minimumResultsForSearch: 5,
        width: 'computedstyle'
    });
}

(new Register()).registerCallback(initSelect2);
