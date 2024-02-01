import Register from '../../../common/utils/Register';
import { VALIDATION_GROUP_DEFAULT } from './validation';

export default function validationMailTemplate () {
    $('#js-mail-templates').find('.js-mail-template').each(function () {
        const self = this;
        const sendMailId = $(this).attr('id') + '_sendMail';

        $(this).jsFormValidator({
            'groups': function () {

                const groups = [VALIDATION_GROUP_DEFAULT];
                if ($(self).find('#' + sendMailId).is(':checked')) {
                    groups.push('sendMail');
                }

                return groups;
            }
        });
    });
}

(new Register()).registerCallback(validationMailTemplate, 'validationMailTemplate');
