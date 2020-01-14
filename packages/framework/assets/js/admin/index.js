import tooltip from '../common/bootstrap/tooltip';

import '../common/components';
import '../common/validation';

import './components';

import './validation/customization';
import './validation/form';

import Register from '../common/utils/register';
const $ = window.jQuery || global.jQuery || jQuery;
tooltip($);

$(document).ready(function () {
    const register = new Register();
    register.registerNewContent($('body'));
});
