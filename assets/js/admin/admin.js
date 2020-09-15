import './article/article';

import '../jQuery/registerJquery';
import registerAdmin from 'framework/admin/registerAdmin';
import '../loadTranslations';
import './order/orderDisablingForm';

import './payment/payment';
import './payment/paymentFormValidation';

import './transport/transportForm';
import './transport/transportPackages';
import './transport/transportFormValidation';

import './validation';
import './advert/advert';
import './../common/validation/customizeFpValidator';

import './promocode/promocode';

registerAdmin();
