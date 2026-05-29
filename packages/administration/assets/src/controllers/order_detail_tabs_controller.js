import { Controller } from '@hotwired/stimulus';
import Translator from 'bazinga-translator';
import Register from 'framework/common/utils/Register';

export default class extends Controller {
    static targets = ['pane'];

    connect() {
        this.onShown = event => {
            this.loadTab(event.target);
            this.updateActiveTabInUrl(event.target);
        };
        this.element.addEventListener('shown.bs.tab', this.onShown);
    }

    disconnect() {
        this.element.removeEventListener('shown.bs.tab', this.onShown);
    }

    loadTab(tabLink) {
        const pane = this.findPane(tabLink);

        if (!pane || pane.dataset.orderDetailTabsLoadedValue === '1') {
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

    findPane(tabLink) {
        const selector = tabLink.dataset.bsTarget ?? tabLink.getAttribute('href');

        if (!selector || selector === '#') {
            return null;
        }

        return this.paneTargets.find(pane => `#${pane.id}` === selector) ?? null;
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
