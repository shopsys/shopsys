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
import { PersonalDataOverviewFormType } from 'types/form';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import { blurInput } from 'utils/forms/blurInput';
import { clearForm } from 'utils/forms/clearForm';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { usePersonalDataOverviewForm, usePersonalDataOverviewFormMeta } from './personalDataOverviewFormMeta';

type PersonalDataOverviewContentProps = {
    contentSiteText: string | null | undefined;
};

export const PersonalDataOverviewContent: FC<PersonalDataOverviewContentProps> = ({ contentSiteText }) => {
    const { t } = useTranslation();
    const [isSuccess, setIsSuccess] = useState(false);
    const [, personalDataOverview] = usePersonalDataRequestMutation();
    const [formProviderMethods] = usePersonalDataOverviewForm();
    const formMeta = usePersonalDataOverviewFormMeta();
    const handleError = useErrorHandler({
        form: formProviderMethods,
        customMessage: formMeta.messages.error,
    });

    const onPersonalDataOverviewHandler: SubmitHandler<PersonalDataOverviewFormType> = async (
        personalDataOverviewFormData,
    ) => {
        blurInput();
        const personalDataOverviewResult = await personalDataOverview({
            email: personalDataOverviewFormData.email,
            type: TypePersonalDataAccessRequestTypeEnum.Display,
        });

        if (personalDataOverviewResult.data?.RequestPersonalDataAccess !== undefined) {
            setIsSuccess(true);
        }

        handleError(personalDataOverviewResult.error);

        clearForm(personalDataOverviewResult.error, formProviderMethods, { email: '' });
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
                            title={t('Personal data overview')}
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
                                onSubmit={formProviderMethods.handleSubmit(onPersonalDataOverviewHandler)}
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
                                            aria-label={t('Submit form to send your personal data overview request', {
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
