import $ from 'jquery';

// import 'jquery-ui-touch-punch';

import './copyFromFw/loadTranslations';
import './copyFromFw/components';

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
// import './frontend/validation/form';

// HP entry?
import './frontend/slickInit';

// order entry?
import './frontend/order/order';
import './frontend/order/orderRememberData';
import './frontend/order/preview';

// product entry?
import './frontend/product/addProduct';
import './frontend/product/bestsellingProducts';
import './frontend/product/gallery';
import './frontend/product/productList.AjaxFilter';
import './frontend/product/productList';
import './frontend/product/productListCategoryToggler';

import './frontend/cart/cartBox';

// cart entry?
import './frontend/cart/cartRecalculator';

import Register from './copyFromFw/register';

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
