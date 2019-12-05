import $ from 'jquery';
import Register from '../copyFromFw/register';

(new Register()).registerCallback(() => {
    $('#js-terms-and-conditions-print').on('click', function () {
        window.frames['js-terms-and-conditions-frame'].print();
    });
});
