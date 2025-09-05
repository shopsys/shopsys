import 'magnific-popup';
import Translator from 'bazinga-translator';
import grapesjs from 'grapesjs';
import ckeditorPlugin from 'grapesjs-plugin-ckeditor';
import webPagePlugin from 'grapesjs-preset-webpage';
import { cs } from './locales/cs.js';
import { en } from './locales/en.js';

export default class GrapesWebEditor {
    static openGrapesEditor(_event, frontendUrl, textareaId, elfinderUrl, allowProducts) {
        GrapesWebEditor.setupBodyForGrapesJsEditor();

        const content = $.get({
            url: frontendUrl,
            async: false,
            crossDomain: true,
        }).responseText;

        const plugins = [
            webPagePlugin,
            ckeditorPlugin,
            'nonEditablePage',
            'buttons',
            'ckeditor',
            'text',
            'link',
            'web-button-link',
            'text-with-image',
            'custom-image',
            'column1',
            'column2',
            'column3',
            'table-custom',
            'video',
            'map',
            'image-file',
            'iframe',
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
            canvas: {
                styles: ['/tailwind-for-admin/style.css'],
            },
            i18n: {
                locale: Translator.locale,
                detectLocale: false,
                messages: {
                    en,
                    cs,
                },
            },
            pluginsOpts: {
                [ckeditorPlugin]: {
                    ckeditor: '',
                    options: {
                        versionCheck: false,
                        language: Translator.locale,
                        allowedContent: true,
                        extraAllowedContent: '*(*)',
                        removePlugins: 'exportpdf,magicline',
                        format_tags: 'p;h2;h3;h4;h5;h6;pre;address;div',
                        toolbar: [
                            { name: 'basicstyles', items: ['Bold', 'Italic', 'Strike', '-', 'RemoveFormat'] },
                            { name: 'clipboard', items: ['PasteText', 'PasteFromWord'] },
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
                            { name: 'insert', items: ['SpecialChar'] },
                        ],
                    },
                },
                [webPagePlugin]: {
                    blocks: [],
                    block: () => {
                        return {
                            category: 'basic-objects',
                            attributes: { class: 'fa fa-link' },
                        };
                    },
                    useCustomTheme: false,
                },
                buttons: {
                    textareaId: textareaId,
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

        editor.TraitManager.addType('textarea', {
            createInput() {
                return document.createElement('textarea');
            },
            onUpdate({ elInput, _, trait }) {
                elInput.value = trait.changed.value;
            },
        });

        editor.once('load', () => {
            editor.Panels.getButton('options', 'sw-visibility').set('active', 1);
            editor.Panels.removeButton('views', 'open-sm');

            const editableContent = $(`#${textareaId}`).val();
            const $gjsEditable = editor.getWrapper().find('.gjs-editable')[0];

            if ($gjsEditable) {
                $gjsEditable.append(editableContent);
            }
        });
    }

    static setupBodyForGrapesJsEditor() {
        if (!$('body').hasClass('grapes-js-editor-opened')) {
            $('body').addClass('grapes-js-editor-opened');
        }
    }
}
