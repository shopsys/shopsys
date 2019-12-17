// Script is named "noticer.js" because scripts named "advert.js" are often blocked by browser (e.g. by AdBlock plugin)

import constant from '../../constant';
import Register from '../../../common/register';

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

    $advertForm.jsFormValidator({
        'groups': function () {
            const groups = [constant('\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];

            const checkedType = getCheckedType();
            if (checkedType === constant('\\Shopsys\\FrameworkBundle\\Model\\Advert\\Advert::TYPE_CODE')) {
                groups.push(constant('\\Shopsys\\FrameworkBundle\\Form\\Admin\\Advert\\AdvertFormType::VALIDATION_GROUP_TYPE_CODE'));
            } else if (checkedType === constant('\\Shopsys\\FrameworkBundle\\Model\\Advert\\Advert::TYPE_IMAGE')) {
                groups.push(constant('\\Shopsys\\FrameworkBundle\\Form\\Admin\\Advert\\AdvertFormType::VALIDATION_GROUP_TYPE_IMAGE'));
            }

            return groups;
        }
    });
}

(new Register()).registerCallback(validationAdvert);
