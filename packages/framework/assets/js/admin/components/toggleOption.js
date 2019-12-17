/**
 * IE compatible hiding of select's options
*/

export default class ToggleOption {

    static hide ($element) {
        $element.hide();
        if ($element.parent('span.' + ToggleOption.wrapperClass).length === 0) {
            $element.wrap('<span class="' + ToggleOption.wrapperClass + '" style="display: none;" />');
        }
    };

    static show ($element) {
        $element.show();
        if ($element.parent('span.' + ToggleOption.wrapperClass).length > 0) {
            $element.unwrap();
        }
    }

}

ToggleOption.wrapperClass = 'js-toggle-option-wrapper';
