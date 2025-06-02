CKEDITOR.plugins.add('defaultlinkvalues', {
    init: function (editor) {
        editor.on('instanceReady', function () {
            CKEDITOR.on('dialogDefinition', function(ev) {
                if (ev.data.name === 'link') {
                    const advTab = ev.data.definition.getContents('advanced');
                    if (advTab) {
                        const tabIndexField = advTab.get('advTabIndex');
                        if (tabIndexField) {
                            const originalSetup = tabIndexField.setup || function () {};
                            tabIndexField.setup = function (widget) {
                                originalSetup.call(this, widget);
                                this.setValue('0');
                            };
                        }
                    }
                }
            });
        });
    }
});
