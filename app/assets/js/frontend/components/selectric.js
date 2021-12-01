import Register from 'framework/common/utils/Register';
import 'selectric';

(function ($) {
    (new Register()).registerCallback(function ($container) {
        $container.filterAllNodes('select').selectric({
            arrowButtonMarkup: '<b class="button"><i class="svg svg-arrow"></i></b>',
            nativeOnMobile: false
        });
    });
})(jQuery);
