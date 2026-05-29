import { Controller } from '@hotwired/stimulus';
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
    };

    connect() {
        this.dirty = false;
        this.onOpen = () => this.showModal();
        this.onClose = () => this.hideModal();
        this.onFieldChanged = event => this.markDirty(event);
        this.onHidePrevented = event => this.requestClose(event);

        if (this.hasOpenEventValue) {
            window.addEventListener(this.openEventValue, this.onOpen);
        }

        if (this.hasCloseEventValue) {
            window.addEventListener(this.closeEventValue, this.onClose);
        }

        this.element.addEventListener('input', this.onFieldChanged, true);
        this.element.addEventListener('change', this.onFieldChanged, true);
        this.element.addEventListener('hidePrevented.bs.modal', this.onHidePrevented);
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
    }
}
