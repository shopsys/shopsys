import { usePersonalDataOverviewForm, usePersonalDataOverviewFormMeta } from './formMeta';
import { Button } from 'components/Forms/Button/Button';
import { Form } from 'components/Forms/Form/Form';
import { ErrorPopup } from 'components/Forms/Lib/ErrorPopup';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { showSuccessMessage } from 'components/Helpers/toasts';
import { UserText } from 'components/Helpers/UserText/UserText';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import {
    BreadcrumbFragmentApi,
    PersonalDataAccessRequestTypeEnumApi,
    usePersonalDataRequestMutationApi,
} from 'graphql/generated';
import { blurInput } from 'helpers/forms/blurInput';
import { clearForm } from 'helpers/forms/clearForm';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCallback } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { PersonalDataOverviewFormType } from 'types/form';
import { GtmMessageOriginType } from 'types/gtm/enums';

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
            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                fields={formMeta.fields}
                gtmMessageOrigin={GtmMessageOriginType.other}
            />
        </>
    );
};
