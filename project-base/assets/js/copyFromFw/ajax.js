import $ from 'jquery';
import { createLoaderOverlay, showLoaderOverlay, removeLoaderOverlay } from './loaderOverlay';
import Window from '../frontend/window';

export default class Ajax {

    static ajax (options) {
        let loaderOverlayTimeout;
        const defaults = {
            loaderElement: undefined,
            loaderMessage: undefined,
            overlayDelay: 200,
            error: Ajax.showDefaultError,
            complete: function () {}
        };
        options = $.extend(defaults, options);
        const userCompleteCallback = options.complete;
        const $loaderOverlay = createLoaderOverlay(options.loaderElement, options.loaderMessage);
        const userErrorCallback = options.error;

        options.complete = function (jqXHR, textStatus) {
            userCompleteCallback.apply(this, [jqXHR, textStatus]);
            clearTimeout(loaderOverlayTimeout);
            removeLoaderOverlay($loaderOverlay);
        };

        options.error = function (jqXHR) {
            // on FireFox abort ajax request, but request was probably successful
            if (jqXHR.status !== 0) {
                userErrorCallback.apply(this, [jqXHR]);
            }
        };

        loaderOverlayTimeout = setTimeout(function () {
            showLoaderOverlay($loaderOverlay);
        }, options.overlayDelay);
        $.ajax(options);
    };

    static showDefaultError () {
        // eslint-disable-next-line no-new
        new Window({
            // content: Shopsys.translator.trans('Error occurred, try again please.')
            content: 'Error occurred, try again please.'
        });
    };

    /**
     * Calls ajax with provided options. If ajax call with the same name is already running, the new ajax call is created as pending.
     * After completion of the ajax call only last pending call with the same name is called.
     * @param {string} pendingCallName
     * @param {object} options
     */
    static ajaxPendingCall (pendingCallName, options) {

        Ajax.ajaxPendingCalls = Ajax.ajaxPendingCalls || {};

        if (typeof pendingCallName !== 'string') {
            throw new Error('Ajax queued call must have name!');
        }
        const userCompleteCallback = options.hasOwnProperty('complete') ? options.complete : null;
        const _this = this;

        options.complete = function (jqXHR, textStatus) {
            if (userCompleteCallback !== null) {
                userCompleteCallback.apply(this, [jqXHR, textStatus]);
            }

            if (Ajax.ajaxPendingCalls.hasOwnProperty(pendingCallName) === true) {
                if (Ajax.ajaxPendingCalls[pendingCallName].isPending === true) {
                    Ajax.ajaxPendingCalls[pendingCallName].isPending = false;
                    _this.ajax(Ajax.ajaxPendingCalls[pendingCallName].options);
                } else {
                    delete Ajax.ajaxPendingCalls[pendingCallName];
                }
            }
        };

        const callImmediately = Ajax.ajaxPendingCalls.hasOwnProperty(pendingCallName) === false;

        Ajax.ajaxPendingCalls[pendingCallName] = {
            isPending: true,
            options: options
        };

        if (callImmediately) {
            Ajax.ajaxPendingCalls[pendingCallName].isPending = false;
            Ajax.ajax(options);
        }
    };
}
