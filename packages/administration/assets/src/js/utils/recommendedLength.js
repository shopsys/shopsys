import Translator from 'bazinga-translator';
import Register from 'framework/common/utils/Register';

export default class RecommendedLength {
    constructor($input) {
        this.recommendedLength = parseInt($input.data('js-recommended-length'), 10);

        if (Number.isNaN(this.recommendedLength)) {
            return;
        }

        this.$message = $('<span class="text-muted small ms-3"></span>');

        const $inputGroup = $input.parent('.input-group');
        if ($inputGroup.length) {
            $inputGroup.after(this.$message);
        } else {
            $input.after(this.$message);
        }

        this.updateMessage($input);

        let debounceTimeout;
        $input.on('input placeholderChange', () => {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => {
                this.updateMessage($input);
            }, 300);
        });
    }

    updateMessage($input) {
        const currentLength = $input.val()?.length || $input.attr('placeholder')?.length || 0;

        const message = Translator.trans('Used: %currentLength% characters. Recommended max. %recommendedLength%', {
            currentLength,
            recommendedLength: this.recommendedLength,
        });

        this.$message.text(message);
    }

    static init($container) {
        $container
            .filterAllNodes('input[data-js-recommended-length], textarea[data-js-recommended-length]')
            .each(function () {
                new RecommendedLength($(this));
            });
    }
}

new Register().registerCallback(RecommendedLength.init, 'CharactersCounter.init');
