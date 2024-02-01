// Script is named "noticer.js" because scripts named "advert.js" are often blocked by browser (e.g. by AdBlock plugin)

import Register from '../../../common/utils/Register';
import { VALIDATION_GROUP_DEFAULT } from './validation';

export default function validationAdvert () {
    const $advertForm = $('form[name="advert_form"]');

    const getCheckedType = function () {
        return $advertForm.find('input[name="advert_form[settings][type]"]:checked').val();
    };

    const initAdvertForm = function () {
        $advertForm
            .find('.js-advert-type-content').hide()
            .filter('[data-type=' + getCheckedType() + ']').show();
    };

    $advertForm.find('input[name="advert_form[settings][type]"]').change(initAdvertForm);
    initAdvertForm();

    const getPositionName = function () {
        return $('select[name="advert_form[settings][positionName]"]').val();
    };

    const initAdvertProductList = function () {
        if (getPositionName() === 'productList') {
            $advertForm.find('.js-category-tree-form-children-container').closest('.form-line').show();
        } else {
            $advertForm.find('.js-category-tree-form-children-container').closest('.form-line').hide();
        }
    };

    $advertForm.find('select[name="advert_form[settings][positionName]"]').change(initAdvertProductList);
    initAdvertProductList();

    $advertForm.jsFormValidator({
        'groups': function () {
            const groups = [VALIDATION_GROUP_DEFAULT];

            const checkedType = getCheckedType();
            if (checkedType === 'code') {
                groups.push('typeCode');
            } else if (checkedType === 'image') {
                groups.push('typeImage');
            }

            return groups;
        }
    });
}

(new Register()).registerCallback(validationAdvert, 'validationAdvert');
