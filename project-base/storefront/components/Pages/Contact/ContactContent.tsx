import { useContactForm, useContactFormMeta } from './contactFormMeta';
import { Heading } from 'components/Basic/Heading/Heading';
import { Link } from 'components/Basic/Link/Link';
import { Button } from 'components/Forms/Button/Button';
import { Form } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { TextareaControlled } from 'components/Forms/Textarea/TextareaControlled';
import { showSuccessMessage } from 'helpers/visual/toasts';
import { Webline } from 'components/Layout/Webline/Webline';
import { useContactMutationApi, usePrivacyPolicyArticleUrlQueryApi, useSettingsQueryApi } from 'graphql/generated';
import { clearForm } from 'helpers/forms/clearForm';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import Trans from 'next-translate/Trans';
import dynamic from 'next/dynamic';
import React, { useCallback } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { ContactFormType } from 'types/form';
import { GtmMessageOriginType } from 'types/gtm/enums';

const ErrorPopup = dynamic(() => import('components/Forms/Lib/ErrorPopup').then((component) => component.ErrorPopup));

export const ContactContent: FC = () => {
    const t = useTypedTranslationFunction();
    const [formProviderMethods, defaultValues] = useContactForm();
    const formMeta = useContactFormMeta(formProviderMethods);
    const [{ data }] = useSettingsQueryApi({ requestPolicy: 'cache-only' });
    const [{ data: privacyPolicyArticleUrlData }] = usePrivacyPolicyArticleUrlQueryApi();
    const privacyPolicyArticleUrl = privacyPolicyArticleUrlData?.privacyPolicyArticle?.slug;
    const [isErrorPopupVisible, setErrorPopupVisibility] = useErrorPopupVisibility(formProviderMethods);
    const [, contact] = useContactMutationApi();

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
                    <Heading type="h1">{t('Write to us')}</Heading>
                    {data?.settings?.contactFormMainText !== undefined && (
                        <div className="mb-4" dangerouslySetInnerHTML={{ __html: data.settings.contactFormMainText }} />
                    )}
                    <FormProvider {...formProviderMethods}>
                        <Form onSubmit={formProviderMethods.handleSubmit(onSubmitHandler)}>
                            <TextInputControlled
                                control={formProviderMethods.control}
                                name={formMeta.fields.name.name}
                                render={(textInput) => (
                                    <FormColumn className="lg:w-[calc(65%+0.75rem)]">
                                        <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                                            {textInput}
                                        </FormLine>
                                    </FormColumn>
                                )}
                                formName={formMeta.formName}
                                textInputProps={{
                                    label: formMeta.fields.name.label,
                                    required: true,
                                    type: 'text',
                                    autoComplete: 'name',
                                }}
                            />
                            <TextInputControlled
                                control={formProviderMethods.control}
                                name={formMeta.fields.email.name}
                                render={(textInput) => (
                                    <FormColumn className="lg:w-[calc(65%+0.75rem)]">
                                        <FormLine bottomGap className="w-full flex-none lg:w-1/2">
                                            {textInput}
                                        </FormLine>
                                    </FormColumn>
                                )}
                                formName={formMeta.formName}
                                textInputProps={{
                                    label: formMeta.fields.email.label,
                                    required: true,
                                    type: 'email',
                                    autoComplete: 'email',
                                }}
                            />
                            <TextareaControlled
                                name={formMeta.fields.message.name}
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
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
                                    i18nKey="ContactFormInfo"
                                    defaultTrans="By clicking on the Send message button, you agree with the <lnk1>processing of privacy policy</lnk1>."
                                    components={{
                                        lnk1:
                                            privacyPolicyArticleUrl !== undefined ? (
                                                <Link href={privacyPolicyArticleUrl} isExternal target="_blank" />
                                            ) : (
                                                <span></span>
                                            ),
                                    }}
                                />
                            </div>
                            <Button
                                type="submit"
                                isRounder
                                variant="primary"
                                isWithDisabledLook={!formProviderMethods.formState.isValid}
                            >
                                {t('Send message')}
                            </Button>
                        </Form>
                    </FormProvider>
                </Webline>
            </div>
            {isErrorPopupVisible && (
                <ErrorPopup
                    onCloseCallback={() => setErrorPopupVisibility(false)}
                    fields={formMeta.fields}
                    gtmMessageOrigin={GtmMessageOriginType.other}
                />
            )}
        </>
    );
};
