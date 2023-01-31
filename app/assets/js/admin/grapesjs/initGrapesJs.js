import Register from 'framework/common/utils/Register';
import grapesjs from 'grapesjs';
import 'grapesjs-preset-webpage';
import 'grapesjs-plugin-ckeditor';
import 'grapesjs-preset-newsletter';
import './grapesjs-non-editable-page';
import './plugins/grapesjs-custom-buttons-plugin';
import './plugins/grapesjs-products-plugin';
import './plugins/grapesjs-text-with-image-plugin';
import './plugins/grapesjs-custom-blocks-plugin';
import './plugins/grapesjs-mail-template-plugin';
import 'magnific-popup';

class Grapesjs {

    static init ($container) {
        // eslint-disable-next-line no-new
        new Grapesjs($container);
    }

    constructor ($container) {
        let isAnyButtonOnPage = false;
        $container.filterAllNodes('.js-grapesjs-button').each((index, element) => {
            $(element).on('click', event => {
                const frontendUrl = $(element).data('template-url');
                const textareaId = $(element).data('textarea-id');
                const elfinderUrl = $(element).data('elfinder-url');
                this.openGrapesEditor(event, frontendUrl, textareaId, elfinderUrl);
            });

            isAnyButtonOnPage = true;
        });

        $container.filterAllNodes('.js-grapesjs-mail-button').each((index, element) => {
            $(element).on('click', event => {
                const textareaId = $(element).data('textarea-id');
                const elfinderUrl = $(element).data('elfinder-url');
                const templateHtml = $(element).data('template');
                const bodyVariables = $(element).data('variables');
                this.openGrapesMailEditor(event, textareaId, elfinderUrl, templateHtml, bodyVariables);
            });

            isAnyButtonOnPage = true;
        });

        if (isAnyButtonOnPage === true) {
            $('body').append('<div id="grapesjs"></div>');
        }
    }

    openGrapesEditor (event, frontendUrl, textareaId, elfinderUrl) {
        $('body').css({
            overflow: 'hidden',
            height: '100%'
        });

        const content = $.get({
            url: frontendUrl,
            async: false,
            crossDomain: true
        }).responseText;

        const editor = grapesjs.init({
            container: '#grapesjs',
            components: content,
            height: '100%',
            width: '100%',
            fromElement: false,
            storageManager: false,
            noticeOnUnload: false,
            plugins: ['gjs-preset-webpage', 'gjs-plugin-ckeditor', 'nonEditablePage', 'customButtons', 'products', 'text-with-image', 'custom-blocks'],
            pluginsOpts: {
                'gjs-plugin-ckeditor': {
                    options: {
                        enterMode: 2,
                        toolbar: [
                            { name: 'basicstyles', items: ['Bold', 'Italic', 'Strike', '-', 'RemoveFormat'] },
                            { name: 'format', items: ['Format'] },
                            { name: 'size', items: ['FontSize'] },
                            { name: 'links', items: ['Link', 'Unlink'] },
                            { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
                            { name: 'colors', items: ['TextColor', 'BGColor'] },
                            { name: 'document', items: ['Source'] },
                            { name: 'insert', items: ['SpecialChar'] }
                        ]
                    }
                },
                'gjs-preset-webpage': {
                    blocks: [],
                    blocksBasicOpts: {
                        blocks: ['image', 'map']
                    },
                    exportOpts: false,
                    navbarOpts: false,
                    formsOpts: false,
                    customStyleManager: [
                    ]
                },
                'customButtons': {
                    textareaId: textareaId
                }
            },
            styleManager: {
                clearProperties: true,
                appendTo: document.createElement('div'), // disable rendering a styleManager
                sectors: []
            },
            assetManager: {
                custom: {
                    open (props) {
                        $.magnificPopup.open({
                            items: { src: elfinderUrl },
                            type: 'iframe',
                            closeOnBgClick: true,
                            callbacks: {
                                close: function () {
                                    props.close();
                                }
                            }
                        });

                        window.document.fileManagerInsertImageCallback = function (selector, url) {
                            props.options.target.set('src', url);
                            $.magnificPopup.close();
                            props.close();
                        };
                    }
                }
            }
        });

        CKEDITOR.on('instanceReady', function (e) {
            editor.RichTextEditor.updatePosition();
        });
        editor.on('rte:enable', (event) => {
            editor.RichTextEditor.updatePosition();
        });

        editor.once('load', () => {
            editor.Panels.getButton('options', 'sw-visibility').set('active', 1);

            const editableContent = $('#' + textareaId).val();
            const wrapper = editor.getWrapper();
            const myComponent = wrapper.find('.gjs-editable')[0];
            myComponent.append(editableContent);
        });
    }

    openGrapesMailEditor (event, textareaId, elfinderUrl, templateHtml, bodyVariables) {
        $('body').css({
            overflow: 'hidden',
            height: '100%'
        });
        const editableContent = $('#' + textareaId).val();
        const $templateHtml = $('<div>' + templateHtml + '</div>');
        $templateHtml.find('.gjs-editable').append(editableContent);

        const variables = JSON.parse(JSON.stringify(bodyVariables));

        const editor = grapesjs.init({
            container: '#grapesjs',
            components: $templateHtml.html(),
            height: '100%',
            width: '100%',
            fromElement: false,
            storageManager: false,
            noticeOnUnload: false,
            avoidInlineStyle: false,
            forceClass: false,
            plugins: ['gjs-preset-newsletter', 'gjs-plugin-ckeditor', 'customButtons', 'mail-template'],
            pluginsOpts: {
                'gjs-preset-newsletter': {
                    styleManagerSectors: []
                },
                'gjs-plugin-ckeditor': {
                    options: {
                        enterMode: 2,
                        toolbar: [
                            { name: 'basicstyles', items: ['Bold', 'Italic', 'Strike', '-', 'RemoveFormat'] },
                            { name: 'format', items: ['Format'] },
                            { name: 'size', items: ['FontSize'] },
                            { name: 'links', items: ['Link', 'Unlink'] },
                            { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
                            { name: 'colors', items: ['TextColor', 'BGColor'] },
                            { name: 'document', items: ['Source'] },
                            { name: 'insert', items: ['SpecialChar', 'strinsert'] }
                        ],
                        extraPlugins: 'strinsert',
                        strinsert_strings: [
                            { 'name': 'Povinné proměnné' },
                            ...variables.filter((variable) => variable.isRequired === true).map((variable) => {
                                return { 'name': variable.label, 'value': variable.placeholder };
                            }),
                            { 'name': 'Volitelné proměnné' },
                            ...variables.filter((variable) => variable.isRequired === false).map((variable) => {
                                return { 'name': variable.label, 'value': variable.placeholder };
                            })
                        ]
                    }
                },
                'customButtons': {
                    textareaId: textareaId,
                    isMail: true
                }
            },
            assetManager: {
                custom: {
                    open (props) {
                        $.magnificPopup.open({
                            items: { src: elfinderUrl },
                            type: 'iframe',
                            closeOnBgClick: true,
                            callbacks: {
                                close: function () {
                                    props.close();
                                }
                            }
                        });

                        window.document.fileManagerInsertImageCallback = function (selector, url) {
                            props.options.target.set('src', url);
                            $.magnificPopup.close();
                            props.close();
                        };
                    }
                }
            }
        });

        editor.Panels.getButton('options', 'sw-visibility').set('active', 1);

        // Remove useless blocks
        editor.BlockManager.remove('sect30');
        editor.BlockManager.remove('sect37');
        editor.BlockManager.remove('button');
        editor.BlockManager.remove('divider');
        editor.BlockManager.remove('text-sect');
        editor.BlockManager.remove('quote');
        editor.BlockManager.remove('link');
        editor.BlockManager.remove('grid-items');
        editor.BlockManager.remove('list-items');
        editor.BlockManager.remove('text');

        CKEDITOR.on('instanceReady', function (e) {
            editor.RichTextEditor.updatePosition();
        });
        editor.on('rte:enable', (event) => {
            editor.RichTextEditor.updatePosition();
        });
    }
}

(new Register()).registerCallback(Grapesjs.init, 'Grapesjs.init');
