import { usePersonalDataOverviewForm, usePersonalDataOverviewFormMeta } from './formMeta';
import { ButtonWrapperStyled, ContentTextStyled } from './PersonalDataOverviewContent.style';
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
import { PersonalDataOverviewFormType } from 'types/form';

type PersonalDataOverviewContentProps = {
    breadcrumbs: BreadcrumbItemType[];
};

export const PersonalDataOverviewContent: FC<PersonalDataOverviewContentProps> = ({ breadcrumbs }) => {
    const t = useTypedTranslationFunction();
    const [personalDataPageTextResult] = usePersonalDataPageTextQueryApi();
    const [personalDataOverviewResult, personalDataOverview] = usePersonalDataRequestMutationApi();
    const [formProviderMethods] = usePersonalDataOverviewForm();
    const formMeta = usePersonalDataOverviewFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);

    useHandleFormErrors(personalDataOverviewResult.error, formProviderMethods, 'other', formMeta.messages.error);
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
    }, [formMeta.messages.success, personalDataOverviewResult]);

    const contentSiteText = personalDataPageTextResult.data?.personalDataPage?.displaySiteContent;

    return (
        <>
            <SimpleLayout heading={t('Personal Data Overview')} breadcrumb={breadcrumbs}>
                {contentSiteText !== undefined && (
                    <ContentTextStyled>
                        <UserText htmlContent={contentSiteText} />
                    </ContentTextStyled>
                )}
                <FormProvider {...formProviderMethods}>
                    <Form onSubmit={formProviderMethods.handleSubmit(onPersonalDataOverviewHandler)}>
                        <Controller
                            name={formMeta.fields.email.name}
                            render={({ fieldState: { isTouched, invalid, error }, field }) => (
                                <>
                                    <FormLine>
                                        <TextInput
                                            id={formMeta.formName + '-' + formMeta.fields.email.name}
                                            name={formMeta.fields.email.name}
                                            label={formMeta.fields.email.label}
                                            required
                                            type="text"
                                            isTouched={isTouched}
                                            hasError={invalid}
                                            fieldRef={field}
                                        />
                                        <FormLineError
                                            textInputSize="small"
                                            error={error}
                                            inputType="text-input"
                                            testIdentifier={
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
