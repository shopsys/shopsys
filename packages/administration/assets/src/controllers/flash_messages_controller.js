import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';
import { Toast } from '@tabler/core';

const flashMessagesHeader = 'X-Admin-Flash-Messages';
const processedLiveResponses = new WeakSet();

export default class extends Controller {
    static targets = ['stack'];

    static values = {
        live: Boolean,
    };

    connect() {
        this.isConnectedToFlashMessages = true;
        this.liveComponentHooks = new Map();
        this.initializeToasts(this.element);

        if (this.liveValue) {
            this.registerLiveComponent = event => this.registerLiveComponentHooks(event.detail.component);
            document.addEventListener('live:connect', this.registerLiveComponent);
            this.registerExistingLiveComponents();
        }
    }

    disconnect() {
        this.isConnectedToFlashMessages = false;

        if (this.liveValue) {
            document.removeEventListener('live:connect', this.registerLiveComponent);
            this.unregisterLiveComponentHooks();
        }
    }

    addToastsFromHtml(html) {
        if (!html) {
            return;
        }

        const template = document.createElement('template');
        template.innerHTML = html.trim();

        template.content.querySelectorAll('[data-bs-toggle="toast"]').forEach(toastElement => {
            const toastElementClone = toastElement.cloneNode(true);

            this.getToastContainer().appendChild(toastElementClone);
            this.showToast(toastElementClone, true);
        });
    }

    initializeToasts(element) {
        element.querySelectorAll('[data-bs-toggle="toast"]').forEach(toastElement => {
            this.showToast(toastElement, this.liveValue);
        });
    }

    getToastContainer() {
        return this.hasStackTarget ? this.stackTarget : this.element;
    }

    async registerExistingLiveComponents() {
        for (const liveElement of document.querySelectorAll('[data-controller~="live"]')) {
            const component = await getComponent(liveElement);

            if (!this.isConnectedToFlashMessages) {
                return;
            }

            this.registerLiveComponentHooks(component);
        }
    }

    registerLiveComponentHooks(component) {
        const componentElement = component.element;

        if (this.liveComponentHooks.has(componentElement)) {
            return;
        }

        const renderStartedCallback = (_html, backendResponse) => {
            if (processedLiveResponses.has(backendResponse.response)) {
                return;
            }

            processedLiveResponses.add(backendResponse.response);

            const encodedToastHtml = backendResponse.response.headers.get(flashMessagesHeader);

            if (!encodedToastHtml) {
                return;
            }

            this.addToastsFromHtml(this.decodeHeaderValue(encodedToastHtml));
        };

        this.liveComponentHooks.set(componentElement, { component, renderStartedCallback });
        component.on('render:started', renderStartedCallback);
    }

    unregisterLiveComponentHooks() {
        this.liveComponentHooks.forEach(({ component, renderStartedCallback }) => {
            component.off('render:started', renderStartedCallback);
        });
        this.liveComponentHooks.clear();
    }

    decodeHeaderValue(value) {
        const binaryString = window.atob(value);
        const bytes = Uint8Array.from(binaryString, character => character.charCodeAt(0));

        return new TextDecoder().decode(bytes);
    }

    showToast(toastElement, removeAfterHidden = false) {
        if (toastElement.dataset.toastInitialized === '1') {
            return;
        }

        toastElement.dataset.toastInitialized = '1';

        if (removeAfterHidden) {
            toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove(), { once: true });
        }

        new Toast(toastElement).show();
    }
}
