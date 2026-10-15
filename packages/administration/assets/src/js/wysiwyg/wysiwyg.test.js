import { Editor } from '@tiptap/core';
import { createExtensions } from './extensions';
import { buildLinkAttributes, readLinkForm } from './linkAttributes';
import { TOOLBAR_COMMANDS } from './toolbarCommands';
import { insertVariable } from './variables';

function createEditor(html) {
    return new Editor({ element: document.createElement('div'), extensions: createExtensions(), content: html });
}

describe('HTML round trip', () => {
    test.each([
        '<p>text <strong>bold</strong> <em>italic</em> <u>underline</u> <s>strike</s></p>',
        '<h2>a</h2><h3>b</h3><h4>c</h4>',
        '<ul><li><p>one</p></li></ul><ol><li><p>two</p></li></ol>',
        '<blockquote><p>quote</p></blockquote><hr>',
        '<p><a href="https://example.com" title="t">link</a></p>',
        '<p>{order_number}</p>',
    ])('supported markup stays the same: %s', html => {
        expect(createEditor(html).getHTML()).toBe(html);
    });

    test('table keeps rows, header cells and cell content', () => {
        const html = createEditor(
            '<table><tbody><tr><th>head</th></tr><tr><td>cell</td></tr></tbody></table>',
        ).getHTML();

        expect(html).toContain('<tr><th><p>head</p></th></tr><tr><td><p>cell</p></td></tr>');
    });

    test('markup without an extension is dropped', () => {
        const editor = createEditor(
            '<p>a</p><script>alert(1)</script><img src="x.png"><h1>b</h1><pre><code>c</code></pre>',
        );

        expect(editor.getHTML()).not.toMatch(/script|img|h1|<pre|<code/);
    });
});

describe('links', () => {
    test('links get neither target nor rel by default', () => {
        const editor = createEditor('<p><a href="https://example.com">link</a></p>');

        expect(editor.getHTML()).toBe('<p><a href="https://example.com">link</a></p>');
    });

    test('a link from the panel opened in a new tab carries target and rel', () => {
        const editor = createEditor('<p>link</p>');
        const attributes = buildLinkAttributes({ href: 'https://example.com', title: '', openInNewTab: true });

        editor.chain().selectAll().setLink(attributes).run();

        expect(editor.getHTML()).toBe('<p><a target="_blank" rel="noopener" href="https://example.com">link</a></p>');
    });

    test('link attributes without a new tab have no target, rel and empty title', () => {
        expect(buildLinkAttributes({ href: '/a', title: '', openInNewTab: false })).toEqual({
            href: '/a',
            title: null,
            target: null,
            rel: null,
        });
    });

    test('the link form is read from the link attributes', () => {
        expect(readLinkForm({ href: '/a', title: 't', target: '_blank' })).toEqual({
            href: '/a',
            title: 't',
            openInNewTab: true,
        });
        expect(readLinkForm({})).toEqual({ href: '', title: '', openInNewTab: false });
    });
});

describe('variables', () => {
    test('a placeholder is inserted as text', () => {
        const editor = createEditor('<p></p>');

        insertVariable(editor, '{order_number}');

        expect(editor.getHTML()).toBe('<p>{order_number}</p>');
    });
});

describe('toolbar commands', () => {
    test('table is inserted with a header row and edited by rows and columns', () => {
        const editor = createEditor('<p>text</p>');
        const count = selector => editor.view.dom.querySelectorAll(selector).length;

        TOOLBAR_COMMANDS.insertTable.run(editor);
        expect([count('table'), count('tr'), count('th'), count('td')]).toEqual([1, 3, 3, 6]);

        TOOLBAR_COMMANDS.addRow.run(editor);
        TOOLBAR_COMMANDS.addColumn.run(editor);
        expect([count('tr'), count('th, td')]).toEqual([4, 16]);

        TOOLBAR_COMMANDS.deleteRow.run(editor);
        TOOLBAR_COMMANDS.deleteColumn.run(editor);
        expect([count('tr'), count('th, td')]).toEqual([3, 9]);

        TOOLBAR_COMMANDS.deleteTable.run(editor);
        expect(count('table')).toBe(0);
    });

    test('bold toggles and reports its active state', () => {
        const editor = createEditor('<p>text</p>');

        editor.commands.selectAll();
        TOOLBAR_COMMANDS.bold.run(editor);

        expect(editor.getHTML()).toBe('<p><strong>text</strong></p>');
        expect(TOOLBAR_COMMANDS.bold.isActive(editor)).toBe(true);
    });

    test('clear formatting removes marks and headings', () => {
        const editor = createEditor('<h2><strong>text</strong></h2>');

        editor.commands.selectAll();
        TOOLBAR_COMMANDS.clearFormatting.run(editor);

        expect(editor.getHTML()).toBe('<p>text</p>');
    });
});
