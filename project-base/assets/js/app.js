// import 'jquery-ui-touch-punch';

import 'framework/assets/js/common/components';

import CustomizeBundle from 'framework/assets/js/common/validation/customizeBundle';
import showFormErrorsWindowOnFrontend from './frontend/customizeBundle';

import 'framework/assets/js/common/checkboxToggle';
import 'framework/assets/js/common/loadTranslations';

import './frontend/lazyLoadInit';
import './frontend/cookies';
import './frontend/categoryPanel';
import './frontend/form';
import './frontend/honeyPot';
import './frontend/legalConditions';
import './frontend/login';
import './frontend/newsletterSubscriptionForm';
import './frontend/promoCode';
import './frontend/responsiveToggle';
import './frontend/safariDetection';
import './frontend/spinbox';
import './frontend/rangeSlider';
import './frontend/components/ajaxMoreLoader';
import './frontend/responsiveTooltip';
import './frontend/searchAutocomplete';

import './frontend/validation/form';

// HP entry?
import SlickInit from './frontend/slickInit';

// order entry?
import './frontend/order/order';
import './frontend/order/orderRememberData';
import './frontend/order/preview';

// product entry?
import './frontend/product/addProduct';
import './frontend/product/bestsellingProducts';
import './frontend/product/gallery';
import ProductListAjaxFilter from './frontend/product/productList.AjaxFilter';
import './frontend/product/productList';
import './frontend/product/productListCategoryToggler';

import './frontend/cart/cartBox';

// cart entry?
import './frontend/cart/cartRecalculator';

import 'framework/assets/js/common/validation/customizeFpValidator';
import './frontend/validation/validationInit';
import 'framework/assets/js/common/validation';

import Register from 'framework/assets/js/common/register';

CustomizeBundle.showFormErrorsWindow = showFormErrorsWindowOnFrontend;

$(document).ready(function () {
    const register = new Register();
    register.registerNewContent($('body'));

    SlickInit();
    ProductListAjaxFilter.init();
});

$(window).on('popstate', function (event) {
    const state = event.originalEvent.state;
    if (state && state.hasOwnProperty('refreshOnPopstate') && state.refreshOnPopstate === true) {
        location.reload();
    }
});
