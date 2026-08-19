import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form } from 'components/Forms/Form/Form';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { FooterContainer } from 'components/Layout/Footer/FooterContainer';
import { useNewsletterSubscribeMutation } from 'graphql/requests/newsletterSubscription/mutations/NewsletterSubscribeMutation.generated';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { NewsletterFormType } from 'types/form';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import { blurInput } from 'utils/forms/blurInput';
import { clearForm } from 'utils/forms/clearForm';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';
import { useNewsletterForm, useNewsletterFormMeta, useNewsletterSubscriptionAgreement } from './newsletterFormMeta';

export const NewsletterForm: FC = () => {
    const { t } = useTranslation();
    const [, subscribeToNewsletter] = useNewsletterSubscribeMutation();
    const [formProviderMethods, defaultValues] = useNewsletterForm();
    const formMeta = useNewsletterFormMeta();
    const newsletterSubscriptionAgreement = useNewsletterSubscriptionAgreement();
    const handleError = useErrorHandler({
        form: formProviderMethods,
        customMessage: formMeta.messages.error,
    });

    const onSubscribeToNewsletterHandler: SubmitHandler<NewsletterFormType> = async (newsletterFormData) => {
        blurInput();
        const subscribeToNewsletterResult = await subscribeToNewsletter(newsletterFormData);

        if (subscribeToNewsletterResult.data?.NewsletterSubscribe !== undefined) {
            showSuccessMessage(formMeta.messages.success);
        }

        handleError(subscribeToNewsletterResult.error);

        clearForm(subscribeToNewsletterResult.error, formProviderMethods, defaultValues);
    };

    return (
        <FooterContainer className="border-t-0! bg-background-brand text-text-inverted">
            <div className="grid grid-cols-1 vl:grid-cols-[minmax(20rem,28rem)_minmax(0,32rem)] items-center vl:justify-center gap-4 vl:gap-12">
                <div className="flex flex-col gap-1.5">
                    <span className="font-secondary font-semibold text-text-inverted text-xs uppercase tracking-widest">
                        {t('Newsletter')}
                    </span>
                    <h2 className="text-balance font-secondary font-semibold text-text-inverted text-xl leading-tight tracking-tight lg:text-2xl">
                        {t('Sign up for our newsletter and get 15% off your next purchase')}
                    </h2>
                </div>

                <FormProvider {...formProviderMethods}>
                    <Form
                        className="flex w-full flex-col gap-2 lg:gap-3"
                        formName={formMeta.formName}
                        onSubmit={formProviderMethods.handleSubmit(onSubscribeToNewsletterHandler)}
                    >
                        <div className="flex items-start gap-2 lg:gap-3">
                            <div className="min-w-0 flex-1 **:[[role=alert]]:text-text-error-inverted">
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.email.name}
                                    textInputProps={{
                                        'aria-label': t('To sign up for newsletter, enter', {
                                            ns: 'accessibility',
                                        }),
                                        className:
                                            'border-white bg-input-bg-default text-input-text-default aria-invalid:border-input-border-error',
                                        inputSize: 'small',
                                        label: formMeta.fields.email.label,
                                        required: true,
                                        type: 'email',
                                        autoComplete: 'email',
                                    }}
                                />
                            </div>

                            <SubmitButton
                                aria-label={t('Send. Sign up for newsletter', { ns: 'accessibility' })}
                                className="h-12 py-0"
                                hasDisabledCursor={!formProviderMethods.formState.isValid}
                                title={t('Sign up')}
                                variant="inverted"
                            >
                                {t('Send')}
                            </SubmitButton>
                        </div>

                        <p className="text-sm text-text-inverted">{newsletterSubscriptionAgreement}</p>
                    </Form>
                </FormProvider>
            </div>
        </FooterContainer>
    );
};
