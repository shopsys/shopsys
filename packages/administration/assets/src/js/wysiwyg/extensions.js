import { TableKit } from '@tiptap/extension-table';
import StarterKit from '@tiptap/starter-kit';

export const HEADING_LEVELS = [2, 3, 4];

/*
 * Only what the toolbar offers (and what the server-side sanitizer allows) is enabled,
 * markup without an extension is dropped by Tiptap when the content is loaded.
 */
export function createExtensions() {
    return [
        StarterKit.configure({
            heading: { levels: HEADING_LEVELS },
            code: false,
            codeBlock: false,
            trailingNode: false,
            link: {
                openOnClick: false,
                // by default Tiptap adds target and rel to every link, here they come only from the link panel
                HTMLAttributes: { target: null, rel: null, class: null },
            },
        }),
        TableKit.configure({ table: { resizable: false } }),
    ];
}
