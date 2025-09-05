import Translator from 'bazinga-translator';
import grapesjs from 'grapesjs';
import ckeditorPlugin from 'grapesjs-plugin-ckeditor';
import newsletterPlugin from 'grapesjs-preset-newsletter';
import 'magnific-popup';
import { cs } from './locales/cs.js';
import { en } from './locales/en.js';

export default class GrapesMailEditor {
    static openGrapesMailEditor(_event, textareaId, elfinderUrl, templateHtml, bodyVariables, customPlugins) {
        GrapesMailEditor.setupBodyForGrapesJsEditor();
        const $templateHtml = $(`<div>${templateHtml}</div>`);

        const variables = JSON.parse(JSON.stringify(bodyVariables));

        const defaultPlugins = [
            newsletterPlugin,
            ckeditorPlugin,
            'buttons',
            'ckeditor',
            'mail-template',
            'mail-text',
            'mail-button-link',
            'mail-custom-image',
        ];

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
            plugins: defaultPlugins.concat(customPlugins),
            i18n: {
                locale: Translator.locale,
                detectLocale: false,
                messages: {
                    en,
                    cs,
                },
            },
            pluginsOpts: {
                [newsletterPlugin]: {
                    styleManagerSectors: [],
                    useCustomTheme: false,
                },
                [ckeditorPlugin]: {
                    ckeditor: '',
                    options: {
                        enterMode: 2,
                        versionCheck: false,
                        language: Translator.locale,
                        format_tags: 'p;h2;h3;h4;h5;h6;pre;address;div',
                        toolbar: [
                            { name: 'basicstyles', items: ['Bold', 'Italic', 'Strike', '-', 'RemoveFormat'] },
                            { name: 'format', items: ['Format'] },
                            { name: 'size', items: ['FontSize'] },
                            { name: 'links', items: ['Link', 'Unlink'] },
                            {
                                name: 'paragraph',
                                items: [
                                    'NumberedList',
                                    'BulletedList',
                                    '-',
                                    'JustifyLeft',
                                    'JustifyCenter',
                                    'JustifyRight',
                                    'JustifyBlock',
                                ],
                            },
                            { name: 'colors', items: ['TextColor', 'BGColor'] },
                            { name: 'document', items: ['Source'] },
                            { name: 'insert', items: ['SpecialChar', 'strinsert'] },
                        ],
                        extraPlugins: 'strinsert',
                        removePlugins: 'exportpdf,magicline',
                        strinsert_button_label: Translator.trans('Insert variable'),
                        strinsert_strings: [
                            { name: Translator.trans('Mandatory variables') },
                            ...variables
                                .filter(variable => variable.isRequired === true)
                                .map(variable => {
                                    return { name: variable.label, value: variable.placeholder };
                                }),
                            { name: Translator.trans('Optional variables') },
                            ...variables
                                .filter(variable => variable.isRequired === false)
                                .map(variable => {
                                    return { name: variable.label, value: variable.placeholder };
                                }),
                        ],
                    },
                },
                buttons: {
                    textareaId: textareaId,
                    isMail: true,
                },
            },
            styleManager: {
                clearProperties: true,
                appendTo: document.querySelector('#panels'),
            },
            selectorManager: {
                componentFirst: true,
            },
            assetManager: {
                custom: {
                    open(props) {
                        $.magnificPopup.open({
                            items: { src: elfinderUrl },
                            type: 'iframe',
                            closeOnBgClick: true,
                            callbacks: {
                                close: () => {
                                    props.close();
                                },
                            },
                        });

                        window.document.fileManagerInsertImageCallback = (_selector, url) => {
                            props.options.target.set('src', url);
                            $.magnificPopup.close();
                            props.close();
                        };
                    },
                },
            },
        });

        editor.addStyle(`
            .gjs-editable {
                min-height: 50px !important;
                padding-block: 1px !important;
            }
        `);

        editor.once('load', () => {
            editor.Panels.getButton('options', 'sw-visibility').set('active', 1);
            editor.Panels.removeButton('views', 'open-sm');

            const editableContent = $(`#${textareaId}`).val();
            const $gjsEditable = editor.getWrapper().find('.gjs-editable')[0];

            if ($gjsEditable) {
                $gjsEditable.append(editableContent);
            }
        });

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

        editor.BlockManager.get('link-block')
            .set('category', 'basic-objects')
            .set('attributes', { class: 'mail-icon' });
        editor.BlockManager.get('sect50').set('category', 'basic-objects').set('attributes', { class: 'mail-icon' });
        editor.BlockManager.get('sect100').set('category', 'basic-objects').set('attributes', { class: 'mail-icon' });
    }

    static setupBodyForGrapesJsEditor() {
        if (!$('body').hasClass('grapes-js-editor-opened')) {
            $('body').addClass('grapes-js-editor-opened');
        }
    }
}
