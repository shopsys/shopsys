import { usePersonalDataOverviewForm, usePersonalDataOverviewFormMeta } from './personalDataOverviewFormMeta';
import { UserText } from 'components/Basic/UserText/UserText';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { Webline } from 'components/Layout/Webline/Webline';
import { usePersonalDataRequestMutation } from 'graphql/requests/personalData/mutations/PersonalDataRequestMutation.generated';
import { TypePersonalDataAccessRequestTypeEnum } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import { useCallback } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { PersonalDataOverviewFormType } from 'types/form';
import { blurInput } from 'utils/forms/blurInput';
import { clearForm } from 'utils/forms/clearForm';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { useErrorPopup } from 'utils/forms/useErrorPopup';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type PersonalDataOverviewContentProps = {
    contentSiteText: string | undefined;
};

export const PersonalDataOverviewContent: FC<PersonalDataOverviewContentProps> = ({ contentSiteText }) => {
    const { t } = useTranslation();
    const [, personalDataOverview] = usePersonalDataRequestMutation();
    const [formProviderMethods] = usePersonalDataOverviewForm();
    const formMeta = usePersonalDataOverviewFormMeta(formProviderMethods);

    useErrorPopup(formProviderMethods, formMeta.fields, undefined, GtmMessageOriginType.other);

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
        <Webline className="flex flex-col items-center">
            <h1 className="w-full max-w-3xl">{t('Personal data overview')}</h1>
            {contentSiteText && (
                <div className="max-w-3xl [&_section]:mb-5 [&_section]:block [&_section]:text-justify ">
                    <UserText htmlContent={contentSiteText} />
                </div>
            )}
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
                            <FormButtonWrapper>
                                <SubmitButton>{t('Send')}</SubmitButton>
                            </FormButtonWrapper>
                        </FormBlockWrapper>
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </Webline>
    );
};
