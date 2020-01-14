/* eslint-disable no-new */
import MiniLazyload from 'minilazyload';
import Register from 'framework/common/utils/register';

new MiniLazyload({
    rootMargin: '500px',
    threshold: 0.5,
    placeholder: '/assets/frontend/images/noimage.png'
}, '', MiniLazyload.IGNORE_NATIVE_LAZYLOAD);

export function lazyLoadCall (container) {
    $(container).find('[loading=lazy]').each(function () {
        $(this).attr('src', $(this).data('src')).addClass('loaded');
    });
}

(new Register()).registerCallback(lazyLoadCall);
