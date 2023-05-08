import 'magnific-popup';
import Register from 'framework/common/utils/Register';

export default class Popup {

    static init ($container) {
        $container.filterAllNodes('.js-popup-image').magnificPopup({
            type: 'image'
        });
    }
}

new Register().registerCallback(Popup.init);
