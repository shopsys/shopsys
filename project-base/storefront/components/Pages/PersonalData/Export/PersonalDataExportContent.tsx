import { MailIcon } from 'components/Basic/Icon/MailIcon';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { usePersonalDataRequestMutation } from 'graphql/requests/personalData/mutations/PersonalDataRequestMutation.generated';
import { TypePersonalDataAccessRequestTypeEnum } from 'graphql/types';
import { useState } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { PersonalDataExportFormType } from 'types/form';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import { blurInput } from 'utils/forms/blurInput';
import { clearForm } from 'utils/forms/clearForm';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { usePersonalDataExportForm, usePersonalDataExportFormMeta } from './personalDataExportFormMeta';

type PersonalDataExportContentProps = {
    contentSiteText: string | null | undefined;
};

export const PersonalDataExportContent: FC<PersonalDataExportContentProps> = ({ contentSiteText }) => {
    const { t } = useTranslation();
    const [isSuccess, setIsSuccess] = useState(false);
    const [, personalDataExport] = usePersonalDataRequestMutation();
    const [formProviderMethods] = usePersonalDataExportForm();
    const formMeta = usePersonalDataExportFormMeta();
    const handleError = useErrorHandler({
        form: formProviderMethods,
        customMessage: formMeta.messages.error,
    });

    const onPersonalDataExportHandler: SubmitHandler<PersonalDataExportFormType> = async (
        personalDataExportFormData,
    ) => {
        blurInput();
        const personalDataExportResult = await personalDataExport({
            email: personalDataExportFormData.email,
            type: TypePersonalDataAccessRequestTypeEnum.Export,
        });

        if (personalDataExportResult.data?.RequestPersonalDataAccess) {
            setIsSuccess(true);
        }

        handleError(personalDataExportResult.error);

        clearForm(personalDataExportResult.error, formProviderMethods, { email: '' });
    };

    return (
        <Webline width="lg">
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
                            icon={UserIcon}
                            title={t('Personal data export')}
                            description={
                                contentSiteText ? (
                                    <span dangerouslySetInnerHTML={{ __html: contentSiteText }} />
                                ) : undefined
                            }
                        />

                        <FormProvider {...formProviderMethods}>
                            <Form
                                className="flex w-full justify-center"
                                formName={formMeta.formName}
                                onSubmit={formProviderMethods.handleSubmit(onPersonalDataExportHandler)}
                            >
                                <FormContentWrapper>
                                    <FormBlockWrapper>
                                        <TextInputControlled
                                            control={formProviderMethods.control}
                                            formName={formMeta.formName}
                                            name={formMeta.fields.email.name}
                                            textInputProps={{
                                                label: formMeta.fields.email.label,
                                                required: true,
                                                type: 'email',
                                                autoComplete: 'email',
                                            }}
                                        />
                                    </FormBlockWrapper>

                                    <FormButtonWrapper>
                                        <SubmitButton
                                            aria-label={t('Submit form to send your personal data export request', {
                                                ns: 'accessibility',
                                            })}
                                        >
                                            {t('Send')}
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
