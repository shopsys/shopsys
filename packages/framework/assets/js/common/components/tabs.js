/**
 * Classic tabs component that uses HybridTabs component in single tab mode.
 *
 * @see Shopsys.HybridTabs
 *
 * == Notes ==
 * - There must be at least one "js-tab-button" for each "js-tab-content".
 * - Default open tab is determined by setting "active" class to a certain
 *   "js-tab-button" button.
 *
 * == Example ==
 * === HTML mark-up ===
 * <div class="js-tabs">
 *     <a href="#" class="js-tab-button" data-tab-id="content-a"></a>
 *     <a href="#" class="js-tab-button" data-tab-id="content-b"></a>
 *
 *     <div class="js-tab-content" data-tab-id="content-a"></div>
 *     <div class="js-tab-content" data-tab-id="content-b"></div>
 * </div>
 *
 * === JavaScript ===
 * There is no need to initialize the component in JavaScript.
 * It is automatically initialized on all DOM containers with class "js-tabs".
 */

import HybridTabs from './hybridTabs';
import Register from '../register';

export default function tabs ($container) {
    $container.filterAllNodes('.js-tabs').each(function () {
        const hybridTabs = new HybridTabs($(this));
        hybridTabs.init(HybridTabs.TABS_MODE_SINGLE);
    });
}

(new Register()).registerCallback(tabs);
