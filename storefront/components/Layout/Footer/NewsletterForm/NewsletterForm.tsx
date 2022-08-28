import { useNewsletterForm, useNewsletterFormMeta } from './formMeta';
import {
    NewsletterFormButtonWrapperStyled,
    NewsletterFormColumnStyled,
    NewsletterFormInputWrapperStyled,
    NewsletterFormWrapperStyled,
} from './NewsletterForm.style';
import { Heading } from 'components/Basic/Heading/Heading';
import { Button } from 'components/Forms/Button/Button';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { Form } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine/ChoiceFormLine';
import { ErrorPopup } from 'components/Forms/Lib/ErrorPopup/ErrorPopup';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError/FormLineError';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { showSuccessMessage } from 'components/Helpers/Toasts';
import { useNewsletterSubscribeMutationApi } from 'graphql/generated';
import { useHandleErrorPopupVisibility } from 'hooks/forms/UseHandleErrorPopupVisibility';
import { useHandleFormErrors } from 'hooks/forms/UseHandleFormErrors';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/UseHandleFormSuccessfulSubmit';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import { NewsletterFormType } from 'types/form';

const TEST_IDENTIFIER = 'layout-footer-newsletterform';

export const NewsletterForm: FC = () => {
    const t = useTypedTranslationFunction();
    const [subscribeToNewsletterResult, subscribeToNewsletter] = useNewsletterSubscribeMutationApi();
    const [formProviderMethods, defaultValues] = useNewsletterForm();
    const formMeta = useNewsletterFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);
    useHandleFormErrors(subscribeToNewsletterResult.error, formProviderMethods, 'footer', formMeta.messages.error);
    useHandleFormSuccessfulSubmit(
        subscribeToNewsletterResult,
        formProviderMethods,
        defaultValues,
        () => showSuccessMessage(formMeta.messages.success),
        { blur: true, reset: true },
    );

    const onSubscribeToNewsletterHandler: SubmitHandler<NewsletterFormType> = async (data, event) => {
        event?.preventDefault();
        await subscribeToNewsletter(data);
    };

    return (
        <>
            <NewsletterFormWrapperStyled data-testid={TEST_IDENTIFIER}>
                <Heading type="h2">{t('Sign up for our newsletter and get 35% discount on running apparel')}</Heading>
                <NewsletterFormColumnStyled>
                    <FormProvider {...formProviderMethods}>
                        <Form onSubmit={formProviderMethods.handleSubmit(onSubscribeToNewsletterHandler)}>
                            <NewsletterFormInputWrapperStyled>
                                <FormLine>
                                    <Controller
                                        name={formMeta.fields.email.name}
                                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                            <>
                                                <TextInput
                                                    id={formMeta.formName + '-' + formMeta.fields.email.name}
                                                    name={formMeta.fields.email.name}
                                                    label={formMeta.fields.email.label}
                                                    required={true}
                                                    type="text"
                                                    inputSize="small"
                                                    isTouched={isTouched}
                                                    hasError={invalid}
                                                    fieldRef={field}
                                                />
                                                <FormLineError
                                                    textInputSize="small"
                                                    error={error}
                                                    inputType="text-input"
                                                    testIdentifier={
                                                        formMeta.formName + '-' + formMeta.fields.email.name + '-error'
                                                    }
                                                />
                                            </>
                                        )}
                                    />
                                </FormLine>
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
                            <ChoiceFormLine>
                                <Controller
                                    name={formMeta.fields.privacyPolicy.name}
                                    render={({ fieldState: { error }, field }) => (
                                        <>
                                            <Checkbox
                                                id={formMeta.formName + '-' + formMeta.fields.privacyPolicy.name}
                                                name={formMeta.fields.privacyPolicy.name}
                                                label={formMeta.fields.privacyPolicy.label}
                                                required={true}
                                                fieldRef={field}
                                            />
                                            <FormLineError
                                                error={error}
                                                inputType="checkbox"
                                                testIdentifier={
                                                    formMeta.formName +
                                                    '-' +
                                                    formMeta.fields.privacyPolicy.name +
                                                    '-error'
                                                }
                                            />
                                        </>
                                    )}
                                />
                            </ChoiceFormLine>
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
