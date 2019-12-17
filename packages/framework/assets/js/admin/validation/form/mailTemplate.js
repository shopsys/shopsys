import constant from '../../constant';
import Register from '../../../common/register';

export default function validationMailTemplate () {
    $('#js-mail-templates').find('.js-mail-template').each(function () {
        const self = this;
        const sendMailId = $(this).attr('id') + '_sendMail';

        $(this).jsFormValidator({
            'groups': function () {

                const groups = [constant('\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];
                if ($(self).find('#' + sendMailId).is(':checked')) {
                    groups.push(constant('\\Shopsys\\FrameworkBundle\\Form\\Admin\\Mail\\MailTemplateFormType::VALIDATION_GROUP_SEND_MAIL'));
                }

                return groups;
            }
        });
    });
}

(new Register()).registerCallback(validationMailTemplate);
