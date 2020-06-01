import 'framework/common/components';
import Register from 'framework/common/utils/Register';

export default class FormField {

    changeFormFieldFocus() {
        const $formField = $('.js-form-field').find('.input');
        const focusClass = 'is-focused';

        $formField.each(function() {
            const $currField = $(this);
            const $parent = $currField.parents('.js-form-field');

            if ($currField.val() != '') {
                $parent.addClass(focusClass);
            }

            $currField.on({
                focus: function() {
                    $parent.addClass(focusClass);
                },

                blur: function() {
                    if ($currField.val() == '') {
                        $parent.removeClass(focusClass);
                    }
                }
            })
        });
    }

    static init () {
        const Field = new FormField();
        Field.changeFormFieldFocus();
    }
}

(new Register()).registerCallback(FormField.init);
