import '../common/components';
import '../common/validation';

import './components';

import './validation/customization';
import './validation/form';

import $ from 'jquery';
import tooltip from '../common/bootstrap/tooltip';

import Register from '../common/utils/register';

tooltip($);

$(document).ready(function () {
    const register = new Register();
    register.registerNewContent($('body'));
});
