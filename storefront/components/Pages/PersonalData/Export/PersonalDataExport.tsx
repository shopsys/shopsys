import { ButtonWrapperStyled, ContentTextStyled } from './PersonalDataExport.style';
import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import { FC, useEffect } from 'react';
import {
    PersonalDataAccessRequestTypeEnumApi,
    usePersonalDataPageTextQueryApi,
    usePersonalDataRequestMutationApi,
} from 'graphql/generated';
import { usePersonalDataExportForm, usePersonalDataExportFormMeta } from './formMeta';
import Button from 'components/Forms/Button';
import ErrorPopup from 'components/Forms/Lib/ErrorPopup';
import Form from 'components/Forms/Form';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import { PersonalDataExportFormType } from 'types/form';
import { showSuccessMessage } from 'components/Helpers/Toasts';
import SimpleLayout from 'components/Layout/SimpleLayout';
import TextInput from 'components/Forms/TextInput';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useHandleErrorPopupVisibility } from 'hooks/forms/UseHandleErrorPopupVisibility';
import { useHandleFormErrors } from 'hooks/forms/UseHandleFormErrors';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/UseHandleFormSuccessfulSubmit';
import UserText from 'components/Helpers/UserText';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const PersonalDataExport: FC = () => {
    const t = useTypedTranslationFunction();
    const [personalDataPageTextResult] = usePersonalDataPageTextQueryApi();
    const [personalDataExportResult, personalDataExport] = usePersonalDataRequestMutationApi();
    const { url } = useShopsysSelector((state) => state.domain);
    const [personalDataExportUrl] = useGetInternationalizedStaticUrls(['/personal-data-export'], url);
    const [formProviderMethods] = usePersonalDataExportForm();
    const formMeta = usePersonalDataExportFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);

    useHandleFormErrors(personalDataExportResult.error, formProviderMethods, formMeta.messages.error);
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
    }, [personalDataExportResult]);

    const contentSiteText: string | undefined =
        personalDataPageTextResult.data?.personalDataPage !== undefined &&
        personalDataPageTextResult.data.personalDataPage !== null
            ? personalDataPageTextResult.data.personalDataPage.exportSiteContent
            : undefined;

    return (
        <>
            <SimpleLayout
                heading={t('Personal Data Export')}
                breadcrumb={[{ name: t('Personal Data Export'), slug: personalDataExportUrl }]}
            >
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
                                        <FormLineError textInputSize="small" error={error} inputType="text-input" />
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
            />
        </>
    );
};

export default PersonalDataExport;
