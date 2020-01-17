// import 'jquery-ui-touch-punch';

import tooltip from 'framework/common/bootstrap/tooltip';

import 'framework/common/components';

import CustomizeBundle from 'framework/common/validation/customizeBundle';
import showFormErrorsWindowOnFrontend from './frontend/utils/customizeBundle';

import './loadTranslations';

import './frontend/components';

import './frontend/validation/form';

// HP entry?
import './frontend/homepage/slickInit';

// order entry?
import './frontend/order';

// product entry?
import './frontend/product';

import './frontend/cart/cartBox';

// cart entry?
import './frontend/cart/cartRecalculator';

import 'framework/common/validation/customizeFpValidator';
import './frontend/validation/validationInit';
import 'framework/common/validation';

import Register from 'framework/common/utils/register';
const $ = window.jQuery || global.jQuery || jQuery;
tooltip($);

CustomizeBundle.showFormErrorsWindow = showFormErrorsWindowOnFrontend;

$(document).ready(function () {
    const register = new Register();
    register.registerNewContent($('body'));
});

$(window).on('popstate', function (event) {
    const state = event.originalEvent.state;
    if (state && state.hasOwnProperty('refreshOnPopstate') && state.refreshOnPopstate === true) {
        location.reload();
    }
});
