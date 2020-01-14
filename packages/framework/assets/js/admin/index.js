import $ from 'jquery';

import '../common/components';
import '../common/validation';

import './components';

import './validation/customization';
import './validation/form';

import Register from '../common/utils/register';

$(document).ready(function () {
    const register = new Register();
    register.registerNewContent($('body'));
});
