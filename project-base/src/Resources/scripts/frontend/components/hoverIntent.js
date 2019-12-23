(function ($) {

    Shopsys = window.Shopsys || {};
    Shopsys.hoverIntent = Shopsys.hoverIntent || {};

    Shopsys.hoverIntent.HoverIntentSetting = function ($hoverIntentParent) {
        var interval = 300;
        var timeout = 300;
        var classForOpen = 'open';
        var forceClick = false;
        var linkOnMobile = false;
        var $selector = $hoverIntentParent;

        this.init = function () {
            if ($hoverIntentParent.data('hover-intent-interval')) {
                interval = parseInt($hoverIntentParent.data('hover-intent-interval'));
            }

            if ($hoverIntentParent.data('hover-intent-timeout')) {
                timeout = parseInt($hoverIntentParent.data('hover-intent-timeout'));
            }

            if ($hoverIntentParent.data('hover-intent-class-for-open')) {
                classForOpen = $hoverIntentParent.data('hover-intent-class-for-open');
            }

            if ($hoverIntentParent.data('hover-intent-force-click')) {
                forceClick = $hoverIntentParent.data('hover-intent-force-click');
            }

            if ($hoverIntentParent.data('hover-intent-link-on-mobile')) {
                linkOnMobile = $hoverIntentParent.data('hover-intent-link-on-mobile');
            }
        };

        this.getInterval = function () {
            return interval;
        };

        this.getTimeout = function () {
            return timeout;
        };

        this.getClassForOpen = function () {
            return classForOpen;
        };

        this.getSelector = function () {
            return $selector;
        };

        this.getForceClick = function () {
            return forceClick;
        };

        this.getLinkOnMobile = function () {
            return linkOnMobile;
        };
    };

    Shopsys.hoverIntent.hoverIntent = function (hoverIntentSettings) {
        hoverIntentSettings.forEach(function (hoverIntentSetting) {
            hoverIntentSetting.getSelector().hoverIntent({
                interval: hoverIntentSetting.getInterval(),
                timeout: hoverIntentSetting.getTimeout(),
                over: function () {
                    hideAllOpenedIntent();

                    if(hoverIntentSetting.getForceClick()){
                        $(this).click();
                    }

                    if (hoverIntentSetting.getLinkOnMobile()) {
                        // this removes unneeded opening element when it is only link on mobile
                        if ($(window).width() > Shopsys.responsive.SM) {
                            $(this).addClass(hoverIntentSetting.getClassForOpen());
                        }
                    } else {
                        $(this).addClass(hoverIntentSetting.getClassForOpen());
                    }

                },
                out: function () {
                    if ($(this).find('input:focus').size() === 0) {
                        $(this).removeClass(hoverIntentSetting.getClassForOpen());
                    }

                    if(hoverIntentSetting.getForceClick()){
                        $(this).click();
                    }
                }
            });
        });

        function hideAllOpenedIntent () {
            hoverIntentSettings.forEach(function (hoverIntentSetting) {
                hoverIntentSetting.getSelector().removeClass(hoverIntentSetting.getClassForOpen());
            });
        }

        // hide all opened intent after click wherever instead of element with hover intent
        $('body').click(function (event) {
            if ($(event.target).closest('.js-hover-intent').length === 0) {
                hideAllOpenedIntent();
            }
        });
    };

    Shopsys.register.registerCallback(function ($container) {
        var hoverIntentSettings = [];
        $container.filterAllNodes('.js-hover-intent').each(function () {
            var hoverIntentSetting = new Shopsys.hoverIntent.HoverIntentSetting($(this));
            hoverIntentSetting.init();
            hoverIntentSettings.push(hoverIntentSetting);
        });

        Shopsys.hoverIntent.hoverIntent(hoverIntentSettings);
    });

})(jQuery);
