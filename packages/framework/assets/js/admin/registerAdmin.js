import '../common/components';
import '../common/validation';
import '../common/utils/DoubleFormSubmitProtection';

import './components';

import './validation/customization';
import './validation/form';

import Register from '../common/utils/Register';

export default function registerAdmin (afterRegistrationCallback = null) {
    $(document).ready(function () {
        const register = new Register();
        register.registerNewContent($('body'));

        if (afterRegistrationCallback !== null) {
            afterRegistrationCallback();
        }
    });
}
