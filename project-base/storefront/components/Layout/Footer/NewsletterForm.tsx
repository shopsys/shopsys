import { useNewsletterForm, useNewsletterFormMeta } from './formMeta';
import { Heading } from 'components/Basic/Heading/Heading';
import { Button } from 'components/Forms/Button/Button';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { showSuccessMessage } from 'components/Helpers/toasts';
import { useNewsletterSubscribeMutationApi } from 'graphql/generated';
import { blurInput } from 'helpers/forms/blurInput';
import { clearForm } from 'helpers/forms/clearForm';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import dynamic from 'next/dynamic';
import { useCallback } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { NewsletterFormType } from 'types/form';

const ErrorPopup = dynamic(() => import('components/Forms/Lib/ErrorPopup').then((component) => component.ErrorPopup));

const TEST_IDENTIFIER = 'layout-footer-newsletterform';

export const NewsletterForm: FC = () => {
    const t = useTypedTranslationFunction();
    const [, subscribeToNewsletter] = useNewsletterSubscribeMutationApi();
    const [formProviderMethods, defaultValues] = useNewsletterForm();
    const formMeta = useNewsletterFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useErrorPopupVisibility(formProviderMethods);

    const onSubscribeToNewsletterHandler = useCallback<SubmitHandler<NewsletterFormType>>(
        async (data) => {
            blurInput();
            const subscribeToNewsletterResult = await subscribeToNewsletter(data);

            if (subscribeToNewsletterResult.data?.NewsletterSubscribe !== undefined) {
                showSuccessMessage(formMeta.messages.success);
            }

            handleFormErrors(subscribeToNewsletterResult.error, formProviderMethods, t, formMeta.messages.error);

            clearForm(subscribeToNewsletterResult.error, formProviderMethods, defaultValues);
        },
        [formMeta.messages, formProviderMethods, subscribeToNewsletter, t, defaultValues],
    );

    return (
        <>
            <div
                className="relative flex flex-col pb-7 pt-8 before:absolute before:bottom-0 before:-left-5 before:h-32 before:w-28 before:-translate-x-full before:bg-[url('/images/lines.webp')] before:content-[''] lg:flex-row lg:items-center"
                data-testid={TEST_IDENTIFIER}
            >
                <Heading type="h2" className="flex-1 lg:pr-5">
                    {t('Sign up for our newsletter and get 35% discount on running apparel')}
                </Heading>
                <div className="w-full lg:max-w-md vl:max-w-lg ">
                    <FormProvider {...formProviderMethods}>
                        <Form
                            className="mt-15 sm:mt-0"
                            onSubmit={formProviderMethods.handleSubmit(onSubscribeToNewsletterHandler)}
                        >
                            <div className="mb-2 flex flex-col lg:mb-3 lg:flex-row">
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    name={formMeta.fields.email.name}
                                    render={(textInput) => <FormLine>{textInput}</FormLine>}
                                    formName={formMeta.formName}
                                    textInputProps={{
                                        inputSize: 'small',
                                        label: formMeta.fields.email.label,
                                        required: true,
                                        type: 'email',
                                        autoComplete: 'email',
                                    }}
                                />
                                <div className="flex flex-col">
                                    <Button
                                        className="max-lg:mt-3 lg:ml-3"
                                        type="submit"
                                        isRounder
                                        isWithDisabledLook={!formProviderMethods.formState.isValid}
                                    >
                                        {t('Send')}
                                    </Button>
                                </div>
                            </div>
                            <CheckboxControlled
                                name={formMeta.fields.privacyPolicy.name}
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                                checkboxProps={{
                                    label: formMeta.fields.privacyPolicy.label,
                                    required: true,
                                }}
                            />
                        </Form>
                    </FormProvider>
                </div>
            </div>
            {isErrorPopupVisible && (
                <ErrorPopup onCloseCallback={() => setErrorPopupVisibility(false)} fields={formMeta.fields} />
            )}
        </>
    );
};
