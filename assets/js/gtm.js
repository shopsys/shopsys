import 'intersection-observer';
import Register from 'framework/common/utils/Register';

const EVENT_NAME_CATEGORY_FILTER = 'category.filter';
const EVENT_NAME_CHECKOUT_OPTION = 'ec.checkout_option';

export default class Gtm {
    static clickPush (e) {
        if ($(this).parents('.is-not-clickable').length) {
            return;
        }

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
    }

    static initScrollerPush (scroller) {
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

    static pushTransportAndPayment (form, e) {
        e.preventDefault();

        let options = [];
        $('form[name=transport_and_payment_form]').find('input:checkbox:checked').each((index, val) => {
            options.push($(val).closest('label').find('span.box-chooser__item__title strong').text().trim());
        });

        const data = {
            'event': EVENT_NAME_CHECKOUT_OPTION,
            'ecommerce': {
                'currencyCode': currencyCode,
                'checkout_option': {
                    'actionField': {
                        'step': 1,
                        'option': options.join('|')
                    }
                }
            }
        };

        /* eslint-disable camelcase */
        if (typeof google_tag_manager != 'undefined') {
            dataLayer.push(
                $.extend({}, data, {
                    'eventCallback': function (gtmId) {
                        $(form).submit();
                    },
                    'eventTimeout': 2000
                })
            );
        }
    }

    static pushFilterData (serializedData) {
        let index = serializedData.length - 1;
        while (index >= 0) {
            if (serializedData[index].value === '' || serializedData[index].value === null) {
                serializedData.splice(index, 1);
            }
            index--;
        }

        try {
            var originalValues = JSON.parse(localStorage.getItem('filter'));
        } catch (e) {
            var originalValues = [];
        }

        localStorage.setItem('filter', JSON.stringify(serializedData));

        const newValues = $.isEmptyObject(originalValues) ? serializedData : serializedData.filter(Gtm.compare(originalValues));
        newValues.forEach(item => {
            if (item.name !== 'q') {
                const section = $(`*[name="${item.name}"]`).closest('.js-product-filter-box').children('.js-product-filter-box-label').text().trim();
                const valueEl = $(`*[name="${item.name}"][value="${item.value}"]`).siblings('label').children('a');
                valueEl.find('span').remove();
                const value = valueEl.text().trim();

                const data = {
                    'event': EVENT_NAME_CATEGORY_FILTER,
                    'eventData': {
                        'category': 'Filtrace',
                        'action': section,
                        'label': value
                    }
                };
                Gtm.pushEvent(data);
            }
        });
    }

    static pushSortData (text) {
        const data = {
            'event': EVENT_NAME_CATEGORY_FILTER,
            'eventData': {
                'category': 'Filtrace',
                'action': 'Řazení',
                'label': text.trim()
            }
        };
        Gtm.pushEvent(data);
    }

    static pushEvent (data) {
        if (typeof dataLayer != 'undefined') {
            dataLayer.push(data);
        }
    }

    static init ($container) {
        $container.find('a').filter(function () {
            return $(this).data('gtm-event') !== undefined;
        }).on('click', Gtm.clickPush);

        if ($container.find('.gtm-scroll').length > 0) {
            const scrollama = require('scrollama');
            var scroller = scrollama();

            Gtm.initScrollerPush(scroller);
        }

        $('.js-transport_and_payment_form_save').click(function (e) {
            Gtm.pushTransportAndPayment($(this.closest('form')), e);
        });
    }

    static compare (otherArray) {
        return function (current) {
            return otherArray.filter(function (other) {
                return other.value == current.value && other.display == current.display;
            }).length == 0;
        };
    }
}

(new Register()).registerCallback(Gtm.init, 'Gtm.init');
