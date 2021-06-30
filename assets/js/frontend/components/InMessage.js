export default class InMessage {

    detachMessage () {
        $(document).on('click', '.js-message-close', function () {
            $('.in-message').detach();
        });
    }

    static init () {
        const Message = new InMessage();
        Message.detachMessage();
    }
}

$(document).ready(function () {
    InMessage.init();
});
