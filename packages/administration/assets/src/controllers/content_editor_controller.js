import { Controller } from '@hotwired/stimulus';
import Translator from 'bazinga-translator';
import FormChangeInfo from 'framework/admin/components/FormChangeInfo';
import ConfirmWindow from '../js/utils/confirmWindow';

const STORAGE_KEY_PREFIX = 'content-editor:';

/*
 * The root of the ContentEditorComponent (templates/content_editor/editor.html.twig):
 * hands the document over to the form of the edited entity and keeps the browser-only state of the cards.
 */
export default class extends Controller {
    static values = { targetInputId: String, document: String };

    connect() {
        this.initialDocument = this.documentValue;
        this.confirmedRemoveButton = null;
        this.onRender = () => this.sync();
        this.onClickCapture = event => this.confirmRemove(event);

        this.element.addEventListener('live:render', this.onRender);
        this.element.addEventListener('click', this.onClickCapture, true);
        this.sync();
    }

    disconnect() {
        this.element.removeEventListener('live:render', this.onRender);
        this.element.removeEventListener('click', this.onClickCapture, true);
    }

    // the whole header toggles the card, except for its toolbar buttons
    toggle(event) {
        const button = event.target.closest('button');

        if (button !== null && !button.hasAttribute('data-content-editor-toggle')) {
            return;
        }

        const card = event.target.closest('[data-content-editor-card]');

        if (card.hasAttribute('data-content-editor-has-errors')) {
            return;
        }

        card.classList.toggle('content-editor-card-collapsed');
        this.storeCollapsed(
            card.dataset.contentEditorBlockId,
            card.classList.contains('content-editor-card-collapsed'),
        );
    }

    sync() {
        this.restoreCollapsedCards();
        this.publishDocument();
    }

    publishDocument() {
        const input = document.getElementById(this.targetInputIdValue);

        if (input) {
            input.value = this.documentValue;
        }

        if (this.documentValue !== this.initialDocument) {
            FormChangeInfo.showInfo();
        }

        window.dispatchEvent(new CustomEvent('content-editor:document-changed', { detail: this.documentValue }));
    }

    // a render resets the cards to their server state, which is expanded
    restoreCollapsedCards() {
        this.element.querySelectorAll('[data-content-editor-card]').forEach(card => {
            const collapsed =
                !card.hasAttribute('data-content-editor-has-errors') &&
                this.isCollapsed(card.dataset.contentEditorBlockId);

            card.classList.toggle('content-editor-card-collapsed', collapsed);
        });
    }

    // runs before the Live Component sees the click, so the removal can be stopped until it is confirmed
    confirmRemove(event) {
        const button = event.target.closest('[data-content-editor-remove]');

        if (button === null || button === this.confirmedRemoveButton) {
            this.confirmedRemoveButton = null;

            return;
        }

        if (button.closest('[data-content-editor-card]').dataset.contentEditorEmpty === '1') {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        ConfirmWindow.show({
            content: Translator.trans('This block has content. Do you really want to remove it?'),
            continueEvent: () => {
                this.confirmedRemoveButton = button;
                button.click();
            },
        });
    }

    isCollapsed(blockId) {
        try {
            return window.localStorage.getItem(STORAGE_KEY_PREFIX + blockId) === '1';
        } catch {
            return false;
        }
    }

    storeCollapsed(blockId, collapsed) {
        try {
            if (collapsed) {
                window.localStorage.setItem(STORAGE_KEY_PREFIX + blockId, '1');
            } else {
                window.localStorage.removeItem(STORAGE_KEY_PREFIX + blockId);
            }
        } catch {
            // the state is only a convenience, it is not remembered without storage
        }
    }
}
