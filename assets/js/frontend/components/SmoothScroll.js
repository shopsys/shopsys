import Register from 'framework/common/utils/Register';

export default class SmoothScroll {

    constructor ($smoothScroll) {
        this.$smoothScroll = $smoothScroll;

        this.$smoothScroll.on('click', function (e) {
            e.preventDefault();
            let target = $(this.hash);

            target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');

            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top
                }, 500, function () {
                    let $target = $(target);
                    $target.focus();
                    if ($target.is(':focus')) {
                        return false;
                    } else {
                        $target.attr('tabindex', '-1');
                        $target.focus();
                    }
                });
            }
        });
    }

    static init ($container) {
        $container.filterAllNodes('.js-smooth-scroll-anchor').each((index, element) => {
            // eslint-disable-next-line no-new
            new SmoothScroll($(element));
        });
    }
}

(new Register()).registerCallback(SmoothScroll.init, 'SmoothScroll.init');
