export function insertVariable(editor, placeholder) {
    // a text node, so the placeholder is not parsed as HTML
    editor.chain().focus().insertContent({ type: 'text', text: placeholder }).run();
}
