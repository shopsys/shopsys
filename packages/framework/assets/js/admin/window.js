import $ from 'jquery';
import { KeyCodes } from '../common/components/keyCodes';
import Register from '../common/register';
import Translator from 'bazinga-translator';

const defaults = {
    content: '',
    buttonClose: true,
    buttonCancel: false,
    buttonContinue: false,
    textContinue: Translator.trans('Yes'),
    textCancel: Translator.trans('No'),
    urlContinue: '#',
    wide: false,
    cssClass: '',
    closeOnBgClick: true,
    eventClose: function () {},
    eventContinue: function () {},
    eventCancel: function () {}
};

export default class Window {

    /**
     * content (string)
     * buttonClose (bool)
     * buttonContinue (bool)
     * textContinue (string)
     * eventClose (function)
     * eventContinue (function)
     * urlContinue (string)
     */
    constructor (inputOptions) {
        this.$activeWindow = null;

        this.options = $.extend(defaults, inputOptions);

        if (this.$activeWindow !== null) {
            this.$activeWindow.trigger('windowFastClose');
        }

        this.$window = $('<div class="window window--active"></div>');
        if (this.options.wide) {
            this.$window.addClass('window--wide');
        }
        if (this.options.cssClass !== '') {
            this.$window.addClass(this.options.cssClass);
        }

        const $windowContent = $('<div class="js-window-content"></div>').html(this.options.content);

        this.$activeWindow = this.$window;

        const _this = this;
        this.$window.on('windowClose', () => {
            Window.hideOverlay();
            $(_this.$window).fadeOut('fast', () => $(this).trigger('windowFastClose'));
        });

        this.$window.on('windowFastClose', () => {
            $(_this.$window).remove();
            _this.$activeWindow = null;
        });

        this.$window.append($windowContent);
        if (this.options.buttonClose) {
            const $windowButtonClose = $('<a href="#" class="window-button-close window__close js-window-button-close" title="' + Translator.trans('Close (Esc)') + '">X</a>');
            $windowButtonClose
                .on('click.window', _this.options.eventClose)
                .on('click.windowClose', function () {
                    _this.$window.trigger('windowClose');
                    return false;
                });
            this.$window.append($windowButtonClose);
        }

        $('body').keyup(function (event) {
            if (event.keyCode === KeyCodes.ESCAPE) {
                _this.$window.trigger('windowClose');
                return false;
            }
        });

        const $windowActions = $('<div class="window__actions"></div>');
        if (this.options.buttonCancel) {
            const $windowButtonCancel = $('<a href="#" class="window__actions__btn window-button-cancel btn btn--default"></a>');
            $windowButtonCancel
                .text(_this.options.textCancel)
                .on('click.windowEventCancel', this.options.eventCancel)
                .on('click.windowEventClose', this.options.eventClose)
                .on('click.windowClose', function () {
                    _this.$window.trigger('windowClose');
                    return false;
                });
            $windowActions.append($windowButtonCancel);
        }

        if (this.options.buttonContinue) {
            const $windowButtonContinue = $('<a href="" class="window__actions__btn window-button-continue btn"></a>');
            $windowButtonContinue
                .text(this.options.textContinue)
                .attr('href', this.options.urlContinue)
                .bind('click.window', this.options.eventContinue)
                .bind('click.windowContinue', function () {
                    _this.$window.trigger('windowClose');
                    if ($(this).attr('href') === '#') {
                        return false;
                    }
                });
            $windowActions.append($windowButtonContinue);
        }

        if ($windowActions.children().length > 0) {
            _this.$window.append($windowActions);
        }

        (new Register()).registerNewContent(this.$window);

        this.show();
    }

    show () {
        Window.showOverlay();
        if (this.options.closeOnBgClick) {
            const _this = this;
            Window.getOverlay().click(function () {
                _this.$window.trigger('windowClose');
                return false;
            });
        }
        this.$window.hide().appendTo(Window.getMainContainer());
        if (this.options.wide) {
            this.moveToCenter();
        }
        this.$window.fadeIn('fast');
    }

    moveToCenter () {
        let relativeY = $(window).height() / 2 - this.$window.height() / 2;
        let minRelativeY = $(window).height() * 0.1;

        if (relativeY < minRelativeY) {
            relativeY = minRelativeY;
        }

        const top = Math.round($(window).scrollTop() + relativeY);

        this.$window.css({ top: top + 'px' });
    }

    getWindow () {
        return this.$window;
    }

    static getMainContainer () {
        let $mainContainer = $('#window-main-container');
        if ($mainContainer.length === 0) {
            $mainContainer = $('<div id="window-main-container"></div>');
            $('body').append($mainContainer);
        }
        return $mainContainer;
    };

    static getOverlay () {
        let $overlay = $('#js-overlay');
        if ($overlay.length === 0) {
            $overlay = $('<div id="js-overlay"></div>');
        }
        return $overlay;
    };

    static showOverlay () {
        let $overlay = Window.getOverlay();
        $('body').append($overlay);
    };

    static hideOverlay () {
        if ($('#js-overlay').length !== 0) {
            $('#js-overlay').remove();
        }
    };
}
