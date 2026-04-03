import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
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
import { useNewsletterForm, useNewsletterFormMeta } from './newsletterFormMeta';

export const NewsletterForm: FC = () => {
    const { t } = useTranslation();
    const [, subscribeToNewsletter] = useNewsletterSubscribeMutation();
    const [formProviderMethods, defaultValues] = useNewsletterForm();
    const formMeta = useNewsletterFormMeta();
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
        <FooterContainer className="bg-background-accent-less">
            <div className="grid grid-cols-1 items-center gap-5 vl:gap-44 lg:grid-cols-2">
                <div className="text-balance font-secondary font-semibold text-lg lg:text-center">
                    {t('Sign up for our newsletter and get 35% discount on running apparel')}
                </div>

                <FormProvider {...formProviderMethods}>
                    <Form
                        className="grid grid-cols-3 items-start gap-2 lg:gap-3"
                        formName={formMeta.formName}
                        onSubmit={formProviderMethods.handleSubmit(onSubscribeToNewsletterHandler)}
                    >
                        <div className="col-span-2">
                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.email.name}
                                textInputProps={{
                                    'aria-label': t('To sign up for newsletter, enter', { ns: 'accessibility' }),
                                    inputSize: 'small',
                                    label: formMeta.fields.email.label,
                                    required: true,
                                    type: 'email',
                                    autoComplete: 'email',
                                }}
                            />
                        </div>

                        <div className="col-span-3 col-start-1 row-start-2">
                            <CheckboxControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.privacyPolicy.name}
                                checkboxProps={{
                                    label: formMeta.fields.privacyPolicy.label,
                                    required: true,
                                }}
                            />
                        </div>

                        <div className="col-start-3 row-start-1">
                            <SubmitButton
                                aria-label={t('Submit form to sign up for newsletter', { ns: 'accessibility' })}
                                className="h-12 w-full py-0 sm:w-auto"
                                hasDisabledCursor={!formProviderMethods.formState.isValid}
                                title={t('Sign up')}
                                variant="inverted"
                            >
                                {t('Send')}
                            </SubmitButton>
                        </div>
                    </Form>
                </FormProvider>
            </div>
        </FooterContainer>
    );
};
