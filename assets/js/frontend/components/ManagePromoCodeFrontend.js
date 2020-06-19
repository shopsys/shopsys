import 'framework/common/components';
import Ajax from 'framework/common/utils/Ajax';
import { KeyCodes } from 'framework/common/utils/KeyCodes';
import Register from 'framework/common/utils/Register';
import Window from '../utils/Window';
import Translator from 'bazinga-translator';

export default class ManagePromoCodeFrontend {
    findAndValidate () {
        const promoCode = $('#find-and-validate-promo-code').val();
        Ajax.ajax({
            url: '/promo-code/find-and-validate/',
            type: 'POST',
            data: { code: promoCode },
            dataType: 'html',
            success: function (responseHtml) {
                ManagePromoCodeFrontend.showResult(responseHtml);
            }
        });
    }

    static showResult (responseHtml) {
        const findAndValidateResult = $('#find-and-validate-result');
        findAndValidateResult.html(responseHtml);
        (new Register()).registerNewContent(findAndValidateResult.parent());
    }

    confirmUsePromoCode () {
        const promoCodeId = $('#deactivate-promo-code').data('promocode-id');
        // eslint-disable-next-line no-new
        new Window({
            content: Translator.trans('Opravdu chcete kupon použít?'),
            buttonContinue: true,
            buttonClose: true,
            buttonCancel: true,
            textContinue: Translator.trans('Použít'),
            textClose: Translator.trans('Zavřít'),
            textCancel: Translator.trans('Zpět'),
            eventContinue: () => ManagePromoCodeFrontend.usePromoCode(promoCodeId)
        });
    }

    static usePromoCode (promoCodeId) {
        console.log(promoCodeId);
        Ajax.ajax({
            url: '/promo-code/use-code/',
            type: 'POST',
            data: { promoCodeId: promoCodeId },
            dataType: 'json',
            success: function (data) {
                const findAndValidateResult = $('#find-and-validate-result');
                findAndValidateResult.html(data.message);
            }
        });
    }

    static init ($container) {
        const managePromoCodeFrontend = new ManagePromoCodeFrontend();
        $container.filterAllNodes('#find-and-validate-submit').click((event) => managePromoCodeFrontend.findAndValidate());
        $container.filterAllNodes('#find-and-validate-promo-code').keypress(function (event) {
            if (event.keyCode === KeyCodes.ENTER) {
                managePromoCodeFrontend.findAndValidate();
                return false;
            }
        });
        $container.filterAllNodes('#deactivate-promo-code').click((event) => managePromoCodeFrontend.confirmUsePromoCode());
    }
}

new Register().registerCallback(ManagePromoCodeFrontend.init, 'ManagePromoCodeFrontend.init');
