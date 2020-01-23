/**
 * Responsive tabs component that uses HybridTabs component to show
 * single tab mode (classic tabs) in desktop view and multiple tabs mode
 * (aka. accordion) in mobile view.
 *
 * @see Shopsys.HybridTabs
 *
 * == Notes ==
 * - There must be at least one "js-tab-button" for each "js-tab-content".
 * - You must hide desktop buttons in mobile view and mobile buttons in desktop
 *   view using CSS.
 *
 * == Example ==
 * === HTML mark-up ===
 * <div class="js-responsive-tabs">
 *     <a href="#" class="js-tab-button desktop-button" data-tab-id="content-a"></a>
 *     <a href="#" class="js-tab-button desktop-button" data-tab-id="content-b"></a>
 *
 *     <a href="#" class="js-tab-button mobile-button" data-tab-id="content-a"></a>
 *     <div class="js-tab-content" data-tab-id="content-a"></div>
 *
 *     <a href="#" class="js-tab-button mobile-button" data-tab-id="content-b"></a>
 *     <div class="js-tab-content" data-tab-id="content-b"></div>
 * </div>
 *
 * === LESS ===
 * @media @query-lg {
 *     .desktop-button {
 *         display: none;
 *     }
 * }
 * @media @query-xl {
 *     .mobile-button {
 *         display: none;
 *     }
 * }
 *
 * === JavaScript ===
 * There is no need to initialize the component in JavaScript.
 * It is automatically initialized on all DOM containers with class "js-responsive-tabs".
 */

import HybridTabs from 'framework/common/utils/hybridTabs';
import Responsive from '../utils/responsive';
import Register from 'framework/common/utils/register';

export default class ResponsiveTabsInit {

    static init ($container) {
        $container.filterAllNodes('.js-responsive-tabs').each((index, element) => {
            // eslint-disable-next-line no-new
            new ResponsiveTabsInit($(element));
        });
    }

    constructor ($tab) {
        const hybridTabs = new HybridTabs($tab);
        const responsive = new Responsive();
        const _this = this;

        hybridTabs.init(this.getHybridTabsModeForCurrentResponsiveMode());
        responsive.registerOnLayoutChange(() => {
            hybridTabs.setTabsMode(_this.getHybridTabsModeForCurrentResponsiveMode());
        });
    }

    getHybridTabsModeForCurrentResponsiveMode () {
        if (Responsive.isDesktopVersion()) {
            return HybridTabs.TABS_MODE_SINGLE;
        } else {
            return HybridTabs.TABS_MODE_MULTIPLE;
        }
    }
}

(new Register()).registerCallback(ResponsiveTabsInit.init);
