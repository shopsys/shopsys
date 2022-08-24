import { usePersonalDataExportForm, usePersonalDataExportFormMeta } from './formMeta';
import { ButtonWrapperStyled, ContentTextStyled } from './PersonalDataExportContent.style';
import { Button } from 'components/Forms/Button/Button';
import { Form } from 'components/Forms/Form/Form';
import { ErrorPopup } from 'components/Forms/Lib/ErrorPopup/ErrorPopup';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError/FormLineError';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { showSuccessMessage } from 'components/Helpers/Toasts';
import { UserText } from 'components/Helpers/UserText/UserText';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import {
    PersonalDataAccessRequestTypeEnumApi,
    usePersonalDataPageTextQueryApi,
    usePersonalDataRequestMutationApi,
} from 'graphql/generated';
import { useHandleErrorPopupVisibility } from 'hooks/forms/UseHandleErrorPopupVisibility';
import { useHandleFormErrors } from 'hooks/forms/UseHandleFormErrors';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/UseHandleFormSuccessfulSubmit';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, useEffect } from 'react';
import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { PersonalDataExportFormType } from 'types/form';

type PersonalDataExportContentProps = {
    breadcrumbs: BreadcrumbItemType[];
};

export const PersonalDataExportContent: FC<PersonalDataExportContentProps> = ({ breadcrumbs }) => {
    const t = useTypedTranslationFunction();
    const [personalDataPageTextResult] = usePersonalDataPageTextQueryApi();
    const [personalDataExportResult, personalDataExport] = usePersonalDataRequestMutationApi();
    const [formProviderMethods] = usePersonalDataExportForm();
    const formMeta = usePersonalDataExportFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);

    useHandleFormErrors(personalDataExportResult.error, formProviderMethods, 'other', formMeta.messages.error);
    useHandleFormSuccessfulSubmit(personalDataExportResult, formProviderMethods, { email: '' }, undefined, {
        blur: true,
        reset: true,
    });

    const personalDataMutationType = 'export' as PersonalDataAccessRequestTypeEnumApi;

    const onPersonalDataExportHandler: SubmitHandler<PersonalDataExportFormType> = async (data, event) => {
        event?.preventDefault();
        await personalDataExport({ email: data.email, type: personalDataMutationType });
    };

    useEffect(() => {
        if (personalDataExportResult.data?.RequestPersonalDataAccess !== undefined) {
            showSuccessMessage(formMeta.messages.success);
        }
    }, [formMeta.messages.success, personalDataExportResult]);

    const contentSiteText = personalDataPageTextResult.data?.personalDataPage?.exportSiteContent;

    return (
        <>
            <SimpleLayout heading={t('Personal Data Export')} breadcrumb={breadcrumbs}>
                {contentSiteText !== undefined && (
                    <ContentTextStyled>
                        <UserText htmlContent={contentSiteText} />
                    </ContentTextStyled>
                )}
                <FormProvider {...formProviderMethods}>
                    <Form onSubmit={formProviderMethods.handleSubmit(onPersonalDataExportHandler)} noValidate>
                        <Controller
                            name={formMeta.fields.email.name}
                            render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                <>
                                    <FormLine>
                                        <TextInput
                                            id={formMeta.formName + '-' + formMeta.fields.email.name}
                                            name={formMeta.fields.email.name}
                                            label={formMeta.fields.email.label}
                                            required={true}
                                            type="text"
                                            isTouched={isTouched}
                                            hasError={invalid}
                                            fieldRef={field}
                                        />
                                        <FormLineError
                                            textInputSize="small"
                                            error={error}
                                            inputType="text-input"
                                            data-testid={
                                                formMeta.formName + '-' + formMeta.fields.email.name + '-error'
                                            }
                                        />
                                    </FormLine>
                                </>
                            )}
                        />
                        <ButtonWrapperStyled>
                            <Button type="submit">{t('Send')}</Button>
                        </ButtonWrapperStyled>
                    </Form>
                </FormProvider>
            </SimpleLayout>
            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                fields={formMeta.fields}
                origin="other"
            />
        </>
    );
};
