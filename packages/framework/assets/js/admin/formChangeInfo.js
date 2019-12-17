import $ from 'jquery';
import Register from '../common/register';

export default class FormChangeInfo {

    constructor () {
        FormChangeInfo.isFormSubmitted = FormChangeInfo.isFormSubmitted || false;
        FormChangeInfo.isInfoShown = FormChangeInfo.isInfoShown || false;
    }

    initContent ($container) {
        $container.filterAllNodes('.web__content form')
            .change(() => FormChangeInfo.showInfo())
            .each(function () {
                if ($(this).find('.form-input-error:first, .js-validation-errors-list li:first').length > 0) {
                    FormChangeInfo.showInfo();
                }
            });
    }

    initDocument () {
        $(document).on('submit', '.web__content form', function (event) {
            if (event.isDefaultPrevented() === false) {
                FormChangeInfo.isFormSubmitted = true;
            }
        });

        $(window).on('beforeunload', function () {
            if (FormChangeInfo.isInfoShown && !FormChangeInfo.isFormSubmitted) {
                // return Shopsys.translator.trans('You have unsaved changes!');
                return 'You have unsaved changes!';
            }
        });
    }

    initWysiwygEditors () {
        if (typeof CKEDITOR !== 'undefined') {
            for (let i in CKEDITOR.instances) {
                const instance = CKEDITOR.instances[i];
                if (!instance.formChangeInfoInitilized) {
                    instance.on('change', FormChangeInfo.showInfo);
                    instance.formChangeInfoInitilized = true;
                }
            }
        }
    }

    static showInfo () {
        // var textToShow = Shopsys.translator.trans('You have made changes, don\'t forget to save them!');
        const textToShow = 'You have made changes, don\'t forget to save them!';
        const $fixedBarIn = $('.web__content .window-fixed-bar .window-fixed-bar__in');
        const $infoDiv = $fixedBarIn.find('#js-form-change-info');
        if (!FormChangeInfo.isInfoShown) {
            $fixedBarIn.prepend(
                '<div class="window-fixed-bar__item">\
                    <div id="js-form-change-info" class="window-fixed-bar__item__cell">\
                        <strong><i class="window-fixed-bar__item__cell__icon svg svg-info"></i> ' + textToShow + '</strong>\
                    </div>\
                </div>');
        } else {
            $infoDiv.text = textToShow;
        }
        if ($fixedBarIn.length > 0) {
            FormChangeInfo.isInfoShown = true;
        }
    };

    static removeInfo () {
        $('#js-form-change-info').remove();
        FormChangeInfo.isInfoShown = false;
    };

    static init($container) {
        const formChangeInfo = new FormChangeInfo();
        formChangeInfo.initContent($container);
        formChangeInfo.initWysiwygEditors();
        formChangeInfo.initDocument();
    }

}

(new Register()).registerCallback(FormChangeInfo.init);
