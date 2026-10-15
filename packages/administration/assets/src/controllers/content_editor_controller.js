import { Controller } from '@hotwired/stimulus';
import Translator from 'bazinga-translator';
import ConfirmWindow from '../js/utils/confirmWindow';

const STORAGE_KEY_PREFIX = 'content-editor:';

/*
 * The root of the ContentEditorComponent (templates/content_editor/editor.html.twig):
 * announces the document to the preview and keeps the browser-only state of the collapsible cards and columns.
 * The document reaches the form of the edited entity only when the editor is done, see content_editor_overlay_controller.js.
 */
export default class extends Controller {
    static values = { document: String };

    connect() {
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

    // the whole header toggles the card or the column, except for its toolbar buttons
    toggle(event) {
        const button = event.target.closest('button');

        if (button !== null && !button.hasAttribute('data-content-editor-toggle')) {
            return;
        }

        const collapsible = event.target.closest('[data-content-editor-collapsible]');

        if (collapsible.hasAttribute('data-content-editor-has-errors')) {
            return;
        }

        collapsible.classList.toggle('content-editor-collapsed');
        this.storeCollapsed(
            collapsible.dataset.contentEditorKey,
            collapsible.classList.contains('content-editor-collapsed'),
        );
    }

    sync() {
        this.restoreCollapsed();
        window.dispatchEvent(new CustomEvent('content-editor:document-changed', { detail: this.documentValue }));
    }

    // a render resets the cards and columns to their server state, which is expanded
    restoreCollapsed() {
        this.element.querySelectorAll('[data-content-editor-collapsible]').forEach(collapsible => {
            const collapsed =
                !collapsible.hasAttribute('data-content-editor-has-errors') &&
                this.isCollapsed(collapsible.dataset.contentEditorKey);

            collapsible.classList.toggle('content-editor-collapsed', collapsed);
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

    isCollapsed(key) {
        try {
            return window.localStorage.getItem(STORAGE_KEY_PREFIX + key) === '1';
        } catch {
            return false;
        }
    }

    storeCollapsed(key, collapsed) {
        try {
            if (collapsed) {
                window.localStorage.setItem(STORAGE_KEY_PREFIX + key, '1');
            } else {
                window.localStorage.removeItem(STORAGE_KEY_PREFIX + key);
            }
        } catch {
            // the state is only a convenience, it is not remembered without storage
        }
    }
}
