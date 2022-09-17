import { usePersonalDataExportForm, usePersonalDataExportFormMeta } from './formMeta';
import { ButtonWrapperStyled, ContentTextStyled } from './PersonalDataExportContent.style';
import { Button } from 'components/Forms/Button/Button';
import { Form } from 'components/Forms/Form/Form';
import { ErrorPopup } from 'components/Forms/Lib/ErrorPopup/ErrorPopup';
import { FormLine } from 'components/Forms/Lib/FormLine/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { showSuccessMessage } from 'components/Helpers/Toasts';
import { UserText } from 'components/Helpers/UserText/UserText';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { PersonalDataAccessRequestTypeEnumApi, usePersonalDataRequestMutationApi } from 'graphql/generated';
import { blurInput } from 'helpers/forms/blurInput';
import { clearForm } from 'helpers/forms/clearForm';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useCallback } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { PersonalDataExportFormType } from 'types/form';

type PersonalDataExportContentProps = {
    breadcrumbs: BreadcrumbItemType[];
    contentSiteText: string | undefined;
};

export const PersonalDataExportContent: FC<PersonalDataExportContentProps> = ({ breadcrumbs, contentSiteText }) => {
    const t = useTypedTranslationFunction();
    const [, personalDataExport] = usePersonalDataRequestMutationApi();
    const [formProviderMethods] = usePersonalDataExportForm();
    const formMeta = usePersonalDataExportFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useErrorPopupVisibility(formProviderMethods);

    const onPersonalDataExportHandler = useCallback<SubmitHandler<PersonalDataExportFormType>>(
        async (data) => {
            blurInput();
            const personalDataExportResult = await personalDataExport({
                email: data.email,
                type: PersonalDataAccessRequestTypeEnumApi.ExportApi,
            });

            if (personalDataExportResult.data?.RequestPersonalDataAccess !== undefined) {
                showSuccessMessage(formMeta.messages.success);
            }

            handleFormErrors(personalDataExportResult.error, formProviderMethods, 'other', t, formMeta.messages.error);
            clearForm(personalDataExportResult.error, formProviderMethods, { email: '' });
        },
        [personalDataExport, formMeta.messages, formProviderMethods, t],
    );

    return (
        <>
            <SimpleLayout heading={t('Personal Data Export')} breadcrumb={breadcrumbs}>
                {contentSiteText !== undefined && (
                    <ContentTextStyled>
                        <UserText htmlContent={contentSiteText} />
                    </ContentTextStyled>
                )}
                <FormProvider {...formProviderMethods}>
                    <Form onSubmit={formProviderMethods.handleSubmit(onPersonalDataExportHandler)}>
                        <TextInputControlled
                            control={formProviderMethods.control}
                            name={formMeta.fields.email.name}
                            render={(textInput) => <FormLine>{textInput}</FormLine>}
                            formName={formMeta.formName}
                            textInputProps={{
                                label: formMeta.fields.email.label,
                                required: true,
                                type: 'text',
                            }}
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
