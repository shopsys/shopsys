import Translator from 'bazinga-translator';
import Register from '../../common/utils/Register';
import Ajax from '../../common/utils/Ajax';

export default class AiChat {

    constructor () {
        this.$chat = $('#js-ai-chat');
        this.loadUrl = this.$chat.data('chat-load-url');
        this.saveMessageUrl = this.$chat.data('save-message-url');
        this.$chatInput = $('#js-ai-chat-input');
        this.$chatSubmit = $('#js-ai-chat-submit');
        this.$chatOutput = $('#js-ai-chat-output');
        this.$chatIcon = $('#js-ai-chat-icon');
        this.$chatClose = $('#js-ai-chat-close');
        this.$chatMenu = $('#js-ai-chat-context-menu');
        this.$chatEditor = $('#js-ai-chat-field-editor');
        this.chatSelection = '';

        this.$chatIcon.on('click', () => {
            this.$chatIcon.removeClass('chat-visible');
            this.$chatIcon.addClass('chat-hidden');
            this.$chat.removeClass('chat-hidden');
            this.$chat.addClass('chat-visible');
        });

        this.$chatClose.on('click', () => {
            this.$chat.removeClass('chat-visible');
            this.$chat.addClass('chat-hidden');
            this.$chatIcon.removeClass('chat-hidden');
            this.$chatIcon.addClass('chat-visible');
        });

        const _this = this;
        this.$chatSubmit.on('click', () => {
            _this.submitQuestion(_this.$chatInput.val(), _this.$chatOutput, this.$chatInput);
        });

        const hideMenu = () => {
            this.$chatMenu.hide();
            this.$chatMenu.html('');
        };

        $().on('click', () => {
            hideMenu();
            this.$chatEditor.hide();
        });

        this.$chatOutput.on('contextmenu', (e) => {
            const selection = window.getSelection().toString().trim();
            if (!selection) {
                hideMenu();
                return;
            }
            e.preventDefault();

            this.chatSelection = selection;

            // this.$chatMenu.html(selection);
            const offset = this.$chat.offset();
            const pageX = e.pageX - offset.left;
            const pageY = e.pageY - offset.top;
            this.$chatMenu.css('left', `${pageX}px`);
            this.$chatMenu.css('top', `${pageY}px`);

            const fields = this.scrapeFormItems();
            fields.forEach(f => {
                $('<li>')
                    .text(f.label)
                    .data('input', $(f.el))
                    .appendTo(this.$chatMenu);
            });

            this.$chatMenu.show();
        });

        // --- klik na položku menu -> otevře editor ---
        this.$chatMenu.on('click', 'li', (e) => {
            e.stopPropagation();
            const $li = $(e.currentTarget);
            console.log($li.data('input'));
            const $input = $li.data('input');
            const $chatEditorArea = this.$chatEditor.find('textarea');
            $chatEditorArea.val($input.val() + this.chatSelection);

            this.$chatEditor.data('input', $input)
                .css({ left: 10, top: 10 })
                .show();
            hideMenu();
        });

        this.$chatEditor.on('click', '.btn-save', (e) => {
            e.preventDefault();

            const $input = this.$chatEditor.data('input');
            const value = this.$chatEditor.find('textarea').val();

            if ($input && $input.length) {
                $input.val(value)
                    .trigger('input change'); // ať se chytnou validátory / listenery
            }

            this.$chatEditor.hide();
        });

        _this.loadChat(_this.$chatOutput);
    }

    scrapeFormItems () {
        const $inputs = $('.form-line').find('input[type="text"], textarea, input[type="hidden"]');
        const fields = $inputs.map(function () {
            const $input = $(this);
            const $formLine = $input.closest('.form-line'); // blok pole
            const $labelEl = $formLine.find('.form-line__label').eq(0);

            // text labelu bez vnořených img/span
            let label = $.trim(
                $labelEl.clone() // kopie, nerušíme DOM
                    .find('*') // smaž vnořené elementy (hvězdička, ikony…)
                    .remove()
                    .end()
                    .text()
            );

            // pokud je vícejazyčné pole, připoj k labelu locale
            const locale = $input.data('locale');
            if (locale) label += ` (${locale})`;

            const domainId = $input.data('domain-id');
            if (!locale && domainId) label += '(' + Translator.trans('Domain') + ` : ${domainId})`;

            if (!label) label = $input.attr('name') || this.id || Translator.trans('without name') + ` ${$input.id}`;

            return { el: this, label };
        }).get();

        console.log(fields);
        return fields;
    }

    submitQuestion (question, $chatOutput, $chatInput) {
        Ajax.ajax({
            loaderElement: 'none',
            url: this.saveMessageUrl,
            data: JSON.stringify({ question: question }),
            contentType: 'application/json',
            type: 'post',
            success: function (data) {
                const $chatHistory = $($.parseHTML(data));
                $chatOutput.html($chatHistory);
                $chatOutput.animate({
                    scrollTop: $chatOutput.scrollHeight
                }, 300);
                $chatInput.val('');
            }
        });
    }

    loadChat ($chatOutput) {
        Ajax.ajax({
            loaderElement: 'none',
            url: this.loadUrl,
            type: 'get',
            success: function (data) {
                const $chatHistory = $($.parseHTML(data));
                $chatOutput.html($chatHistory);
                $chatOutput.animate({
                    scrollTop: $chatOutput.scrollHeight
                }, 300);
            }
        });
    }

    static init () {
        // eslint-disable-next-line no-new
        new AiChat();
    }
}

(new Register()).registerCallback(AiChat.init, 'AiChat.init');
