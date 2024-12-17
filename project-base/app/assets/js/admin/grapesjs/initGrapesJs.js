import Register from 'framework/common/utils/Register';
import grapesjs from 'grapesjs';
import webPagePlugin from 'grapesjs-preset-webpage';
import ckeditorPlugin from 'grapesjs-plugin-ckeditor';
import newsletterPlugin from 'grapesjs-preset-newsletter';
import './grapesjs-non-editable-page';
import './plugins/grapesjs-custom-buttons-plugin';
import './plugins/grapesjs-products-plugin';
import './plugins/grapesjs-text-with-image-plugin';
import './plugins/grapesjs-custom-blocks-plugin';
import './plugins/grapesjs-mail-template-plugin';
import './plugins/grapesjs-custom-image-plugin';
import './plugins/grapesjs-custom-link-plugin';
import './plugins/grapesjs-custom-image-file-plugin';
import './plugins/grapesjs-custom-iframe-plugin';
import './plugins/grapesjs-table-custom-plugin';
import 'magnific-popup';
import { en } from './locales/en';
import Translator from 'bazinga-translator';

import { Buffer } from 'buffer';
global.Buffer = Buffer;

export default class InitGrapesJs {

    static init ($container) {
        let isAnyButtonOnPage = false;
        $container.filterAllNodes('.js-grapesjs-button').each((index, element) => {
            $(element).on('click', (event) => {
                const frontendUrl = $(element).data('template-url');
                const textareaId = $(element).data('textarea-id');
                const elfinderUrl = $(element).data('elfinder-url');
                const allowProducts = $(element).data('allow-products');
                InitGrapesJs.openGrapesEditor(event, frontendUrl, textareaId, elfinderUrl, allowProducts);
            });

            isAnyButtonOnPage = true;
        });

        $container.filterAllNodes('.js-grapesjs-mail-button').each((index, element) => {
            $(element).on('click', (event) => {
                const textareaId = $(element).data('textarea-id');
                const elfinderUrl = $(element).data('elfinder-url');
                const templateHtml = $(element).data('template');
                const bodyVariables = $(element).data('variables');
                InitGrapesJs.openGrapesMailEditor(event, textareaId, elfinderUrl, templateHtml, bodyVariables);
            });

            isAnyButtonOnPage = true;
        });

        if (isAnyButtonOnPage === true) {
            $('body').append('<div id="grapesjs"></div>');
        }
    }

    static openGrapesEditor (event, frontendUrl, textareaId, elfinderUrl, allowProducts) {
        InitGrapesJs.setupBodyForGrapesJsEditor();

        const content = $.get({
            url: frontendUrl,
            async: false,
            crossDomain: true
        }).responseText;

        const plugins = [
            webPagePlugin,
            ckeditorPlugin,
            'nonEditablePage',
            'customButtons',
            'text-with-image',
            'custom-blocks',
            'table-custom',
            'custom-image',
            'custom-link',
            'custom-image-file',
            'custom-iframe'
        ];

        if (allowProducts) {
            plugins.push('products');
        }

        const editor = grapesjs.init({
            container: '#grapesjs',
            components: content,
            height: '100%',
            width: '100%',
            fromElement: false,
            storageManager: false,
            noticeOnUnload: false,
            avoidInlineStyle: false,
            forceClass: false,
            nativeDnD: true,
            plugins: plugins,
            pluginsOpts: {
                [ckeditorPlugin]: {
                    ckeditor: '',
                    options: {
                        enterMode: 2,
                        versionCheck: false,
                        allowedContent: true,
                        extraAllowedContent: '*(*)',
                        removePlugins: 'exportpdf',
                        toolbar: [
                            { name: 'basicstyles', items: ['Bold', 'Italic', 'Strike', '-', 'RemoveFormat'] },
                            { name: 'clipboard', items: ['PasteText', 'PasteFromWord'] },
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
                [webPagePlugin]: {
                    blocks: [],
                    block: () => {
                        return {
                            label: Translator.trans('Link'),
                            category: Translator.trans('Basic objects'),
                            attributes: { class: 'fa fa-link' }
                        };
                    },
                    useCustomTheme: false
                },
                customButtons: {
                    textareaId: textareaId
                }
            },
            styleManager: {
                clearProperties: true,
                appendTo: document.querySelector('#panels')
            },
            selectorManager: {
                componentFirst: true
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

        editor.TraitManager.addType('textarea', {
            createInput () {
                return document.createElement('textarea');
            },
            onUpdate ({ elInput, _, trait }) {
                elInput.value = trait.changed.value;
            }
        });

        editor.I18n.setMessages({
            en
        });

        editor.once('load', () => {
            editor.Panels.getButton('options', 'sw-visibility').set('active', 1);

            const editableContent = $('#' + textareaId).val();
            editor.getWrapper().find('.gjs-editable')[0].append(editableContent);
        });
    }

    static openGrapesMailEditor (event, textareaId, elfinderUrl, templateHtml, bodyVariables) {
        InitGrapesJs.setupBodyForGrapesJsEditor();
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
            plugins: [newsletterPlugin, ckeditorPlugin, 'customButtons', 'mail-template'],
            pluginsOpts: {
                [newsletterPlugin]: {
                    styleManagerSectors: []
                },
                [ckeditorPlugin]: {
                    ckeditor: '',
                    options: {
                        enterMode: 2,
                        versionCheck: false,
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
                        removePlugins: 'exportpdf',
                        strinsert_strings: [
                            { 'name': 'Povinné proměnné' },
                            ...variables
                                .filter((variable) => variable.isRequired === true)
                                .map((variable) => {
                                    return { 'name': variable.label, 'value': variable.placeholder };
                                }),
                            { 'name': 'Volitelné proměnné' },
                            ...variables
                                .filter((variable) => variable.isRequired === false)
                                .map((variable) => {
                                    return { 'name': variable.label, 'value': variable.placeholder };
                                })
                        ]
                    }
                },
                customButtons: {
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

        editor.I18n.addMessages({
            en
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
    }

    static setupBodyForGrapesJsEditor () {
        if (!$('body').hasClass('grapes-js-editor-opened')) {
            $('body').addClass('grapes-js-editor-opened');
        }
    }
}

(new Register()).registerCallback(InitGrapesJs.init, 'InitGrapesJs.init');
