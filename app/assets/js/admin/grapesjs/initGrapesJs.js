import Register from 'framework/common/utils/Register';
import grapesjs from 'grapesjs';
import 'grapesjs-preset-webpage';
import 'grapesjs-plugin-ckeditor';
import './plugins/grapesjs-custom-buttons-plugin';
import './plugins/grapesjs-products-plugin';
import './plugins/grapesjs-text-with-image-plugin';
import './plugins/grapesjs-custom-blocks-plugin';
import './grapesjs-non-editable-page';
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
            async: false
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
                        {
                            name: 'General',
                            open: false,
                            buildProps: ['border', 'border-radius', 'background-color']
                        },
                        {
                            name: 'Layout',
                            open: false,
                            buildProps: ['margin', 'padding']
                        }
                    ]
                },
                'customButtons': {
                    textareaId: textareaId
                }
            },
            styleManager: {
                clearProperties: true,
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

        editor.Panels.getButton('options', 'sw-visibility').set('active', 1);

        const editableContent = $('#' + textareaId).val();
        editor.getWrapper().find('.gjs-editable')[0].append(editableContent);
    }
}

(new Register()).registerCallback(Grapesjs.init, 'Grapesjs.init');
