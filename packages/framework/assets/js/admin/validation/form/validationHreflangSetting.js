import Register from '../../../common/utils/Register';

export default function validationHreflangSetting () {
    const hreflangForm = $('#hreflang_setting_form_hreflang_collection');
    hreflangForm.jsFormValidator({
        callbacks: {
            validateDomainUniqueness: function () {

            }
        }
    });
}

(new Register()).registerCallback(validationHreflangSetting, 'validationHreflangSetting');
