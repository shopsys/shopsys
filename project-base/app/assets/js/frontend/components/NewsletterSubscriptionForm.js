import Ajax from 'framework/common/utils/Ajax';
import Register from 'framework/common/utils/Register';
import Window from '../utils/Window';
import Translator from 'bazinga-translator';
import CustomizeBundle from 'framework/common/validation/customizeBundle';

const subscriptionFormSelector = 'form[name="subscription_form"]';

export default class NewsletterSubscriptionForm {

    ajaxSubmit (event, newsletterSubscriptionForm) {
        event.preventDefault();

        if (CustomizeBundle.isFormValid($('form[name="subscription_form"]')) === false) {
            return;
        }

        Ajax.ajax({
            loaderElement: 'body',
            url: $(event.currentTarget).attr('action'),
            method: 'post',
            data: $(event.currentTarget).serialize(),
            success: newsletterSubscriptionForm.onSuccess
        });
    }

    onSuccess (data) {

        if (data['success'] === false) {
            // eslint-disable-next-line no-new
            new Window({
                content: NewsletterSubscriptionForm.formatErrors(data['errors']),
                buttonCancel: true,
                textCancel: Translator.trans('Close')
            });
        } else {

            // eslint-disable-next-line no-new
            new Window({
                content: Translator.trans('You have been successfully subscribed to our newsletter.'),
                buttonCancel: true,
                textCancel: Translator.trans('Close')
            });
        }
    }

    static formatErrors (arrayOfErrors) {
        if (!arrayOfErrors || arrayOfErrors.length === 0) {
            return Translator.trans('Subscription was failed');
        }

        let errorMessage = '<ul>';

        errorMessage += '</ul>';
        arrayOfErrors.forEach(error => {
            errorMessage += '<li>' + error + '</li>';
        });

        return errorMessage;
    }

    static init ($container) {
        const newsletterSubscriptionForm = new NewsletterSubscriptionForm();
        $container.filterAllNodes(subscriptionFormSelector)
            .on('submit', (event) => newsletterSubscriptionForm.ajaxSubmit(event, newsletterSubscriptionForm));
    }
}

(new Register()).registerCallback(NewsletterSubscriptionForm.init, 'NewsletterSubscriptionForm.init');
