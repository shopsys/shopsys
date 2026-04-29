import Register from 'framework/common/utils/Register';

export default class FixedBar {
    static adjustToolbarHeightProperty() {
        const toolbarContainer = document.querySelector('.sf-toolbar');

        if (toolbarContainer === null) {
            document.documentElement.style.removeProperty('--admin-sf-toolbar-height');

            return;
        }

        const toolbarHeight = toolbarContainer.getBoundingClientRect().height;

        if (toolbarHeight > 0) {
            document.documentElement.style.setProperty('--admin-sf-toolbar-height', `${toolbarHeight}px`);
        } else {
            document.documentElement.style.removeProperty('--admin-sf-toolbar-height');
        }
    }

    static adjustFixedBarsPosition($fixedBars) {
        FixedBar.adjustToolbarHeightProperty();

        $fixedBars.each(function () {
            const $fixedBar = $(this);
            FixedBar.adjustFixedBarPosition($fixedBar);
        });
    }

    static adjustFixedBarPosition($fixedBar) {
        const $sfToolbar = $('[id^="sfToolbarMainContent-"]');

        if ($sfToolbar.length > 0 && $sfToolbar.is(':visible')) {
            const toolbarHeight = $sfToolbar.outerHeight() || 0;
            $fixedBar.css('bottom', `${toolbarHeight}px`);
        } else {
            $fixedBar.css('bottom', '');
        }
    }

    static observeToolbarChanges($fixedBars) {
        const toolbarContainer = document.querySelector('[id^="sfToolbarMainContent-"]')?.closest('.sf-toolbar');

        if (toolbarContainer === null) {
            return;
        }

        const mutationObserver = new MutationObserver(() => FixedBar.adjustFixedBarsPosition($fixedBars));
        mutationObserver.observe(toolbarContainer, { attributes: true, attributeFilter: ['class', 'style'] });
    }

    static waitForToolbarAndAdjust($fixedBars, maxWaitTime = 5000, pollInterval = 100) {
        const startTime = Date.now();

        const checkToolbar = () => {
            const $sfToolbar = $('[id^="sfToolbarMainContent-"]');

            if ($sfToolbar.length > 0) {
                FixedBar.adjustFixedBarsPosition($fixedBars);
                FixedBar.observeToolbarChanges($fixedBars);

                return;
            }

            if (Date.now() - startTime < maxWaitTime) {
                setTimeout(checkToolbar, pollInterval);
            }
        };

        checkToolbar();
    }

    static init($container) {
        const $fixedBars = $container.filterAllNodes('[data-js-fixed-bar]');

        // Adjust fixed bars and sidebar offset when the Symfony toolbar is present
        FixedBar.waitForToolbarAndAdjust($fixedBars);

        // Adjust fixed bars and sidebar offset when the Symfony toolbar is toggled
        $(document).on('click', '[id^="sfToolbarHideButton-"], [id^="sfToolbarMiniToggler-"]', () => {
            setTimeout(() => FixedBar.adjustFixedBarsPosition($fixedBars), 100);
        });

        // Adjust fixed bars and sidebar offset on the window resize
        $(window).on('resize', () => {
            FixedBar.adjustFixedBarsPosition($fixedBars);
        });
    }
}

new Register().registerCallback(FixedBar.init, 'FixedBar.init');
