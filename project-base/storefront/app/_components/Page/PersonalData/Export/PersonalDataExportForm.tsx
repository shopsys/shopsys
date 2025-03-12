'use client';

import { usePersonalDataExportForm, usePersonalDataExportFormMeta } from './personalDataExportFormMeta';
import { personalDataAction } from 'app/_actions/personalDataAction';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useTranslation } from 'components/providers/TranslationProvider';
import { TypePersonalDataAccessRequestTypeEnum } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { Translate } from 'next-translate';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { PersonalDataExportFormType } from 'types/form';
import { blurInput } from 'utils/forms/blurInput';
import { clearForm } from 'utils/forms/clearForm';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { useErrorPopup } from 'utils/forms/useErrorPopup';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

export const PersonalDataExportForm = () => {
    const { t } = useTranslation();
    const [formProviderMethods] = usePersonalDataExportForm();
    const formMeta = usePersonalDataExportFormMeta(formProviderMethods);

    useErrorPopup(formProviderMethods, formMeta.fields, undefined, GtmMessageOriginType.other);

    const onPersonalDataExportHandler: SubmitHandler<PersonalDataExportFormType> = async (
        personalDataExportFormData,
    ) => {
        blurInput();

        const personalDataExportResult = await personalDataAction({
            email: personalDataExportFormData.email,
            type: TypePersonalDataAccessRequestTypeEnum.Export,
        });

        if (personalDataExportResult.data) {
            showSuccessMessage(formMeta.messages.success);
        }

        handleFormErrors(personalDataExportResult.error, formProviderMethods, t as Translate, formMeta.messages.error);
        clearForm(personalDataExportResult.error, formProviderMethods, { email: '' });
    };

    return (
        <FormProvider {...formProviderMethods}>
            <Form
                className="flex w-full justify-center"
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
    );
};
