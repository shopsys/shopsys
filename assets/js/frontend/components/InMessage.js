import 'framework/common/components';
import Register from 'framework/common/utils/Register';

export default class InMessage {

    detachMessage () {
        $(document).on('click', $('.in-message'), function () {
            $('.in-message').detach();
        });
    }

    static init () {
        const Message = new InMessage();
        Message.detachMessage();
    }
}

(new Register()).registerCallback(InMessage.init);
