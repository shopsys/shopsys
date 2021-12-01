import MiniLazyload from 'minilazyload';
import Register from 'framework/common/utils/Register';

/* eslint-disable no-new */
const lazyload = new MiniLazyload({
    rootMargin: '1250px',
    threshold: 0.5,
    placeholder: $('body').data('assets-url') + '/public/frontend/images/optimized-noimage.png'
});

(new Register()).registerCallback(lazyload.update);
