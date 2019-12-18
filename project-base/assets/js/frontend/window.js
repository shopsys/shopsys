import $ from 'jquery';
import { KeyCodes } from '../copyFromFw/components/keyCodes';
import Timeout from '../copyFromFw/components/timeout';
import Translator from 'bazinga-translator';

const defaults = {
    content: '',
    buttonClose: true,
    buttonCancel: false,
    buttonContinue: false,
    textContinue: Translator.trans('Yes'),
    textCancel: Translator.trans('No'),
    textHeading: '',
    urlContinue: '#',
    cssClass: 'window-popup--standard',
    cssClassContinue: '',
    cssClassCancel: '',
    cssClassHeading: '',
    closeOnBgClick: true,
    eventClose: function () {
    },
    eventContinue: function () {
    },
    eventCancel: function () {
    },
    eventOnLoad: function () {
    }
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

        this.$window = $('<div class="window-popup" id="js-window"></div>');
        if (this.options.cssClass !== '') {
            this.$window.addClass(this.options.cssClass);
        }

        const $windowContent = $('<div class="js-window-content window-popup__in"></div>');

        if (this.options.textHeading !== '') {
            $windowContent.append('<h2 class="' + this.options.cssClassHeading + '">' + this.options.textHeading + '</h2>');
        }

        $windowContent.append(
            '<div class="display-none in-message in-message--alert js-window-validation-errors"></div>'
            + this.options.content
        );

        this.$activeWindow = this.$window;

        const _this = this;
        this.$window.bind('windowClose', function () {
            _this.$window.removeClass('window-popup--active');
            Window.hideOverlay();

            setTimeout(function () {
                _this.$activeWindow.trigger('windowFastClose');
            }, Window.animationTime);
        });

        this.$window.bind('windowFastClose', function () {
            $(this).remove();
            Window.hideOverlay();
            this.$activeWindow = null;
        });

        this.$window.append($windowContent);
        if (this.options.buttonClose) {
            const $windowButtonClose = $('<a href="#" class="window-button-close window-popup__close js-window-button-close" title="' + Translator.trans('Close (Esc)') + '"><i class="svg svg-remove-thin"></i></a>');
            $windowButtonClose
                .bind('click.window', this.options.eventClose)
                .bind('click.windowClose', function () {
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

        const $windowActions = $('<div class="window-popup__actions"></div>');
        if (this.options.buttonContinue && this.options.buttonCancel) {
            $windowActions.addClass('window-popup__actions--multiple-buttons');
        }

        if (this.options.buttonContinue) {
            const $windowButtonContinue = $('<a href="" class="window-popup__actions__btn window-popup__actions__btn--continue window-button-continue btn"><i class="svg svg-arrow"></i></a>');
            $windowButtonContinue
                .append(document.createTextNode(this.options.textContinue))
                .addClass(this.options.cssClassContinue)
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

        if (this.options.buttonCancel) {
            const $windowButtonCancel = $('<a href="#" class="window-popup__actions__btn window-popup__actions__btn--cancel window-button-cancel btn"><i class="svg svg-arrow"></i></a>');

            $windowButtonCancel
                .append(document.createTextNode(this.options.textCancel))
                .addClass(this.options.cssClassCancel)
                .bind('click.windowEventCancel', this.options.eventCancel)
                .bind('click.windowEventClose', this.options.eventClose)
                .bind('click.windowClose', function () {
                    _this.$window.trigger('windowClose');
                    return false;
                });
            $windowActions.append($windowButtonCancel);
        }

        if ($windowActions.children().length > 0) {
            this.$window.append($windowActions);
        }

        this.show();
        $(window).resize(function () {
            Timeout.setTimeoutAndClearPrevious('window.window.resize', function () {
                _this.fixVerticalAlign();
            }, 200);
        });
    }

    /**
     * Window with big height is fixed on top of viewport, smaller window is centered in viewport
     */
    fixVerticalAlign () {
        const windowAndViewportRatioLimitToCenter = 0.9;
        if (this.$window.height() / $(window).height() < windowAndViewportRatioLimitToCenter) {
            this.moveToCenter();
        } else {
            // remove css attribute "top" which is used by function moveToCenter()
            this.$window.css({ top: '' });
        }
    }

    show () {
        const _this = this;
        Window.showOverlay();
        if (_this.options.closeOnBgClick) {
            Window.getOverlay().click(function () {
                _this.$window.trigger('windowClose');
                return false;
            });
        }
        _this.$window.appendTo(Window.getMainContainer());
        if (this.$window.height() < Window.flexPopupHeightIssueDetectionBoundaryHeight) {
            $('html').addClass('is-flex-popup-height-issue-detected');
        }
        _this.fixVerticalAlign();
        setTimeout(function () {
            _this.$window.addClass('window-popup--active');
            _this.options.eventOnLoad();
        }, Window.animationTime);
    }

    moveToCenter () {
        let relativeY = $(window).height() / 2 - this.$window.innerHeight() / 2;
        let minRelativeY = 10;

        if (relativeY < minRelativeY) {
            relativeY = minRelativeY;
        }

        const top = Math.round(relativeY);

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
            $overlay = $('<div class="window-popup__overlay" id="js-overlay"></div>');
        }
        return $overlay;
    };

    static showOverlay () {
        let $overlay = Window.getOverlay();
        $('body').addClass('web--window-activated').append($overlay);

        // timeout 0 to asynchronous run to fix css animation fade
        setTimeout(function () {
            $overlay.addClass('window-popup__overlay--active');
        }, 0);
    };

    static hideOverlay () {
        let $overlay = $('#js-overlay');
        $('body').removeClass('web--window-activated');
        $overlay.removeClass('window-popup__overlay--active');

        if ($overlay.length !== 0) {
            setTimeout(function () {
                $overlay.remove();
            }, Window.animationTime);
        }
    };
}

Window.animationTime = 300;
Window.flexPopupHeightIssueDetectionBoundaryHeight = 45;
