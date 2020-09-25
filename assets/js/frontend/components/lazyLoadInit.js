import MiniLazyload from 'minilazyload';
import Register from 'framework/common/utils/Register';

/* eslint-disable no-new */
new MiniLazyload({
    rootMargin: '500px',
    threshold: 0.5,
    placeholder: $('body').data('assets-url') + '/public/frontend/images/optimized-noimage.png'
}, '', MiniLazyload.IGNORE_NATIVE_LAZYLOAD);

export function lazyLoadInit (container) {
    $(container).find('[loading=lazy]').each(function () {
        $(this).attr('src', $(this).data('src')).addClass('loaded');
    });
}

(new Register()).registerCallback(lazyLoadInit);
