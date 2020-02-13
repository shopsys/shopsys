import '@claviska/jquery-minicolors';
import Register from '../../common/utils/register';

export default class ColorPicker {

    static init ($container) {
        $container.filterAllNodes('.js-color-picker').minicolors({
            theme: 'bootstrap'
        });
    }

}

(new Register()).registerCallback(ColorPicker.init);
