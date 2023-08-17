import { usePersonalDataOverviewForm, usePersonalDataOverviewFormMeta } from './personalDataOverviewFormMeta';
import { Button } from 'components/Forms/Button/Button';
import { Form } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { UserText } from 'components/Basic/UserText/UserText';
import { showSuccessMessage } from 'helpers/toasts';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { blurInput } from 'helpers/forms/blurInput';
import { clearForm } from 'helpers/forms/clearForm';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import dynamic from 'next/dynamic';
import { useCallback } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { PersonalDataOverviewFormType } from 'types/form';
import { GtmMessageOriginType } from 'gtm/types/enums';
import { BreadcrumbFragmentApi } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { usePersonalDataRequestMutationApi } from 'graphql/requests/personalData/mutations/PersonalDataRequestMutation.generated';
import { PersonalDataAccessRequestTypeEnumApi } from 'graphql/requests/types';

const ErrorPopup = dynamic(() => import('components/Forms/Lib/ErrorPopup').then((component) => component.ErrorPopup));

type PersonalDataOverviewContentProps = {
    breadcrumbs: BreadcrumbFragmentApi[];
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

            handleFormErrors(personalDataOverviewResult.error, formProviderMethods, t, formMeta.messages.error);
            clearForm(personalDataOverviewResult.error, formProviderMethods, { email: '' });
        },
        [personalDataOverview, formMeta.messages, t, formProviderMethods],
    );

    return (
        <>
            <SimpleLayout heading={t('Personal Data Overview')} breadcrumb={breadcrumbs}>
                {contentSiteText !== undefined && (
                    <div className="[&_section]:mb-5 [&_section]:block [&_section]:text-justify ">
                        <UserText htmlContent={contentSiteText} />
                    </div>
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
                                type: 'email',
                                autoComplete: 'email',
                            }}
                        />
                        <div className="mt-8 flex w-full justify-center">
                            <Button type="submit">{t('Send')}</Button>
                        </div>
                    </Form>
                </FormProvider>
            </SimpleLayout>
            {isErrorPopupVisible && (
                <ErrorPopup
                    onCloseCallback={() => setErrorPopupVisibility(false)}
                    fields={formMeta.fields}
                    gtmMessageOrigin={GtmMessageOriginType.other}
                />
            )}
        </>
    );
};
