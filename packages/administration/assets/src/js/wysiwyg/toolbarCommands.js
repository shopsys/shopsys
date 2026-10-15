import { HEADING_LEVELS } from './extensions';

function headingCommand(level) {
    return {
        run: editor => editor.chain().focus().toggleHeading({ level }).run(),
        isActive: editor => editor.isActive('heading', { level }),
    };
}

function toggleCommand(name, toggleName) {
    return {
        run: editor => editor.chain().focus()[toggleName]().run(),
        isActive: editor => editor.isActive(name),
    };
}

const headingCommands = Object.fromEntries(HEADING_LEVELS.map(level => [`h${level}`, headingCommand(level)]));

export const TOOLBAR_COMMANDS = {
    bold: toggleCommand('bold', 'toggleBold'),
    italic: toggleCommand('italic', 'toggleItalic'),
    underline: toggleCommand('underline', 'toggleUnderline'),
    strike: toggleCommand('strike', 'toggleStrike'),
    ...headingCommands,
    bulletList: toggleCommand('bulletList', 'toggleBulletList'),
    orderedList: toggleCommand('orderedList', 'toggleOrderedList'),
    blockquote: toggleCommand('blockquote', 'toggleBlockquote'),
    horizontalRule: {
        run: editor => editor.chain().focus().setHorizontalRule().run(),
    },
    insertTable: {
        run: editor => editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run(),
    },
    addRow: {
        run: editor => editor.chain().focus().addRowAfter().run(),
    },
    deleteRow: {
        run: editor => editor.chain().focus().deleteRow().run(),
    },
    addColumn: {
        run: editor => editor.chain().focus().addColumnAfter().run(),
    },
    deleteColumn: {
        run: editor => editor.chain().focus().deleteColumn().run(),
    },
    deleteTable: {
        run: editor => editor.chain().focus().deleteTable().run(),
    },
    clearFormatting: {
        run: editor => editor.chain().focus().unsetAllMarks().clearNodes().run(),
    },
};
