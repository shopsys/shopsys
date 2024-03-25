import { useContactForm, useContactFormMeta } from './contactFormMeta';
import { Link } from 'components/Basic/Link/Link';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { TextareaControlled } from 'components/Forms/Textarea/TextareaControlled';
import { Webline } from 'components/Layout/Webline/Webline';
import { usePrivacyPolicyArticleUrlQuery } from 'graphql/requests/articles/queries/PrivacyPolicyArticleUrlQuery.generated';
import { useContactMutation } from 'graphql/requests/contact/mutations/ContactMutation.generated';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { clearForm } from 'helpers/forms/clearForm';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { showSuccessMessage } from 'helpers/toasts';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import React, { useCallback } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { ContactFormType } from 'types/form';

const ErrorPopup = dynamic(() => import('components/Forms/Lib/ErrorPopup').then((component) => component.ErrorPopup));

export const ContactContent: FC = () => {
    const { t } = useTranslation();
    const [formProviderMethods, defaultValues] = useContactForm();
    const formMeta = useContactFormMeta(formProviderMethods);
    const [{ data }] = useSettingsQuery({ requestPolicy: 'cache-only' });
    const [{ data: privacyPolicyArticleUrlData }] = usePrivacyPolicyArticleUrlQuery();
    const privacyPolicyArticleUrl = privacyPolicyArticleUrlData?.privacyPolicyArticle?.slug;
    const [isErrorPopupVisible, setErrorPopupVisibility] = useErrorPopupVisibility(formProviderMethods);
    const [, contact] = useContactMutation();

    const onSubmitHandler = useCallback<SubmitHandler<ContactFormType>>(
        async (values) => {
            const contactResult = await contact({ input: values });

            if (contactResult.data?.Contact !== undefined) {
                showSuccessMessage(formMeta.messages.success);
            }

            handleFormErrors(contactResult.error, formProviderMethods, t, formMeta.messages.error);
            clearForm(contactResult.error, formProviderMethods, defaultValues);
        },
        [contact, formMeta.messages, formProviderMethods, t, defaultValues],
    );

    return (
        <>
            <div className="mb-8">
                <Webline>
                    <h1 className="mb-3">{t('Write to us')}</h1>
                    {data?.settings?.contactFormMainText !== undefined && (
                        <div className="mb-4" dangerouslySetInnerHTML={{ __html: data.settings.contactFormMainText }} />
                    )}
                    <FormProvider {...formProviderMethods}>
                        <Form onSubmit={formProviderMethods.handleSubmit(onSubmitHandler)}>
                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.name.name}
                                render={(textInput) => (
                                    <FormColumn className="lg:w-[calc(65%+0.75rem)]">
                                        <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                                            {textInput}
                                        </FormLine>
                                    </FormColumn>
                                )}
                                textInputProps={{
                                    label: formMeta.fields.name.label,
                                    required: true,
                                    type: 'text',
                                    autoComplete: 'name',
                                }}
                            />
                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.email.name}
                                render={(textInput) => (
                                    <FormColumn className="lg:w-[calc(65%+0.75rem)]">
                                        <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                                            {textInput}
                                        </FormLine>
                                    </FormColumn>
                                )}
                                textInputProps={{
                                    label: formMeta.fields.email.label,
                                    required: true,
                                    type: 'email',
                                    autoComplete: 'email',
                                }}
                            />
                            <TextareaControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.message.name}
                                render={(textarea) => (
                                    <FormColumn className="lg:w-[calc(65%+0.75rem)]">
                                        <FormLine bottomGap className="w-full">
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
                            <div className="mb-4">
                                <Trans
                                    defaultTrans="By clicking on the Send message button, you agree with the <lnk1>processing of privacy policy</lnk1>."
                                    i18nKey="ContactFormInfo"
                                    components={{
                                        lnk1:
                                            privacyPolicyArticleUrl !== undefined ? (
                                                <Link isExternal href={privacyPolicyArticleUrl} target="_blank" />
                                            ) : (
                                                <span />
                                            ),
                                    }}
                                />
                            </div>
                            <SubmitButton isWithDisabledLook={!formProviderMethods.formState.isValid} variant="primary">
                                {t('Send message')}
                            </SubmitButton>
                        </Form>
                    </FormProvider>
                </Webline>
            </div>

            {isErrorPopupVisible && (
                <ErrorPopup
                    fields={formMeta.fields}
                    gtmMessageOrigin={GtmMessageOriginType.other}
                    onCloseCallback={() => setErrorPopupVisibility(false)}
                />
            )}
        </>
    );
};
