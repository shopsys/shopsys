import { usePersonalDataOverviewForm, usePersonalDataOverviewFormMeta } from './formMeta';
import { ButtonWrapperStyled, ContentTextStyled } from './PersonalDataOverviewContent.style';
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
import { PersonalDataOverviewFormType } from 'types/form';

type PersonalDataOverviewContentProps = {
    breadcrumbs: BreadcrumbItemType[];
    contentSiteText: string | undefined;
};

export const PersonalDataOverviewContent: FC<PersonalDataOverviewContentProps> = ({ breadcrumbs, contentSiteText }) => {
    const t = useTypedTranslationFunction();
    const [, personalDataOverview] = usePersonalDataRequestMutationApi();
    const [formProviderMethods] = usePersonalDataOverviewForm();
    const formMeta = usePersonalDataOverviewFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useErrorPopupVisibility(formProviderMethods);

    const onPersonalDataOverviewHandler = useCallback<SubmitHandler<PersonalDataOverviewFormType>>(
        async (data) => {
            blurInput();
            const personalDataOverviewResult = await personalDataOverview({
                email: data.email,
                type: PersonalDataAccessRequestTypeEnumApi.DisplayApi,
            });

            if (personalDataOverviewResult.data?.RequestPersonalDataAccess !== undefined) {
                showSuccessMessage(formMeta.messages.success);
            }

            handleFormErrors(
                personalDataOverviewResult.error,
                formProviderMethods,
                'other',
                t,
                formMeta.messages.error,
            );
            clearForm(personalDataOverviewResult.error, formProviderMethods, { email: '' });
        },
        [personalDataOverview, formMeta.messages, t, formProviderMethods],
    );

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
