import Register from '../common/register';
import Translator from 'bazinga-translator';

export default class CharactersCounter {

    constructor ($counter) {
        this.$input = $counter.find('.js-characters-counter-input input, input.js-characters-counter-input, textarea.js-characters-counter-input');
        this.$info = $counter.find('.js-characters-counter-info');
        this.recommendedLength = this.$info.data('recommended-length');

        if (this.$input.length > 0) {
            this.$input.bind('keyup placeholderChange', () => CharactersCounter.countCharacters(this));
            CharactersCounter.countCharacters(this);
        }
    };

    static countCharacters (charactersCounter) {
        let currentLength = charactersCounter.$input.val().length;
        const placeholder = charactersCounter.$input.attr('placeholder');
        if (currentLength === 0 && placeholder) {
            currentLength = placeholder.length;
        }

        charactersCounter.$info.text(Translator.trans(
            'Used: %currentLength% characters. Recommended max. %recommendedLength%',
            {
                'currentLength': currentLength,
                'recommendedLength': charactersCounter.recommendedLength
            }
        ));
    };

    static init ($container) {
        $container.filterAllNodes('.js-characters-counter').each(function () {
            // eslint-disable-next-line no-new
            new CharactersCounter($(this));
        });
    }
}

(new Register()).registerCallback(CharactersCounter.init);
