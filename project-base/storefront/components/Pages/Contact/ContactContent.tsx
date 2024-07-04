import { useContactForm, useContactFormMeta } from './contactFormMeta';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { TextareaControlled } from 'components/Forms/Textarea/TextareaControlled';
import { Webline } from 'components/Layout/Webline/Webline';
import { useContactFormMutation } from 'graphql/requests/contact/mutations/ContactFormMutation.generated';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import React, { useCallback } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { ContactFormType } from 'types/form';
import { clearForm } from 'utils/forms/clearForm';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { useErrorPopup } from 'utils/forms/useErrorPopup';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

export const ContactContent: FC = () => {
    const { t } = useTranslation();
    const [formProviderMethods, defaultValues] = useContactForm();
    const formMeta = useContactFormMeta(formProviderMethods);
    const [{ data: settingsData }] = useSettingsQuery({ requestPolicy: 'cache-only' });
    const [, contactForm] = useContactFormMutation();

    useErrorPopup(formProviderMethods, formMeta.fields, undefined, GtmMessageOriginType.other);

    const onSubmitHandler = useCallback<SubmitHandler<ContactFormType>>(
        async (values) => {
            const { name, email, message } = values;
            const contactFormResult = await contactForm({
                input: {
                    name,
                    email,
                    message,
                },
            });

            if (contactFormResult.data?.ContactForm !== undefined) {
                showSuccessMessage(formMeta.messages.success);
            }

            handleFormErrors(contactFormResult.error, formProviderMethods, t, formMeta.messages.error);
            clearForm(contactFormResult.error, formProviderMethods, defaultValues);
        },
        [contactForm, formMeta.messages, formProviderMethods, t, defaultValues],
    );

    return (
        <div className="mb-8">
            <Webline>
                <h1>{t('Write to us')}</h1>
                {settingsData?.settings?.contactFormMainText !== undefined && (
                    <div
                        className="mb-4"
                        dangerouslySetInnerHTML={{ __html: settingsData.settings.contactFormMainText }}
                    />
                )}
                <FormProvider {...formProviderMethods}>
                    <Form onSubmit={formProviderMethods.handleSubmit(onSubmitHandler)}>
                        <FormContentWrapper>
                            <FormBlockWrapper>
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.name.name}
                                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
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
                                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
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
                                    render={(textarea) => <FormLine bottomGap>{textarea}</FormLine>}
                                    textareaProps={{
                                        label: formMeta.fields.message.label,
                                        required: true,
                                        rows: 4,
                                    }}
                                />
                                <CheckboxControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.privacyPolicy.name}
                                    render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                                    checkboxProps={{
                                        label: formMeta.fields.privacyPolicy.label,
                                        required: true,
                                    }}
                                />
                                <FormButtonWrapper>
                                    <SubmitButton isWithDisabledLook={!formProviderMethods.formState.isValid}>
                                        {t('Send message')}
                                    </SubmitButton>
                                </FormButtonWrapper>
                            </FormBlockWrapper>
                        </FormContentWrapper>
                    </Form>
                </FormProvider>
            </Webline>
        </div>
    );
};
