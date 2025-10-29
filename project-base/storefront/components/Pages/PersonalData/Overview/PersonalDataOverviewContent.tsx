import { usePersonalDataOverviewForm, usePersonalDataOverviewFormMeta } from './personalDataOverviewFormMeta';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { usePersonalDataRequestMutation } from 'graphql/requests/personalData/mutations/PersonalDataRequestMutation.generated';
import { TypePersonalDataAccessRequestTypeEnum } from 'graphql/types';
import { useCallback } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { PersonalDataOverviewFormType } from 'types/form';
import { blurInput } from 'utils/forms/blurInput';
import { clearForm } from 'utils/forms/clearForm';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type PersonalDataOverviewContentProps = {
    contentSiteText: string | null | undefined;
};

export const PersonalDataOverviewContent: FC<PersonalDataOverviewContentProps> = ({ contentSiteText }) => {
    const { t } = useTranslation();
    const [, personalDataOverview] = usePersonalDataRequestMutation();
    const [formProviderMethods] = usePersonalDataOverviewForm();
    const formMeta = usePersonalDataOverviewFormMeta(formProviderMethods);

    const onPersonalDataOverviewHandler = useCallback<SubmitHandler<PersonalDataOverviewFormType>>(
        async (personalDataOverviewFormData) => {
            blurInput();
            const personalDataOverviewResult = await personalDataOverview({
                email: personalDataOverviewFormData.email,
                type: TypePersonalDataAccessRequestTypeEnum.Display,
            });

            if (personalDataOverviewResult.data?.RequestPersonalDataAccess !== undefined) {
                showSuccessMessage(formMeta.messages.success);
            }

            handleFormErrors(personalDataOverviewResult.error, formProviderMethods, t, formMeta.messages.error);
            clearForm(personalDataOverviewResult.error, formProviderMethods, { email: '' });
        },
        [personalDataOverview, formMeta.messages, t, formProviderMethods],
    );

    return (
        <Webline width="lg">
            <VerticalStack gap="sm">
                <PageHero
                    icon={UserIcon}
                    title={t('Personal data overview')}
                    description={
                        contentSiteText ? <span dangerouslySetInnerHTML={{ __html: contentSiteText }} /> : undefined
                    }
                />

                <FormProvider {...formProviderMethods}>
                    <Form
                        className="flex w-full justify-center"
                        onSubmit={formProviderMethods.handleSubmit(onPersonalDataOverviewHandler)}
                    >
                        <FormContentWrapper>
                            <FormBlockWrapper>
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
            </VerticalStack>
        </Webline>
    );
};
