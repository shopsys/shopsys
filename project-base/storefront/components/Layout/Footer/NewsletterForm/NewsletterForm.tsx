import { useNewsletterForm, useNewsletterFormMeta } from './newsletterFormMeta';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { FooterContainer } from 'components/Layout/Footer/FooterContainer';
import { useNewsletterSubscribeMutation } from 'graphql/requests/newsletterSubscription/mutations/NewsletterSubscribeMutation.generated';
import useTranslation from 'next-translate/useTranslation';
import { useCallback } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { NewsletterFormType } from 'types/form';
import { blurInput } from 'utils/forms/blurInput';
import { clearForm } from 'utils/forms/clearForm';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { useErrorPopup } from 'utils/forms/useErrorPopup';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

export const NewsletterForm: FC = () => {
    const { t } = useTranslation();
    const [, subscribeToNewsletter] = useNewsletterSubscribeMutation();
    const [formProviderMethods, defaultValues] = useNewsletterForm();
    const formMeta = useNewsletterFormMeta(formProviderMethods);

    useErrorPopup(formProviderMethods, formMeta.fields);

    const onSubscribeToNewsletterHandler = useCallback<SubmitHandler<NewsletterFormType>>(
        async (newsletterFormData) => {
            blurInput();
            const subscribeToNewsletterResult = await subscribeToNewsletter(newsletterFormData);

            if (subscribeToNewsletterResult.data?.NewsletterSubscribe !== undefined) {
                showSuccessMessage(formMeta.messages.success);
            }

            handleFormErrors(subscribeToNewsletterResult.error, formProviderMethods, t, formMeta.messages.error);

            clearForm(subscribeToNewsletterResult.error, formProviderMethods, defaultValues);
        },
        [formMeta.messages, formProviderMethods, subscribeToNewsletter, t, defaultValues],
    );

    return (
        <FooterContainer className="bg-background-accent-less">
            <div className="vl:gap-44 grid grid-cols-1 items-center gap-5 lg:grid-cols-2">
                <div className="font-secondary text-lg font-semibold text-balance lg:text-center">
                    {t('Sign up for our newsletter and get 35% discount on running apparel')}
                </div>

                <div>
                    <FormProvider {...formProviderMethods}>
                        <Form
                            className="grid grid-cols-3 grid-rows-2 gap-2 lg:gap-3"
                            onSubmit={formProviderMethods.handleSubmit(onSubscribeToNewsletterHandler)}
                        >
                            <div className="col-span-2">
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.email.name}
                                    render={(textInput) => <FormLine>{textInput}</FormLine>}
                                    textInputProps={{
                                        'aria-label': t('To sign up for newsletter, enter'),
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
                                    render={(checkbox) => <ChoiceFormLine className="mb-0">{checkbox}</ChoiceFormLine>}
                                    checkboxProps={{
                                        label: formMeta.fields.privacyPolicy.label,
                                        required: true,
                                    }}
                                />
                            </div>

                            <div className="col-start-3 row-start-1">
                                <SubmitButton
                                    aria-label={t('Submit form to sign up for newsletter')}
                                    className="h-12 w-full py-0 sm:w-auto"
                                    isWithDisabledLook={!formProviderMethods.formState.isValid}
                                    title={t('Sign up')}
                                    variant="inverted"
                                >
                                    {t('Send')}
                                </SubmitButton>
                            </div>
                        </Form>
                    </FormProvider>
                </div>
            </div>
        </FooterContainer>
    );
};
