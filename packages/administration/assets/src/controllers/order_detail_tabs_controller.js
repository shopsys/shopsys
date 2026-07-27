import { Controller } from '@hotwired/stimulus';
import Translator from 'bazinga-translator';
import Register from 'framework/common/utils/Register';

export default class extends Controller {
    static targets = ['pane'];
    static values = {
        historyTabId: String,
        orderUpdatedEvent: String,
    };

    connect() {
        this.pendingTabSwitch = null;
        this.onShow = event => this.prepareTabSwitch(event.target);
        this.onShown = event => {
            const pane = this.findPane(event.target);
            const loading = this.loadTab(event.target);

            if (loading === null) {
                this.finishTabSwitch(pane);
            } else {
                loading.finally(() => this.finishTabSwitch(pane));
            }

            this.updateActiveTabInUrl(event.target);
        };
        this.onOrderUpdated = () => this.markHistoryTabStale();

        this.element.addEventListener('show.bs.tab', this.onShow);
        this.element.addEventListener('shown.bs.tab', this.onShown);
        document.addEventListener(this.orderUpdatedEventValue, this.onOrderUpdated);
    }

    disconnect() {
        this.element.removeEventListener('show.bs.tab', this.onShow);
        this.element.removeEventListener('shown.bs.tab', this.onShown);
        document.removeEventListener(this.orderUpdatedEventValue, this.onOrderUpdated);

        this.clearPendingTabSwitch();
    }

    loadTab(tabLink, force = false) {
        const pane = this.findPane(tabLink);

        return this.loadPane(pane, force);
    }

    loadPane(pane, force = false) {
        if (!pane || (!force && pane.dataset.orderDetailTabsLoadedValue === '1')) {
            return null;
        }

        pane.dataset.orderDetailTabsLoadedValue = '1';
        pane.innerHTML = `<div class="text-secondary">${Translator.trans('Loading...')}</div>`;

        return fetch(pane.dataset.orderDetailTabsContentUrlValue, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Unable to load order detail tab: ${response.status}`);
                }

                return response.text();
            })
            .then(html => {
                pane.innerHTML = html;
                new Register().registerNewContent($(pane));
            })
            .catch(() => {
                pane.dataset.orderDetailTabsLoadedValue = '0';
                pane.innerHTML = `<div class="alert alert-danger mb-0">${Translator.trans('Tab content could not be loaded.')}</div>`;
            });
    }

    prepareTabSwitch(tabLink) {
        const pane = this.findPane(tabLink);
        const activePane = this.paneTargets.find(paneTarget => paneTarget.classList.contains('active'));

        this.clearPendingTabSwitch();

        if (!pane || !activePane || pane === activePane) {
            return;
        }

        const tabContent = activePane.parentElement;
        const currentMinHeight = Number.parseFloat(tabContent.style.minHeight) || 0;

        tabContent.style.minHeight = `${Math.max(currentMinHeight, activePane.getBoundingClientRect().height)}px`;
        this.pendingTabSwitch = {
            pane,
            scrollPosition: window.scrollY,
        };
    }

    finishTabSwitch(pane) {
        if (!this.pendingTabSwitch || this.pendingTabSwitch.pane !== pane) {
            return;
        }

        const { scrollPosition } = this.pendingTabSwitch;

        this.clearPendingTabSwitch();
        window.requestAnimationFrame(() => window.scrollTo({ top: scrollPosition, behavior: 'instant' }));
    }

    clearPendingTabSwitch() {
        this.pendingTabSwitch = null;
    }

    markHistoryTabStale() {
        const historyPane = this.findPaneByTabId(this.historyTabIdValue);

        if (!historyPane) {
            return;
        }

        if (historyPane.classList.contains('active')) {
            this.loadPane(historyPane, true);

            return;
        }

        historyPane.dataset.orderDetailTabsLoadedValue = '0';
    }

    findPane(tabLink) {
        const selector = tabLink.dataset.bsTarget ?? tabLink.getAttribute('href');

        if (!selector || selector === '#') {
            return null;
        }

        return this.paneTargets.find(pane => `#${pane.id}` === selector) ?? null;
    }

    findPaneByTabId(tabId) {
        const tabLink = [...this.element.querySelectorAll('[data-tab-id]')].find(
            element => element.dataset.tabId === tabId,
        );

        return tabLink ? this.findPane(tabLink) : null;
    }

    updateActiveTabInUrl(tabLink) {
        const tabId = tabLink.dataset.tabId;

        if (!tabId) {
            return;
        }

        const url = new URL(window.location.href);
        url.searchParams.set('activeTab', tabId);
        window.history.replaceState({}, '', url);
    }
}
