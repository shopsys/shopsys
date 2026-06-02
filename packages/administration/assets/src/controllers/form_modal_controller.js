import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';
import { Modal } from '@tabler/core';
import Translator from 'bazinga-translator';
import Register from 'framework/common/utils/Register';
import ConfirmWindow from '../js/utils/confirmWindow';

export default class extends Controller {
    static targets = ['closeTrigger'];

    static values = {
        closeEvent: String,
        open: Boolean,
        openEvent: String,
        pendingActions: { type: Array, default: [] },
    };

    connect() {
        this.dirty = false;
        this.pendingRequests = 0;
        this.liveComponentHooks = new Map();
        this.onOpen = () => this.showModal();
        this.onClose = () => this.hideModal();
        this.onFieldChanged = event => this.markDirty(event);
        this.onHidePrevented = event => this.requestClose(event);
        this.onLiveConnect = event => this.registerLiveComponentElement(event.target);

        if (this.hasOpenEventValue) {
            window.addEventListener(this.openEventValue, this.onOpen);
        }

        if (this.hasCloseEventValue) {
            window.addEventListener(this.closeEventValue, this.onClose);
        }

        this.element.addEventListener('input', this.onFieldChanged, true);
        this.element.addEventListener('change', this.onFieldChanged, true);
        this.element.addEventListener('hidePrevented.bs.modal', this.onHidePrevented);

        if (this.shouldTrackPendingRequests()) {
            this.element.addEventListener('live:connect', this.onLiveConnect);
            this.registerExistingLiveComponents();
        }

        this.syncModal();
    }

    disconnect() {
        if (this.hasOpenEventValue) {
            window.removeEventListener(this.openEventValue, this.onOpen);
        }

        if (this.hasCloseEventValue) {
            window.removeEventListener(this.closeEventValue, this.onClose);
        }

        this.element.removeEventListener('input', this.onFieldChanged, true);
        this.element.removeEventListener('change', this.onFieldChanged, true);
        this.element.removeEventListener('hidePrevented.bs.modal', this.onHidePrevented);

        if (this.shouldTrackPendingRequests()) {
            this.element.removeEventListener('live:connect', this.onLiveConnect);
        }

        this.unregisterLiveComponentHooks();
    }

    openValueChanged() {
        this.syncModal();
    }

    syncModal() {
        if (this.openValue) {
            this.showModal();

            return;
        }

        this.hideModal();
    }

    showModal() {
        window.requestAnimationFrame(() => {
            this.dirty = false;
            this.registerModalContent();
            this.getModal().show();
        });
    }

    hideModal() {
        window.requestAnimationFrame(() => {
            this.dirty = false;
            this.getModal().hide();
        });
    }

    markDirty(event) {
        if (event.target.closest('form') === null) {
            return;
        }

        this.dirty = true;
    }

    requestClose(event) {
        event.preventDefault();

        if (this.isPending()) {
            return;
        }

        if (!this.dirty) {
            this.closeByTrigger();

            return;
        }

        ConfirmWindow.show({
            style: 'warning',
            content: Translator.trans(
                'You have unsaved changes. Do you really want to close the editor without saving?',
            ),
            continueEvent: () => this.closeByTrigger(),
        });
    }

    closeByTrigger() {
        this.dirty = false;

        if (this.hasCloseTriggerTarget) {
            this.closeTriggerTarget.click();
            return;
        }

        this.hideModal();
    }

    getModal() {
        return Modal.getOrCreateInstance(this.element, {
            backdrop: 'static',
            keyboard: false,
        });
    }

    registerModalContent() {
        const content = this.element.querySelector('.modal-content');

        if (!content) {
            return;
        }

        const contentSignature = content.innerHTML;

        if (this.registeredContentSignature === contentSignature) {
            return;
        }

        new Register().registerNewContent($(content));
        this.registeredContentSignature = content.innerHTML;

        if (this.shouldTrackPendingRequests()) {
            this.registerExistingLiveComponents();
        }
    }

    registerExistingLiveComponents() {
        const parentLiveComponentElement = this.element.closest('[data-controller~="live"]');

        if (parentLiveComponentElement) {
            this.registerLiveComponentElement(parentLiveComponentElement);
        }

        this.element.querySelectorAll('[data-controller~="live"]').forEach(element => {
            this.registerLiveComponentElement(element);
        });
    }

    async registerLiveComponentElement(element) {
        if (!this.isRelatedLiveComponentElement(element) || this.liveComponentHooks.has(element)) {
            return;
        }

        const component = await getComponent(element);

        if (!this.isRelatedLiveComponentElement(element) || this.liveComponentHooks.has(element)) {
            return;
        }

        const loadingStartedCallback = (_targetElement, request) => this.startPendingRequest(request);
        const loadingFinishedCallback = () => this.finishPendingRequest();
        const responseErrorCallback = () => this.finishPendingRequests();

        component.on('loading.state:started', loadingStartedCallback);
        component.on('loading.state:finished', loadingFinishedCallback);
        component.on('response:error', responseErrorCallback);

        this.liveComponentHooks.set(element, {
            component,
            loadingStartedCallback,
            loadingFinishedCallback,
            responseErrorCallback,
        });
    }

    unregisterLiveComponentHooks() {
        this.liveComponentHooks.forEach(
            ({ component, loadingStartedCallback, loadingFinishedCallback, responseErrorCallback }) => {
                component.off('loading.state:started', loadingStartedCallback);
                component.off('loading.state:finished', loadingFinishedCallback);
                component.off('response:error', responseErrorCallback);
            },
        );
        this.liveComponentHooks.clear();
    }

    isRelatedLiveComponentElement(element) {
        return this.element.contains(element) || element.contains(this.element);
    }

    shouldTrackPendingRequests() {
        return this.pendingActionsValue.length > 0;
    }

    startPendingRequest(request) {
        if (!request?.containsOneOfActions(this.pendingActionsValue)) {
            return;
        }

        this.pendingRequests += 1;
        this.dirty = false;
        this.updatePendingState();
    }

    finishPendingRequest() {
        if (this.pendingRequests === 0) {
            return;
        }

        this.pendingRequests -= 1;
        this.updatePendingState();
    }

    finishPendingRequests() {
        this.pendingRequests = 0;
        this.updatePendingState();
    }

    isPending() {
        return this.pendingRequests > 0;
    }

    updatePendingState() {
        const pending = this.isPending();

        this.element.classList.toggle('is-pending', pending);
        this.element.toggleAttribute('data-form-modal-pending', pending);
        this.element.querySelectorAll('[data-form-modal-disable-on-pending]').forEach(element => {
            if (pending) {
                if (!('formModalOriginallyDisabled' in element.dataset)) {
                    element.dataset.formModalOriginallyDisabled = element.disabled ? '1' : '0';
                }

                element.disabled = true;

                return;
            }

            if (element.dataset.formModalOriginallyDisabled !== '1') {
                element.disabled = false;
            }

            delete element.dataset.formModalOriginallyDisabled;
        });
        this.element.querySelectorAll('[data-form-modal-hide-on-pending]').forEach(element => {
            element.classList.toggle('d-none', pending);
        });
        this.element.querySelectorAll('[data-form-modal-show-on-pending]').forEach(element => {
            element.classList.toggle('d-none', !pending);
        });
    }
}
