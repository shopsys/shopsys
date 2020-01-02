import $ from 'jquery';

import '../common/components';
import '../common/components/tabs';
import '../common/components/datePicker';
import '../common/checkboxToggle';

import './article';
import './statistics';
import './sideMenu';
import './toggleMenu';
import './advancedSearch';
import './charactersCounter';
import './product';
import './dynamicPlaceholder';
import './product-visibility';
import './formChangeInfo';
import './parameters';
import './productPicker';
import './productsPicker';
import './productsPicker.window';
import './categoryTree.sorting';
import './categoryTree.form';
import './freeTransportAndPayment';
import './entityUrlList.row';
import './entityUrlList.newUrl';
import './measuringScript';
import './massAction';
import './massActionConfirm';
import './order';
import './orderItems';
import './orderTransportAndPayment';
import './sortableValues';
import './symfonyToolbarSupport';
import './fixedBar';
import './domainIcon';
import './fileUpload';
import './staticConfirmWindow';
import './components/selectToggle';

import './components';

import '../common/components/toggleElement';
import '../common/validation/customizeFpValidator';
import '../common/validation/validation';
import '../common/validation';

import './validation/ShopsysShopBundleComponentTransformersProductParameterValueToProductParameterValuesLocalizedTransformer';
import './validation/ShopsysShopBundleComponentTransformersRemoveWhitespacesTransformer';
import './validation/form';

import Register from '../common/register';

$(document).ready(function () {
    const register = new Register();
    register.registerNewContent($('body'));
});
