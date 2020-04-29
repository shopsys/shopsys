import './jQuery/registerJquery';
import 'jquery-ui/ui/widgets/mouse';
import 'jquery-ui-touch-punch';

import tooltip from 'framework/common/bootstrap/tooltip';

import 'framework/common/components';

import CustomizeBundle from 'framework/common/validation/customizeBundle';
import showFormErrorsWindowOnFrontend from './frontend/utils/showFormErrorsWindow';

import './loadTranslations';

import './frontend/components';

import './frontend/validation/form';

// HP entry?
import './frontend/homepage/slickInit';

import './frontend/deliveryAddress';

// order entry?
import './frontend/order';

// product entry?
import './frontend/product';

// cart entry?
import './frontend/cart';

import 'framework/common/validation';

import Register from 'framework/common/utils/Register';

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
