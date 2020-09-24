import 'framework/common/components';
import Register from 'framework/common/utils/Register';

export default class FormField {

    changeFormFieldFocus () {
        const $formField = $('.js-form-field').find('.input');
        const focusClass = 'is-focused';
        const focusClassOnError = 'is-focused-error';
        const classOnBlur = 'has-blur';

        $formField.each(function () {
            const $currField = $(this);
            const $parent = $currField.parents('.js-form-field');

            if ($currField.val() != '') {
                $parent.addClass(focusClass);
            }

            if ($currField.parents('.js-trigger-focus').length) {
                // Manually trigger focus because of webkit-autofill, which is not currently detectable in chrome
                // (since 2020)
                setTimeout(function () {
                    $currField.trigger('focus');
                }, 0);
            }

            $currField.on({
                focus: function () {
                    $parent.addClass(focusClass);
                    if ($parent.find('.form-error__icon').is(':visible')) {
                        $parent.addClass(focusClassOnError);
                        $parent.removeClass(classOnBlur);
                    }
                },

                blur: function () {
                    $parent.removeClass(focusClassOnError);
                    if ($currField.val() == '') {
                        $parent.removeClass(focusClass);
                    } else {
                        if ($parent.find('.form-error__icon').is(':hidden')) {
                            $parent.addClass(classOnBlur);
                        }
                    }
                }
            });
        });
    }

    static init () {
        const Field = new FormField();
        Field.changeFormFieldFocus();
    }
}

(new Register()).registerCallback(FormField.init);
