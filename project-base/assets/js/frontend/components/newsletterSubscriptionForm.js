import Ajax from 'framework/common/utils/ajax';
import Register from 'framework/common/utils/register';
import Window from '../utils/window';
import Translator from 'bazinga-translator';

const subscriptionFormSelector = 'form[name="subscription_form"]';

export default class NewsletterSubscriptionForm {

    ajaxSubmit (event, newsletterSubscriptionForm) {
        event.preventDefault();
        Ajax.ajax({
            loaderElement: 'body',
            url: $(event.currentTarget).attr('action'),
            method: 'post',
            data: $(event.currentTarget).serialize(),
            success: newsletterSubscriptionForm.onSuccess
        });
    }

    onSuccess (data) {
        if ($(data).data('success')) {
            $(subscriptionFormSelector).find('input[name="subscription_form[email]"]').val('');
            $(subscriptionFormSelector).find('input[name="subscription_form[privacyPolicyAgreement]"]').prop('checked', false);

            // eslint-disable-next-line no-new
            new Window({
                content: Translator.trans('You have been successfully subscribed to our newsletter.'),
                buttonCancel: true,
                textCancel: Translator.trans('Close')
            });
        }
    }

    static init ($container) {
        const newsletterSubscriptionForm = new NewsletterSubscriptionForm();
        $container.filterAllNodes(subscriptionFormSelector)
            .on('submit', (event) => newsletterSubscriptionForm.ajaxSubmit(event, newsletterSubscriptionForm));
    }
}

(new Register()).registerCallback(NewsletterSubscriptionForm.init);
