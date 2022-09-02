import { ContactTextStyled, ContactWrapper } from './Contact.style';
import { useContactForm, useContactFormMeta } from './formMeta';
import { Heading1Styled } from 'components/Basic/Heading/Heading.style';
import Link from 'components/Basic/Link';
import Button from 'components/Forms/Button';
import Form from 'components/Forms/Form';
import ErrorPopup from 'components/Forms/Lib/ErrorPopup';
import FormColumn from 'components/Forms/Lib/FormColumn';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import Textarea from 'components/Forms/Textarea';
import TextInput from 'components/Forms/TextInput';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { showSuccessMessage } from 'components/Helpers/Toasts';
import Webline from 'components/Layout/Webline';
import { useContactMutationApi, useSettingsQueryApi } from 'graphql/generated';
import { useHandleErrorPopupVisibility } from 'hooks/forms/UseHandleErrorPopupVisibility';
import { useHandleFormErrors } from 'hooks/forms/UseHandleFormErrors';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/UseHandleFormSuccessfulSubmit';
import { useGetPrivacyPolicyUrl } from 'hooks/routes/useGetPrivacyPolicyUrl';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Trans from 'next-translate/Trans';
import React, { FC } from 'react';
import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import { useShopsysSelector } from 'redux/main';
import { ContactFormType } from 'types/form';

const Contact: FC = () => {
    const t = useTypedTranslationFunction();
    const [formProviderMethods, defaultValues] = useContactForm();
    const formMeta = useContactFormMeta(formProviderMethods);

    const [{ data }] = useSettingsQueryApi({ requestPolicy: 'cache-only' });

    const { url } = useShopsysSelector((state) => state.domain);
    const gdprUrl = useGetPrivacyPolicyUrl();

    const [contactResult, contactMutation] = useContactMutationApi();
    useHandleFormSuccessfulSubmit(
        contactResult,
        formProviderMethods,
        defaultValues,
        () => showSuccessMessage(formMeta.messages.success),
        { reset: true },
    );
    useHandleFormErrors(contactResult.error, formProviderMethods, 'other', formMeta.messages.error);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);

    const onSubmitHandler: SubmitHandler<ContactFormType> = async (values, event) => {
        event?.preventDefault();

        await contactMutation({ input: values });
    };

    return (
        <StaticUrlGuard domainUrl={url}>
            <ContactWrapper>
                <Webline>
                    <Heading1Styled>{t('Write to us')}</Heading1Styled>
                    {data?.settings?.contactFormMainText !== undefined && (
                        <ContactTextStyled dangerouslySetInnerHTML={{ __html: data.settings.contactFormMainText }} />
                    )}
                    <FormProvider {...formProviderMethods}>
                        <Form onSubmit={formProviderMethods.handleSubmit(onSubmitHandler)}>
                            <FormColumn lg="65%">
                                <FormLine bottomGap width="100%" lg="50%">
                                    <Controller
                                        name={formMeta.fields.name.name}
                                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                            <>
                                                <TextInput
                                                    id={formMeta.formName + '-' + formMeta.fields.name.name}
                                                    name={formMeta.fields.name.name}
                                                    label={formMeta.fields.name.label}
                                                    required
                                                    type="text"
                                                    isTouched={isTouched}
                                                    hasError={invalid}
                                                    fieldRef={field}
                                                />
                                                <FormLineError
                                                    error={error}
                                                    inputType="text-input"
                                                    data-testid={
                                                        formMeta.formName + '-' + formMeta.fields.name.name + '-error'
                                                    }
                                                />
                                            </>
                                        )}
                                    />
                                </FormLine>
                            </FormColumn>
                            <FormColumn lg="65%">
                                <FormLine bottomGap width="100%" lg="50%">
                                    <Controller
                                        name={formMeta.fields.email.name}
                                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                            <>
                                                <TextInput
                                                    id={formMeta.formName + '-' + formMeta.fields.email.name}
                                                    name={formMeta.fields.email.name}
                                                    label={formMeta.fields.email.label}
                                                    required
                                                    type="email"
                                                    isTouched={isTouched}
                                                    hasError={invalid}
                                                    fieldRef={field}
                                                />
                                                <FormLineError
                                                    error={error}
                                                    inputType="text-input"
                                                    data-testid={
                                                        formMeta.formName + '-' + formMeta.fields.email.name + '-error'
                                                    }
                                                />
                                            </>
                                        )}
                                    />
                                </FormLine>
                            </FormColumn>
                            <FormColumn lg="65%">
                                <FormLine bottomGap={true} width="100%">
                                    <Controller
                                        name={formMeta.fields.message.name}
                                        render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                            <>
                                                <Textarea
                                                    id={formMeta.formName + '-' + formMeta.fields.message.name}
                                                    name={formMeta.fields.message.name}
                                                    label={formMeta.fields.message.label}
                                                    required
                                                    isTouched={isTouched}
                                                    hasError={invalid}
                                                    fieldRef={field}
                                                    rows={4}
                                                />
                                                <FormLineError
                                                    error={error}
                                                    inputType="textarea"
                                                    data-testid={
                                                        formMeta.formName +
                                                        '-' +
                                                        formMeta.fields.message.name +
                                                        '-error'
                                                    }
                                                />
                                            </>
                                        )}
                                    />
                                </FormLine>
                            </FormColumn>
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
        </StaticUrlGuard>
    );
};

export default Contact;
