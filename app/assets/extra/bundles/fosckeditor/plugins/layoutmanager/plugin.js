CKEDITOR.plugins.add('layoutmanager', {
    icons: 'addLayout',
    init: function (editor) {
        if (typeof editor.config.contentsCss == 'object') {
            editor.config.contentsCss.push(CKEDITOR.getUrl(this.path + 'css/style.css'));
        } else {
            editor.config.contentsCss = [CKEDITOR.getUrl(this.path + 'css/style.css')];
        }

        // eslint-disable-next-line new-cap
        editor.addCommand('addLayout', new CKEDITOR.dialogCommand('layoutDialog'));
        editor.ui.addButton('AddLayout', {
            label: 'Insert layout',
            command: 'addLayout'
        });

        CKEDITOR.dialog.add('layoutDialog', this.path + 'dialogs/layoutDialog.js');
    }
});
