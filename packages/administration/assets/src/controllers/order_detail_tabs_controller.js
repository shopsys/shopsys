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
        this.onShown = event => {
            this.loadTab(event.target);
            this.updateActiveTabInUrl(event.target);
        };
        this.onOrderUpdated = () => this.markHistoryTabStale();

        this.element.addEventListener('shown.bs.tab', this.onShown);
        document.addEventListener(this.orderUpdatedEventValue, this.onOrderUpdated);
    }

    disconnect() {
        this.element.removeEventListener('shown.bs.tab', this.onShown);
        document.removeEventListener(this.orderUpdatedEventValue, this.onOrderUpdated);
    }

    loadTab(tabLink, force = false) {
        const pane = this.findPane(tabLink);

        this.loadPane(pane, force);
    }

    loadPane(pane, force = false) {
        if (!pane || (!force && pane.dataset.orderDetailTabsLoadedValue === '1')) {
            return;
        }

        pane.dataset.orderDetailTabsLoadedValue = '1';
        pane.innerHTML = `<div class="text-secondary">${Translator.trans('Loading...')}</div>`;

        fetch(pane.dataset.orderDetailTabsContentUrlValue, {
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
