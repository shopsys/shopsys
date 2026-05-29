import Translator from 'bazinga-translator';
import AlertTriangle from 'icons/tabler/alert-triangle.svg';
import Register from '../../common/utils/Register';

export default class FormChangeInfo {
    constructor() {
        FormChangeInfo.isFormSubmitted = FormChangeInfo.isFormSubmitted || false;
        FormChangeInfo.isInfoShown = FormChangeInfo.isInfoShown || false;
    }

    initContent($container) {
        $container
            .filterAllNodes('.page-body form:has(button[type="submit"])')
            .not('[data-no-form-change-info]')
            .change(() => FormChangeInfo.showInfo())
            .each(function () {
                if ($(this).find('.form-input-error:first, .js-validation-errors-list li:first').length > 0) {
                    FormChangeInfo.showInfo();
                }
            });
    }

    initDocument() {
        $(document).on('submit', '.page-body form', event => {
            if (event.isDefaultPrevented() === false) {
                FormChangeInfo.isFormSubmitted = true;
            }
        });

        $(window).on('beforeunload', event => {
            if (FormChangeInfo.isInfoShown && !FormChangeInfo.isFormSubmitted) {
                event.preventDefault();
                event.returnValue = true;
            }
        });
    }

    initWysiwygEditors() {
        if (typeof CKEDITOR !== 'undefined') {
            for (const i in CKEDITOR.instances) {
                const instance = CKEDITOR.instances[i];
                if (!instance.formChangeInfoInitilized) {
                    instance.on('change', FormChangeInfo.showInfo);
                    instance.formChangeInfoInitilized = true;
                }
            }
        }
    }

    static showInfo() {
        const textToShow = Translator.trans('Unsaved changes');
        const $unsavedChangesContainer = $('[data-js-unsaved-changes-container]');

        if (FormChangeInfo.isInfoShown || $unsavedChangesContainer.length === 0) {
            return;
        }

        $unsavedChangesContainer.append(
            `<div id="js-form-change-info" class="d-flex align-items-center h-100 gap-2">
                <span class="icon-wrapper text-warning">${AlertTriangle}</span>
                <span class="small">${textToShow}</span>
            </div>`,
        );

        FormChangeInfo.isInfoShown = true;
    }

    static removeInfo() {
        $('#js-form-change-info').remove();
        FormChangeInfo.isInfoShown = false;
    }

    static init($container) {
        const formChangeInfo = new FormChangeInfo();
        formChangeInfo.initContent($container);
        formChangeInfo.initWysiwygEditors();
        formChangeInfo.initDocument();
    }
}

new Register().registerCallback(FormChangeInfo.init, 'FormChangeInfo.init');
