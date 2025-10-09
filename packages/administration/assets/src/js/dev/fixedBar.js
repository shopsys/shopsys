import Register from 'framework/common/utils/Register';

export default class FixedBar {
    static adjustFixedBarPosition($fixedBar) {
        const $sfToolbar = $('[id^="sfToolbarMainContent-"]');

        if ($sfToolbar.length > 0 && $sfToolbar.is(':visible')) {
            const toolbarHeight = $sfToolbar.outerHeight() || 0;
            $fixedBar.css('bottom', `${toolbarHeight}px`);
        } else {
            $fixedBar.css('bottom', '');
        }
    }

    static waitForToolbarAndAdjust($fixedBar, maxWaitTime = 5000, pollInterval = 100) {
        const startTime = Date.now();

        const checkToolbar = () => {
            const $sfToolbar = $('[id^="sfToolbarMainContent-"]');

            if ($sfToolbar.length > 0 && $sfToolbar.is(':visible')) {
                // Toolbar found, adjust position
                FixedBar.adjustFixedBarPosition($fixedBar);
                return;
            }

            // Check if we've exceeded max wait time
            if (Date.now() - startTime < maxWaitTime) {
                setTimeout(checkToolbar, pollInterval);
            }
        };

        checkToolbar();
    }

    static init($container) {
        const $fixedBars = $container.filterAllNodes('[data-js-fixed-bar]');

        if ($fixedBars.length === 0) {
            return;
        }

        // Adjust the position of fixed bars on the initial load
        $fixedBars.each(function () {
            const $fixedBar = $(this);

            FixedBar.waitForToolbarAndAdjust($fixedBar);
        });

        // Adjust the position of fixed bars when the Symfony toolbar is toggled
        $(document).on('click', '[id^="sfToolbarHideButton-"], [id^="sfToolbarMiniToggler-"]', () => {
            setTimeout(() => {
                $fixedBars.each(function () {
                    const $fixedBar = $(this);
                    FixedBar.adjustFixedBarPosition($fixedBar);
                });
            }, 100);
        });

        // Adjust the position of fixed bars on the window resize
        $(window).on('resize', () => {
            $fixedBars.each(function () {
                const $fixedBar = $(this);
                FixedBar.adjustFixedBarPosition($fixedBar);
            });
        });
    }
}

new Register().registerCallback(FixedBar.init, 'FixedBar.init');
