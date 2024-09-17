import { useNewsletterForm, useNewsletterFormMeta } from './newsletterFormMeta';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
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
        <div className="relative flex flex-col pb-7 pt-8 lg:flex-row lg:items-center">
            <div className="mb-3 break-words text-lg font-bold lg:mb-0 lg:pr-5 lg:text-2xl">
                {t('Sign up for our newsletter and get 35% discount on running apparel')}
            </div>

            <div className="lg:basis-5/12">
                <FormProvider {...formProviderMethods}>
                    <Form
                        className="mt-15 sm:mt-0"
                        onSubmit={formProviderMethods.handleSubmit(onSubscribeToNewsletterHandler)}
                    >
                        <div className="mb-2 flex flex-col lg:mb-3 lg:flex-row">
                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.email.name}
                                render={(textInput) => <FormLine>{textInput}</FormLine>}
                                textInputProps={{
                                    inputSize: 'small',
                                    label: formMeta.fields.email.label,
                                    required: true,
                                    type: 'email',
                                    autoComplete: 'email',
                                }}
                            />

                            <div className="flex flex-col">
                                <SubmitButton
                                    className="h-12 py-0 max-lg:mt-3 lg:ml-3"
                                    isWithDisabledLook={!formProviderMethods.formState.isValid}
                                    variant="inverted"
                                >
                                    {t('Send')}
                                </SubmitButton>
                            </div>
                        </div>

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
                    </Form>
                </FormProvider>
            </div>
        </div>
    );
};
