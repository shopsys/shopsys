$(document).ready(function() {

    $('.js-web-header-menu-toggle-button').on('click', function(e) {
        $('body').toggleClass("js-side-menu-open");
        $(this).toggleClass("active");
        e.preventDefault();
    });

});
