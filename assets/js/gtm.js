import 'intersection-observer';
const scrollama = require('scrollama');
var scroller = scrollama();

$(document).ready(function () {
    $('a').filter(function () {
        return $(this).data('gtm-event') !== undefined;
    }).click(function (e) {
        /* eslint-disable camelcase */
        if (typeof google_tag_manager != 'undefined') {
            e.preventDefault();
        }

        let ctrl = false;
        let url = this.href;

        if (e.ctrlKey) {
            ctrl = true;
        }

        /* eslint-disable camelcase */
        if (typeof google_tag_manager != 'undefined') {
            dataLayer.push(
                $.extend({}, $(this).data('gtm-event'), {
                    'eventCallback': function (gtmId) {
                        if (ctrl === true) {
                            window.open(url);
                        } else {
                            document.location = url;
                        }
                    },
                    'eventTimeout': 2000
                })
            );
        }
    });

    if ($('.gtm-scroll').length > 0) {
        scroller
            .setup({
                step: '.gtm-scroll',
                once: true,
                offset: 0.5
            })
            .onStepEnter(response => {
                /* eslint-disable camelcase */
                if (typeof google_tag_manager != 'undefined') {
                    dataLayer.push($(response.element).data('gtm-event'));
                }
            });
    }
});
