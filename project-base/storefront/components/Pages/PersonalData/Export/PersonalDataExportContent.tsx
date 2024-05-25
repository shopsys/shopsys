import { usePersonalDataExportForm, usePersonalDataExportFormMeta } from './personalDataExportFormMeta';
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
import { PersonalDataExportFormType } from 'types/form';
import { blurInput } from 'utils/forms/blurInput';
import { clearForm } from 'utils/forms/clearForm';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { useErrorPopup } from 'utils/forms/useErrorPopup';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type PersonalDataExportContentProps = {
    contentSiteText: string | undefined;
};

export const PersonalDataExportContent: FC<PersonalDataExportContentProps> = ({ contentSiteText }) => {
    const { t } = useTranslation();
    const [, personalDataExport] = usePersonalDataRequestMutation();
    const [formProviderMethods] = usePersonalDataExportForm();
    const formMeta = usePersonalDataExportFormMeta(formProviderMethods);

    useErrorPopup(formProviderMethods, formMeta.fields, undefined, GtmMessageOriginType.other);

    const onPersonalDataExportHandler = useCallback<SubmitHandler<PersonalDataExportFormType>>(
        async (personalDataExportFormData) => {
            blurInput();
            const personalDataExportResult = await personalDataExport({
                email: personalDataExportFormData.email,
                type: TypePersonalDataAccessRequestTypeEnum.Export,
            });

            if (personalDataExportResult.data?.RequestPersonalDataAccess) {
                showSuccessMessage(formMeta.messages.success);
            }

            handleFormErrors(personalDataExportResult.error, formProviderMethods, t, formMeta.messages.error);
            clearForm(personalDataExportResult.error, formProviderMethods, { email: '' });
        },
        [personalDataExport, formMeta.messages, formProviderMethods, t],
    );

    return (
        <Webline className="flex flex-col items-center">
            <h1 className="max-w-3xl w-full">{t('Personal data export')}</h1>
            {!!contentSiteText && (
                <div className="mb-5 max-w-3xl block text-justify">
                    <UserText htmlContent={contentSiteText} />
                </div>
            )}
            <FormProvider {...formProviderMethods}>
                <Form
                    className="w-full flex justify-center"
                    onSubmit={formProviderMethods.handleSubmit(onPersonalDataExportHandler)}
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
