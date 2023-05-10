import Register from 'framework/common/utils/Register';
import constant from '../utils/constant';

(new Register()).registerCallback($container => {
    const getCheckedPositionName = function () {
        return $('#advert_form_settings_positionName').val();
    };

    const initAdvertForm = function () {
        if (getCheckedPositionName() === constant('\\App\\Model\\Advert\\AdvertPositionRegistry::CATEGORIES_ABOVE_PRODUCT_LIST')) {
            $('#advert_form_settings').find('.js-category-tree-form').closest('.form-line').show();
        } else {
            $('#advert_form_settings').find('.js-category-tree-form').closest('.form-line').hide();
        }
    };

    initAdvertForm();
    $('#advert_form_settings_positionName').change(initAdvertForm);
});
