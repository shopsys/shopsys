import { ContactTextStyled, ContactWrapper } from './ContactContent.style';
import { useContactForm, useContactFormMeta } from './formMeta';
import { Heading1Styled } from 'components/Basic/Heading/Heading.style';
import { Link } from 'components/Basic/Link/Link';
import { Button } from 'components/Forms/Button/Button';
import { Form } from 'components/Forms/Form/Form';
import { ErrorPopup } from 'components/Forms/Lib/ErrorPopup/ErrorPopup';
import { FormColumn } from 'components/Forms/Lib/FormColumn/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { TextareaControlled } from 'components/Forms/Textarea/TextareaControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { showSuccessMessage } from 'components/Helpers/Toasts';
import { Webline } from 'components/Layout/Webline/Webline';
import { useContactMutationApi, useSettingsQueryApi } from 'graphql/generated';
import { clearForm } from 'helpers/forms/clearForm';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import { useGetPrivacyPolicyUrl } from 'hooks/routes/useGetPrivacyPolicyUrl';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import Trans from 'next-translate/Trans';
import React, { FC, useCallback } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { ContactFormType } from 'types/form';

export const ContactContent: FC = () => {
    const t = useTypedTranslationFunction();
    const [formProviderMethods, defaultValues] = useContactForm();
    const formMeta = useContactFormMeta(formProviderMethods);
    const [{ data }] = useSettingsQueryApi({ requestPolicy: 'cache-only' });
    const gdprUrl = useGetPrivacyPolicyUrl();
    const [isErrorPopupVisible, setErrorPopupVisibility] = useErrorPopupVisibility(formProviderMethods);
    const [, contact] = useContactMutationApi();

    const onSubmitHandler = useCallback<SubmitHandler<ContactFormType>>(
        async (values) => {
            const contactResult = await contact({ input: values });

            if (contactResult.data?.Contact !== undefined) {
                showSuccessMessage(formMeta.messages.success);
            }

            handleFormErrors(contactResult.error, formProviderMethods, 'other', t, formMeta.messages.error);
            clearForm(contactResult.error, formProviderMethods, defaultValues);
        },
        [contact, formMeta.messages, formProviderMethods, t, defaultValues],
    );

    return (
        <>
            <ContactWrapper>
                <Webline>
                    <Heading1Styled>{t('Write to us')}</Heading1Styled>
                    {data?.settings?.contactFormMainText !== undefined && (
                        <ContactTextStyled dangerouslySetInnerHTML={{ __html: data.settings.contactFormMainText }} />
                    )}
                    <FormProvider {...formProviderMethods}>
                        <Form onSubmit={formProviderMethods.handleSubmit(onSubmitHandler)}>
                            <TextInputControlled
                                control={formProviderMethods.control}
                                name={formMeta.fields.name.name}
                                render={(textInput) => (
                                    <FormColumn lg="65%">
                                        <FormLine bottomGap width="100%" lg="50%">
                                            {textInput}
                                        </FormLine>
                                    </FormColumn>
                                )}
                                formName={formMeta.formName}
                                textInputProps={{
                                    label: formMeta.fields.name.label,
                                    required: true,
                                    type: 'text',
                                }}
                            />
                            <TextInputControlled
                                control={formProviderMethods.control}
                                name={formMeta.fields.email.name}
                                render={(textInput) => (
                                    <FormColumn lg="65%">
                                        <FormLine bottomGap width="100%" lg="50%">
                                            {textInput}
                                        </FormLine>
                                    </FormColumn>
                                )}
                                formName={formMeta.formName}
                                textInputProps={{
                                    label: formMeta.fields.email.label,
                                    required: true,
                                    type: 'email',
                                }}
                            />
                            <TextareaControlled
                                name={formMeta.fields.message.name}
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                render={(textarea) => (
                                    <FormColumn lg="65%">
                                        <FormLine bottomGap width="100%">
                                            {textarea}
                                        </FormLine>
                                    </FormColumn>
                                )}
                                textareaProps={{
                                    label: formMeta.fields.message.label,
                                    required: true,
                                    rows: 4,
                                }}
                            />
                            <ContactTextStyled>
                                <Trans
                                    i18nKey="ContactFormInfo"
                                    defaultTrans="By clicking on the Send message button, you agree with the <lnk1>processing of privacy policy</lnk1>."
                                    components={{
                                        lnk1: <Link href={gdprUrl} linkType="external" target="_blank" />,
                                    }}
                                />
                            </ContactTextStyled>
                            <Button
                                type="submit"
                                borderRadius="big"
                                variant="primary"
                                hasDisabledLook={!formProviderMethods.formState.isValid}
                            >
                                {t('Send message')}
                            </Button>
                        </Form>
                    </FormProvider>
                </Webline>
            </ContactWrapper>
            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                fields={formMeta.fields}
                origin="other"
            />
        </>
    );
};
