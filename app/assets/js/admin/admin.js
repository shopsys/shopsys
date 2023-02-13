import './article/article';

import '../jQuery/registerJquery';
import registerAdmin from 'framework/admin/registerAdmin';
import '../loadTranslations';

import './payment/payment';

import './validation';
import './advert/advert';
import './../common/validation/customizeFpValidator';

import './promocode/promocode';
import './promocode/promoCodeGroup';
import './promocode/promoCodeFlags';

import './category/categoryDeleteConfirm';

import './grapesjs/initGrapesJs';
import './roleGroup/roleGroups';
import './roleGroup/administratorForm';

import './order/executeRefund';

registerAdmin();
