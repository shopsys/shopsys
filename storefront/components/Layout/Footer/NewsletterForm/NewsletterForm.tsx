import { useNewsletterForm, useNewsletterFormMeta } from './formMeta';
import {
    NewsletterFormButtonWrapperStyled,
    NewsletterFormColumnStyled,
    NewsletterFormInputWrapperStyled,
    NewsletterFormWrapperStyled,
} from './NewsletterForm.style';
import { Heading } from 'components/Basic/Heading/Heading';
import { Button } from 'components/Forms/Button/Button';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine/ChoiceFormLine';
import { ErrorPopup } from 'components/Forms/Lib/ErrorPopup/ErrorPopup';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { showSuccessMessage } from 'components/Helpers/Toasts';
import { useNewsletterSubscribeMutationApi } from 'graphql/generated';
import { blurInput } from 'helpers/forms/blurInput';
import { clearForm } from 'helpers/forms/clearForm';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useCallback } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { NewsletterFormType } from 'types/form';

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

            handleFormErrors(
                subscribeToNewsletterResult.error,
                formProviderMethods,
                'footer',
                t,
                formMeta.messages.error,
            );

            clearForm(subscribeToNewsletterResult.error, formProviderMethods, defaultValues);
        },
        [formMeta.messages, formProviderMethods, subscribeToNewsletter, t, defaultValues],
    );

    return (
        <>
            <NewsletterFormWrapperStyled data-testid={TEST_IDENTIFIER}>
                <Heading type="h2">{t('Sign up for our newsletter and get 35% discount on running apparel')}</Heading>
                <NewsletterFormColumnStyled>
                    <FormProvider {...formProviderMethods}>
                        <Form onSubmit={formProviderMethods.handleSubmit(onSubscribeToNewsletterHandler)}>
                            <NewsletterFormInputWrapperStyled>
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    name={formMeta.fields.email.name}
                                    render={(textInput) => <FormLine>{textInput}</FormLine>}
                                    formName={formMeta.formName}
                                    textInputProps={{
                                        inputSize: 'small',
                                        label: formMeta.fields.email.label,
                                        required: true,
                                        type: 'text',
                                    }}
                                />
                                <NewsletterFormButtonWrapperStyled>
                                    <Button
                                        type="submit"
                                        borderRadius="big"
                                        hasDisabledLook={!formProviderMethods.formState.isValid}
                                    >
                                        {t('Send')}
                                    </Button>
                                </NewsletterFormButtonWrapperStyled>
                            </NewsletterFormInputWrapperStyled>
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
                </NewsletterFormColumnStyled>
            </NewsletterFormWrapperStyled>
            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                fields={formMeta.fields}
                origin="footer"
            />
        </>
    );
};
