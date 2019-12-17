import Register from '../../common/register';

export default class FlashMessage {

    constructor ($container) {
        $container.filterAllNodes('.js-flash-message .js-flash-message-close')
            .on('click.closeFlashMessage', (event) => this.onCloseFlashMessage(event));
    }

    onCloseFlashMessage (event) {
        $(event.target).closest('.js-flash-message').slideUp('fast', function () {
            $(this).remove();
        });
        event.preventDefault();
    }

    static init ($container) {
        // eslint-disable-next-line no-new
        new FlashMessage($container);
    }
}

(new Register()).registerCallback(FlashMessage.init);
