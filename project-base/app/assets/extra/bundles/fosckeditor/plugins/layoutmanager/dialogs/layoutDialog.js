CKEDITOR.dialog.add('layoutDialog', function (editor) {
    genereateButtons = function () {
        return [
            {
                type: 'hbox',
                children: [
                    {
                        type: 'html',
                        html: '<div class="editor-popup-layout"><img src="' + CKEDITOR.getUrl(CKEDITOR.plugins.getPath('layoutmanager') + 'img/1-1.png') + '" alt="1/1"/></div>',
                        onClick: function () {
                            editor.insertHtml(
                                '<div class="grid">'
                                + '   <div class="grid__item grid__item--1"><p></p></div>'
                                + '</div>'
                                + '<p></p>'
                            );
                            this._.dialog.hide();
                        }
                    },
                    {
                        type: 'html',
                        html: '<div class="editor-popup-layout"><img src="' + CKEDITOR.getUrl(CKEDITOR.plugins.getPath('layoutmanager') + 'img/2-2.png') + '" alt="2/2"/></div>',
                        onClick: function () {
                            editor.insertHtml(
                                '<div class="grid">'
                                + '   <div class="grid__item grid__item--1-2"><p></p></div>'
                                + '   <div class="grid__item grid__item--1-2"><p></p></div>'
                                + '</div>'
                                + '<p></p>'
                            );
                            this._.dialog.hide();
                        }
                    },
                    {
                        type: 'html',
                        html: '<div class="editor-popup-layout"><img src="' + CKEDITOR.getUrl(CKEDITOR.plugins.getPath('layoutmanager') + 'img/1-3-2-3.png') + '" alt="2/2"/></div>',
                        onClick: function () {
                            editor.insertHtml(
                                '<div class="grid">'
                                + '<div class="grid__item grid__item--1-3"><p></p></div>'
                                + '<div class="grid__item grid__item--2-3"><p></p></div>'
                                + '</div>'
                                + '<p></p>'
                            );
                            this._.dialog.hide();
                        }
                    }
                ]

            },
            {
                type: 'hbox',
                children: [
                    {
                        type: 'html',
                        html: '<div class="editor-popup-layout"><img src="' + CKEDITOR.getUrl(CKEDITOR.plugins.getPath('layoutmanager') + 'img/2-3-1-3.png') + '" alt="2/2"/></div>',
                        onClick: function () {
                            editor.insertHtml(
                                '<div class="grid">'
                                + '<div class="grid__item grid__item--2-3"><p></p></div>'
                                + '<div class="grid__item grid__item--1-3"><p></p></div>'
                                + '</div>'
                                + '<p></p>'
                            );
                            this._.dialog.hide();
                        }
                    },
                    {
                        type: 'html',
                        html: '<div class="editor-popup-layout"><img src="' + CKEDITOR.getUrl(CKEDITOR.plugins.getPath('layoutmanager') + 'img/3-3.png') + '" alt="2/2"/></div>',
                        onClick: function () {
                            editor.insertHtml(
                                '<div class="grid">'
                                + '<div class="grid__item grid__item--1-3"><p></p></div>'
                                + '<div class="grid__item grid__item--1-3"><p></p></div>'
                                + '<div class="grid__item grid__item--1-3"><p></p></div>'
                                + '</div>'
                                + '<p></p>'
                            );
                            this._.dialog.hide();
                        }
                    },
                    {
                        type: 'html',
                        html: ''
                    }
                ]
            }
        ];
    };

    CKEDITOR.document.appendStyleSheet(CKEDITOR.getUrl(CKEDITOR.plugins.get('layoutmanager').path + 'dialogs/layoutDialog.css'));

    return {
        title: 'Layouts',
        buttons: [CKEDITOR.dialog.cancelButton],
        contents: [
            {
                elements: genereateButtons()
            }
        ]
    };
});
