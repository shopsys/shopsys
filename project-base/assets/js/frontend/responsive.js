import $ from 'jquery';
import Timeout from '../copyFromFw/components/timeout';

export default class Responsive {

    constructor () {

        this.onLayoutChangeListeners = [];
        this.lastIsDesktop = null;

        const _this = this;
        $(window).resize(function () {
            Timeout.setTimeoutAndClearPrevious('Shopsys.responsive.window.resize', () => _this.onWindowResize(_this), 200);
        });
    }

    static isDesktopVersion () {
        const windowWidth = window.innerWidth || $(window).width();
        return windowWidth >= Responsive.LG;
    };

    registerOnLayoutChange (callback) {
        this.onLayoutChangeListeners.push(callback);
    };

    onWindowResize (responsive) {
        if (responsive.lastIsDesktop !== Responsive.isDesktopVersion()) {
            $.each(responsive.onLayoutChangeListeners, function (index, callback) {
                callback(Responsive.isDesktopVersion());
            });

            responsive.lastIsDesktop = Responsive.isDesktopVersion();
        }
    }
}

Responsive.XS = 320;
Responsive.SM = 480;
Responsive.MD = 600;
Responsive.LG = 769;
Responsive.VL = 980;
Responsive.XL = 1200;
