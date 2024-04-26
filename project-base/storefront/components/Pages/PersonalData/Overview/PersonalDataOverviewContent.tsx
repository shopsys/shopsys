import { usePersonalDataOverviewForm, usePersonalDataOverviewFormMeta } from './personalDataOverviewFormMeta';
import { UserText } from 'components/Basic/UserText/UserText';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
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
        async (data) => {
            blurInput();
            const personalDataOverviewResult = await personalDataOverview({
                email: data.email,
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
        <SimpleLayout heading={t('Personal Data Overview')}>
            {contentSiteText !== undefined && (
                <div className="[&_section]:mb-5 [&_section]:block [&_section]:text-justify ">
                    <UserText htmlContent={contentSiteText} />
                </div>
            )}
            <FormProvider {...formProviderMethods}>
                <Form onSubmit={formProviderMethods.handleSubmit(onPersonalDataOverviewHandler)}>
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
                    <div className="mt-8 flex w-full justify-center">
                        <SubmitButton>{t('Send')}</SubmitButton>
                    </div>
                </Form>
            </FormProvider>
        </SimpleLayout>
    );
};
