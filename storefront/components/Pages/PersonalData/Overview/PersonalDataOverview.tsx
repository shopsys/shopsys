import { ButtonWrapperStyled, ContentTextStyled } from './PersonalDataOverview.style';
import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import { FC, useEffect } from 'react';
import {
    PersonalDataAccessRequestTypeEnumApi,
    usePersonalDataPageTextQueryApi,
    usePersonalDataRequestMutationApi,
} from 'graphql/generated';
import { usePersonalDataOverviewForm, usePersonalDataOverviewFormMeta } from './formMeta';
import Button from 'components/Forms/Button';
import ErrorPopup from 'components/Forms/Lib/ErrorPopup';
import Form from 'components/Forms/Form';
import FormLine from 'components/Forms/Lib/FormLine';
import FormLineError from 'components/Forms/Lib/FormLineError';
import { PersonalDataOverviewFormType } from 'types/form';
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

const PersonalDataOverview: FC = () => {
    const t = useTypedTranslationFunction();
    const [personalDataPageTextResult] = usePersonalDataPageTextQueryApi();
    const [personalDataOverviewResult, personalDataOverview] = usePersonalDataRequestMutationApi();
    const { url } = useShopsysSelector((state) => state.domain);
    const [personalDataOverviewUrl] = useGetInternationalizedStaticUrls(['/personal-data-overview'], url);
    const [formProviderMethods] = usePersonalDataOverviewForm();
    const formMeta = usePersonalDataOverviewFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);

    useHandleFormErrors(personalDataOverviewResult.error, formProviderMethods, formMeta.messages.error);
    useHandleFormSuccessfulSubmit(personalDataOverviewResult, formProviderMethods, { email: '' }, undefined, {
        blur: true,
        reset: true,
    });

    const personalDataMutationType = 'display' as PersonalDataAccessRequestTypeEnumApi;

    const onPersonalDataOverviewHandler: SubmitHandler<PersonalDataOverviewFormType> = async (data, event) => {
        event?.preventDefault();
        await personalDataOverview({ email: data.email, type: personalDataMutationType });
    };

    useEffect(() => {
        if (personalDataOverviewResult.data?.RequestPersonalDataAccess !== undefined) {
            showSuccessMessage(formMeta.messages.success);
        }
    }, [personalDataOverviewResult]);

    const contentSiteText: string | undefined =
        personalDataPageTextResult.data?.personalDataPage !== undefined &&
        personalDataPageTextResult.data.personalDataPage !== null
            ? personalDataPageTextResult.data.personalDataPage.displaySiteContent
            : undefined;

    return (
        <>
            <SimpleLayout
                heading={t('Personal Data Overview')}
                breadcrumb={[{ name: t('Personal Data Overview'), slug: personalDataOverviewUrl }]}
            >
                {contentSiteText !== undefined && (
                    <ContentTextStyled>
                        <UserText htmlContent={contentSiteText} />
                    </ContentTextStyled>
                )}
                <FormProvider {...formProviderMethods}>
                    <Form onSubmit={formProviderMethods.handleSubmit(onPersonalDataOverviewHandler)} noValidate>
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

export default PersonalDataOverview;
