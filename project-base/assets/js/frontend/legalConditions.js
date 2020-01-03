import Register from 'framework/common/register';

(new Register()).registerCallback(() => {
    $('#js-terms-and-conditions-print').on('click', function () {
        window.frames['js-terms-and-conditions-frame'].print();
    });
});
