import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';
import Translator from 'bazinga-translator';
import throttle from '../js/contentEditor/throttle';
import ConfirmWindow from '../js/utils/confirmWindow';

const STORAGE_KEY_PREFIX = 'content-editor:';
const PREVIEW_THROTTLE_MS = 300;

/*
 * The root of the ContentEditorComponent (templates/content_editor/editor.html.twig):
 * sends the exported document to the preview and keeps the browser-only state of the collapsible cards and columns.
 * The document reaches the form of the edited entity only when the editor is done, see content_editor_overlay_controller.js.
 */
export default class extends Controller {
    static values = { exportedDocument: String };
    static targets = ['previewFrame', 'previewFrameWrapper'];

    connect() {
        this.confirmedRemoveButton = null;
        this.postDocumentToPreview = throttle(() => this.postDocument(), PREVIEW_THROTTLE_MS);
        this.onRender = () => this.sync();
        this.onClickCapture = event => this.confirmRemove(event);
        // the preview page tells that it is listening, it has nothing to show until it gets the document
        this.onMessage = event => {
            if (
                this.hasPreviewFrameTarget &&
                event.source === this.previewFrameTarget.contentWindow &&
                event.data?.type === 'content-editor:ready'
            ) {
                this.postDocument();
            }
        };

        this.element.addEventListener('click', this.onClickCapture, true);
        window.addEventListener('message', this.onMessage);
        this.sync();
        this.listenToRenders();
    }

    // the Live Component has no DOM event for a render, only the hook of its component
    async listenToRenders() {
        const component = await getComponent(this.element);

        // the editor can be disconnected while the component is being resolved
        if (!this.element.isConnected) {
            return;
        }

        this.liveComponent = component;
        component.on('render:finished', this.onRender);
    }

    disconnect() {
        this.postDocumentToPreview.cancel();
        this.liveComponent?.off('render:finished', this.onRender);
        this.element.removeEventListener('click', this.onClickCapture, true);
        window.removeEventListener('message', this.onMessage);
    }

    setPreviewWidth({ currentTarget, params: { width } }) {
        this.previewFrameWrapperTarget.style.maxWidth = width;
        currentTarget.parentElement.querySelectorAll('button').forEach(button => {
            button.classList.toggle('active', button === currentTarget);
        });
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
        window.dispatchEvent(
            new CustomEvent('content-editor:document-changed', { detail: this.exportedDocumentValue }),
        );
        this.postDocumentToPreview();
    }

    postDocument() {
        if (!this.hasPreviewFrameTarget) {
            return;
        }

        // the preview is served from the same origin as the administration
        this.previewFrameTarget.contentWindow?.postMessage(
            { type: 'content-editor:document', document: JSON.parse(this.exportedDocumentValue) },
            window.location.origin,
        );
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
