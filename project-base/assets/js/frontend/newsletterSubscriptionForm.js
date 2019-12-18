import $ from 'jquery';
import Ajax from '../copyFromFw/ajax';
import Register from '../copyFromFw/register';
import Window from './window';
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
    };

    onSuccess (data) {
        $(subscriptionFormSelector).replaceWith(data);

        // We must select again from modified DOM, because replaceWith() does not change previous jQuery collection.
        const $newContent = $(subscriptionFormSelector);
        const $emailInput = $newContent.find('input[name="subscription_form[email]"]');

        (new Register()).registerNewContent($newContent);
        if ($newContent.data('success')) {
            $emailInput.val('');

            // eslint-disable-next-line no-new
            new Window({
                content: Translator.trans('You have been successfully subscribed to our newsletter.'),
                buttonCancel: true,
                textCancel: Translator.trans('Close')
            });
        }
    };

    static init ($container) {
        const newsletterSubscriptionForm = new NewsletterSubscriptionForm();
        $container.filterAllNodes(subscriptionFormSelector)
            .on('submit', (event) => newsletterSubscriptionForm.ajaxSubmit(event, newsletterSubscriptionForm));
    }
}

(new Register()).registerCallback(NewsletterSubscriptionForm.init);
