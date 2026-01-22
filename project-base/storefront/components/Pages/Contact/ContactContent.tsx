import { useContactForm, useContactFormMeta } from './contactFormMeta';
import { MailIcon } from 'components/Basic/Icon/MailIcon';
import { MailSecondaryIcon } from 'components/Basic/Icon/MailSecondaryIcon';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { TextareaControlled } from 'components/Forms/Textarea/TextareaControlled';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { useContactFormMutation } from 'graphql/requests/contact/mutations/ContactFormMutation.generated';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import React, { useState } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { ContactFormType } from 'types/form';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import { clearForm } from 'utils/forms/clearForm';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const ContactContent: FC = () => {
    const { t } = useTranslation();
    const [isSuccess, setIsSuccess] = useState(false);
    const [formProviderMethods, defaultValues] = useContactForm();
    const formMeta = useContactFormMeta(formProviderMethods);
    const [{ data: settingsData }] = useSettingsQuery({ requestPolicy: 'cache-only' });
    const [, contactForm] = useContactFormMutation();
    const handleError = useErrorHandler({
        form: formProviderMethods,
        customMessage: formMeta.messages.error,
    });

    const onSubmitHandler: SubmitHandler<ContactFormType> = async (values) => {
        const { name, email, message } = values;
        const contactFormResult = await contactForm({
            input: {
                name,
                email,
                message,
            },
        });

        if (contactFormResult.data?.ContactForm !== undefined) {
            setIsSuccess(true);
        }

        handleError(contactFormResult.error);
        clearForm(contactFormResult.error, formProviderMethods, defaultValues);
    };

    return (
        <Webline className="mt-8" width="lg">
            <VerticalStack gap="sm">
                {isSuccess && (
                    <PageHero
                        actionHref="/"
                        actionSkeletonType="homepage"
                        actionTitle={t("Let's shop")}
                        icon={MailIcon}
                        title={formMeta.messages.success}
                    />
                )}

                {!isSuccess && (
                    <>
                        <PageHero
                            icon={MailSecondaryIcon}
                            title={t('Write to us')}
                            description={
                                settingsData?.settings?.contactFormMainText ? (
                                    <span
                                        dangerouslySetInnerHTML={{ __html: settingsData.settings.contactFormMainText }}
                                    />
                                ) : undefined
                            }
                        />

                        <FormProvider {...formProviderMethods}>
                            <Form onSubmit={formProviderMethods.handleSubmit(onSubmitHandler)}>
                                <FormContentWrapper>
                                    <FormBlockWrapper>
                                        <TextInputControlled
                                            control={formProviderMethods.control}
                                            formName={formMeta.formName}
                                            name={formMeta.fields.name.name}
                                            render={(textInput) => <FormLine>{textInput}</FormLine>}
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
                                            render={(textInput) => <FormLine>{textInput}</FormLine>}
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
                                            render={(textarea) => <FormLine>{textarea}</FormLine>}
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
                                    </FormBlockWrapper>

                                    <FormButtonWrapper>
                                        <SubmitButton
                                            aria-label={t('Submit form to send your message', { ns: 'accessibility' })}
                                            hasDisabledCursor={!formProviderMethods.formState.isValid}
                                        >
                                            {t('Send message')}
                                        </SubmitButton>
                                    </FormButtonWrapper>
                                </FormContentWrapper>
                            </Form>
                        </FormProvider>
                    </>
                )}
            </VerticalStack>
        </Webline>
    );
};
