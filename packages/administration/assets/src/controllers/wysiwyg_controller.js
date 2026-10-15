import { Controller } from '@hotwired/stimulus';
import { Dropdown } from '@tabler/core';
import { Editor } from '@tiptap/core';
import { createExtensions } from '../js/wysiwyg/extensions';
import { buildLinkAttributes, readLinkForm } from '../js/wysiwyg/linkAttributes';
import { TOOLBAR_COMMANDS } from '../js/wysiwyg/toolbarCommands';
import { insertVariable } from '../js/wysiwyg/variables';

/*
 * The WysiwygType widget: a Tiptap editor that keeps the HTML in the form textarea.
 * Used by form/type/WysiwygType.html.twig.
 */
export default class extends Controller {
    static targets = [
        'editor',
        'textarea',
        'source',
        'sourceToggle',
        'button',
        'linkDropdown',
        'linkHref',
        'linkTitle',
        'linkNewTab',
    ];

    connect() {
        this.editor = new Editor({
            element: this.editorTarget,
            extensions: createExtensions(),
            content: this.textareaTarget.value,
            onUpdate: () => this.syncTextarea(),
            // the editor is not yet assigned to this.editor when the first transaction runs
            onTransaction: ({ editor }) => this.markActiveButtons(editor),
        });
    }

    disconnect() {
        this.editor.destroy();
    }

    command({ params }) {
        TOOLBAR_COMMANDS[params.command].run(this.editor);
    }

    prepareLink() {
        const form = readLinkForm(this.editor.getAttributes('link'));

        this.linkHrefTarget.value = form.href;
        this.linkTitleTarget.value = form.title;
        this.linkNewTabTarget.checked = form.openInNewTab;
    }

    applyLink(event) {
        event.preventDefault();

        const href = this.linkHrefTarget.value.trim();

        if (href === '') {
            this.removeLink();

            return;
        }

        const attributes = buildLinkAttributes({
            href,
            title: this.linkTitleTarget.value.trim(),
            openInNewTab: this.linkNewTabTarget.checked,
        });

        this.editor.chain().focus().extendMarkRange('link').setLink(attributes).run();
        this.closeLinkPanel();
    }

    removeLink() {
        this.editor.chain().focus().extendMarkRange('link').unsetLink().run();
        this.closeLinkPanel();
    }

    insertVariable({ params }) {
        insertVariable(this.editor, params.placeholder);
    }

    toggleSource() {
        if (this.sourceTarget.classList.contains('d-none')) {
            this.showSource();
        } else {
            this.hideSource();
        }
    }

    // the source textarea is the only one the user edits while the source is shown
    onSourceInput() {
        this.writeTextarea(this.sourceTarget.value);
    }

    // the source and the editor are given each other's height, so the form below does not jump when switching
    showSource() {
        this.sourceTarget.style.height = `${this.editorTarget.offsetHeight}px`;
        this.sourceTarget.value = this.editor.getHTML();
        this.sourceTarget.classList.remove('d-none');
        this.editorTarget.classList.add('d-none');
        this.sourceToggleTarget.classList.add('active');
        this.setButtonsDisabled(true);
    }

    hideSource() {
        this.editorTarget.style.minHeight = `${this.sourceTarget.offsetHeight}px`;
        this.editor.commands.setContent(this.sourceTarget.value);
        this.syncTextarea();
        this.sourceTarget.classList.add('d-none');
        this.editorTarget.classList.remove('d-none');
        this.sourceToggleTarget.classList.remove('active');
        this.setButtonsDisabled(false);
    }

    syncTextarea() {
        this.writeTextarea(this.editor.getHTML());
    }

    // input and change, so Live Components (data-model) and FormChangeInfo notice the change
    writeTextarea(html) {
        this.textareaTarget.value = html;
        this.textareaTarget.dispatchEvent(new Event('input', { bubbles: true }));
        this.textareaTarget.dispatchEvent(new Event('change', { bubbles: true }));
    }

    markActiveButtons(editor) {
        for (const button of this.buttonTargets) {
            const command = TOOLBAR_COMMANDS[button.dataset.wysiwygCommandParam];

            if (command) {
                button.classList.toggle('active', command.isActive?.(editor) ?? false);
            }
        }
    }

    setButtonsDisabled(disabled) {
        for (const button of this.buttonTargets) {
            button.disabled = disabled;
        }
    }

    closeLinkPanel() {
        const toggle = this.linkDropdownTarget.querySelector('[data-bs-toggle="dropdown"]');

        Dropdown.getOrCreateInstance(toggle).hide();
    }
}
