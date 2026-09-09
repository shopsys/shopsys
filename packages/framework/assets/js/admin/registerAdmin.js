import $ from 'jquery';
import '../common/components';

import './components';
import './search/search';

import Register from '../common/utils/Register';

export default function registerAdmin(afterRegistrationCallback = null) {
    $(document).ready(() => {
        const register = new Register();
        register.registerNewContent($('body'));

        if (afterRegistrationCallback !== null) {
            afterRegistrationCallback();
        }
    });
}
