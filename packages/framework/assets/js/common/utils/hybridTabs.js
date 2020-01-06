/**
 * Hybrid tabs component that can work in two modes:
 * - "single" - only one tab can be selected at once (aka. classic tabs)
 * - "multiple" - multiple tabs can be selected at once (aka. accordion)
 *
 * == Notes ==
 * - There can be more "js-tab-button" for each "js-tab-content" but there
 *   always must be at least one "js-tab-button" for each "js-tab-content".
 * - Default open tabs are determined by setting "active" class to certain
 *   "js-tab-button" buttons.
 *
 * == Examples ==
 * === HTML mark-up ===
 * <div id="container">
 *     <a href="#" class="js-tab-button" data-tab-id="content-a"></a>
 *     <a href="#" class="js-tab-button" data-tab-id="content-b"></a>
 *
 *     <div class="js-tab-content" data-tab-id="content-a"></div>
 *     <div class="js-tab-content" data-tab-id="content-b"></div>
 * </div>
 *
 * === Single tab mode initialization ===
 * var hybridTabs = new Shopsys.hybridTabs.HybridTabs($('#container'));
 * hybridTabs.init(Shopsys.hybridTabs.TABS_MODE_SINGLE);
 *
 * === Multiple tabs mode initialization ===
 * var hybridTabs = new Shopsys.hybridTabs.HybridTabs($('#container'));
 * hybridTabs.init(Shopsys.hybridTabs.TABS_MODE_MULTIPLE);
 */

export default class HybridTabs {

    constructor ($container) {
        this.$tabButtons = $container.find('.js-tabs-button');
        this.$tabContents = $container.find('.js-tabs-content');
        this.tabsMode = null;
    }

    init (initialTabsMode) {
        this.tabsMode = initialTabsMode;

        const _this = this;
        this.$tabButtons.click((event) => HybridTabs.onClickTabButton(event, _this));

        this.fixTabsState();
    }

    setTabsMode (newTabsMode) {
        this.tabsMode = newTabsMode;
        this.fixTabsState();
    }

    fixTabsState () {
        if (this.tabsMode === HybridTabs.TABS_MODE_SINGLE) {
            const $activeButtons = this.$tabButtons.filter('.active');
            if ($activeButtons.length > 0) {
                this.activateOneTabAndDeactivateOther($activeButtons.last().data('tab-id'));
            } else {
                this.activateOneTabAndDeactivateOther(this.$tabButtons.first().data('tab-id'));
            }
        } else if (this.tabsMode === HybridTabs.TABS_MODE_MULTIPLE) {
            const _this = this;
            this.$tabContents.each(function () {
                const tabId = $(this).data('tab-id');
                const $tabButton = _this.$tabButtons.filter('[data-tab-id="' + tabId + '"]');
                const isTabActive = $tabButton.hasClass('active');

                _this.toggleTab(tabId, isTabActive);
            });
        }
    }

    static onClickTabButton (event, hybridTabs) {
        const tabId = $(event.currentTarget).data('tab-id');
        if (hybridTabs.tabsMode === HybridTabs.TABS_MODE_SINGLE) {
            hybridTabs.activateOneTabAndDeactivateOther(tabId);
        } else if (hybridTabs.tabsMode === HybridTabs.TABS_MODE_MULTIPLE) {
            const isTabActive = $(event.currentTarget).hasClass('active');
            hybridTabs.toggleTab(tabId, !isTabActive);
        }

        return false;
    }

    // activates exactly one tab (in "single" mode)
    activateOneTabAndDeactivateOther (tabId) {
        const _this = this;
        this.$tabButtons.each(function () {
            const currentTabId = $(this).data('tab-id');
            const isCurrentTab = currentTabId === tabId;

            _this.toggleTab(currentTabId, isCurrentTab);
        });
    }

    // use true to show the tab or false to hide it without checking single/multiple mode
    toggleTab (tabId, display) {
        const $tabButton = this.$tabButtons.filter('[data-tab-id="' + tabId + '"]');
        const $tabContent = this.$tabContents.filter('[data-tab-id="' + tabId + '"]');

        $tabButton.toggleClass('active', display);
        $tabContent.toggleClass('active', display);
    }
}

HybridTabs.TABS_MODE_SINGLE = 'single';
HybridTabs.TABS_MODE_MULTIPLE = 'multiple';
