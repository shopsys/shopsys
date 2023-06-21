import grapesjs from 'grapesjs';
import FormChangeInfo from 'framework/admin/components/FormChangeInfo';

const BUTTON_CLOSE = 'close';
const BUTTON_SAVE = 'save';

const resetBody = (editor) => {
    if ($('body').hasClass('grapes-js-editor-opened')) {
        $('body').removeClass('grapes-js-editor-opened');
    }
    $('#grapesjs').removeAttr('style').removeAttr('class');
    editor.destroy();
};

export default grapesjs.plugins.add('customButtons', (editor, options) => {

    const panels = editor.Panels;
    const textareaId = options.textareaId;
    const commands = editor.Commands;

    commands.add('export-inlined-html', {
        run (editor, sender, opts = {}) {
            const juice = require('juice');
            const tmpl = editor.getHtml() + `<style>${editor.getCss()}</style>`;
            return juice(tmpl, opts);
        }
    });

    panels.removeButton('options', 'fullscreen');
    panels.removeButton('options', 'export-template');
    panels.removeButton('options', 'gjs-open-import-webpage');
    panels.removeButton('options', 'canvas-clear');

    panels.addButton('options', {
        id: BUTTON_SAVE,
        context: BUTTON_SAVE,
        className: 'fa fa-save',
        command (editor) {
            const template = editor.runCommand('export-inlined-html');
            $('#' + textareaId).val(template);

            resetBody(editor);
            FormChangeInfo.showInfo();
        }
    });

    panels.addButton('options', {
        id: BUTTON_CLOSE,
        context: BUTTON_CLOSE,
        className: 'fa fa-times',
        command (editor) {
            resetBody(editor);
        }
    });
});
