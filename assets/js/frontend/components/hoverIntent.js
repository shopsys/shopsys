import Responsive from '../utils/responsive';
import HoverIntentSetting from './HoverIntentSetting';
import Register from 'framework/common/utils/register';
import $ from 'jquery';
import 'jquery-hoverintent/jquery.hoverIntent';

export default class HoverIntent {

    constructor (hoverIntentSettings) {
        hoverIntentSettings.forEach(hoverIntentSetting => {

            if (Responsive.isDesktopVersion()) {
                hoverIntentSetting.getSelector().hover(event => {
                    event.target.classList.add('intented');
                });
            }

            hoverIntentSetting.getSelector().hoverIntent({
                interval: hoverIntentSetting.getInterval(),
                timeout: hoverIntentSetting.getTimeout(),
                over: function () {
                    HoverIntent.hideAllOpenedIntent(hoverIntentSettings);
                    if (hoverIntentSetting.getForceClick()) {
                        $(this).find(hoverIntentSetting.getForceClickElement()).click();
                    }

                    if (hoverIntentSetting.getLinkOnMobile()) {
                        // this removes unneeded opening element when it is only link on mobile
                        if ($(window).width() > Responsive.SM) {
                            $(this).addClass(hoverIntentSetting.getClassForOpen());
                        }
                    } else {
                        $(this).addClass(hoverIntentSetting.getClassForOpen());
                    }

                },
                out: function () {
                    if ($(this).find('input:focus').length === 0) {
                        $(this).removeClass(hoverIntentSetting.getClassForOpen());
                    }

                    if (hoverIntentSetting.getForceClick()) {
                        $(this).find(hoverIntentSetting.getForceClickElement()).click();
                    }
                }
            });
        });
    }

    static hideAllOpenedIntent (hoverIntentSettings) {
        hoverIntentSettings.forEach(hoverIntentSetting => {
            hoverIntentSetting.getSelector().removeClass(hoverIntentSetting.getClassForOpen());
        });
    }

    static init ($container) {

        const hoverIntentSettings = [];
        $container.filterAllNodes('.js-hover-intent').each((index, element) => {
            const hoverIntentSetting = new HoverIntentSetting($(element));
            hoverIntentSettings.push(hoverIntentSetting);
        });

        // hide all opened intent after click wherever instead of element with hover intent
        const hideAllOpenedIntentsEvent = (event) => {
            if ($(event.target).closest('.js-hover-intent').length === 0) {
                HoverIntent.hideAllOpenedIntent(hoverIntentSettings);
            }
        };

        $('body').off('click', hideAllOpenedIntentsEvent);
        $('body').on('click', hideAllOpenedIntentsEvent);

        // eslint-disable-next-line no-new
        new HoverIntent(hoverIntentSettings);
    }
}

if (Responsive.isDesktopVersion()) {
    (new Register()).registerCallback(HoverIntent.init);
}
