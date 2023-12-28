import '../jQuery/registerJquery';
import registerAdmin from 'framework/admin/registerAdmin';
import '../loadTranslations';

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

import './product/product';

registerAdmin();
