import $ from 'jquery';
import constant from './constant';
import Register from '../copyFromFw/register';

const cookieName = constant('\\Shopsys\\FrameworkBundle\\Model\\Cookies\\CookiesFacade::EU_COOKIES_COOKIE_CONSENT_NAME');
const tenYears = 10 * 365;

export default function cookieInit () {
    $('.js-eu-cookies-consent-button').click(function () {
        const $cookiesFooterGap = $('.js-eu-cookies-consent-footer-gap');
        const $cookiesBlock = $('.js-eu-cookies');
        $.cookie(cookieName, true, { expires: tenYears, path: '/' });

        $cookiesBlock.addClass('box-cookies--closing');
        $cookiesFooterGap.removeClass('web__footer--with-cookies');
    });
}

(new Register()).registerCallback(cookieInit);
