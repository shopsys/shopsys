import { KeyCodes } from '../../common/components/keyCodes';
import Register from '../../common/register';

export default class Grid {

    static bindGoTo () {
        const $button = $(this).find('.js-grid-go-to-button');
        const $input = $(this).find('.js-grid-go-to-input');

        $input.on('keydown.gridGoTo', (event) => {
            if (event.keyCode == KeyCodes.ENTER) {
                $button.trigger('click.gridGoTo', event);

                return false;
            }
        });

        $button.on('click.gridGoTo', (event) => {
            document.location = $(event.currentTarget).data('url').replace('--page--', $input.val());
            return false;
        });
    }

    static init () {
        $('.js-grid-go-to').each(Grid.bindGoTo);
    }
}

(new Register()).registerCallback(Grid.init);
