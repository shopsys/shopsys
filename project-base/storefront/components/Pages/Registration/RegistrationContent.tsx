import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { RegistrationFormType } from 'types/form';
import { useRegistration } from 'utils/auth/useRegistration';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import { blurInput } from 'utils/forms/blurInput';
import { clearForm } from 'utils/forms/clearForm';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { RegistrationAgreements } from './RegistrationAgreements';
import { RegistrationBillingAddress } from './RegistrationBillingAddress';
import { RegistrationPassword } from './RegistrationPassword';
import { RegistrationUser } from './RegistrationUser';
import { useRegistrationForm, useRegistrationFormMeta } from './registrationFormMeta';

export const RegistrationContent: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [loginUrl] = getInternationalizedStaticUrls(['/login'], url);
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const [formProviderMethods, defaultValues] = useRegistrationForm();
    const formMeta = useRegistrationFormMeta();
    const { register } = useRegistration();
    const handleError = useErrorHandler({
        form: formProviderMethods,
        customMessage: formMeta.messages.error,
    });

    const onRegistrationHandler: SubmitHandler<RegistrationFormType> = async (registrationFormData) => {
        blurInput();
        const registrationError = await register({
            ...registrationFormData,
            password: registrationFormData.password,
            cartUuid,
            country: registrationFormData.country.value,
            telephone: {
                prefix: registrationFormData.telephonePrefix,
                countryCode: registrationFormData.telephonePrefixCountryCode || '',
                number: registrationFormData.telephone,
            },
            companyCustomer: registrationFormData.customer === 'companyCustomer',
            billingAddressUuid: null,
        });

        handleError(registrationError);

        clearForm(registrationError, formProviderMethods, defaultValues);
    };

    return (
        <Webline width="lg">
            <VerticalStack gap="sm">
                <PageHero
                    description={
                        <>
                            {t('Already registered?')}{' '}
                            <ExtendedNextLink href={loginUrl} skeletonType="login">
                                {t('Log in')}
                            </ExtendedNextLink>
                        </>
                    }
                    icon={UserIcon}
                    title={t('Create your account')}
                />

                <FormProvider {...formProviderMethods}>
                    <Form
                        className="flex w-full max-w-3xl justify-center"
                        formName={formMeta.formName}
                        onSubmit={formProviderMethods.handleSubmit(onRegistrationHandler)}
                    >
                        <FormContentWrapper>
                            <RegistrationUser />

                            <RegistrationPassword />

                            <RegistrationBillingAddress />

                            <RegistrationAgreements />

                            <FormButtonWrapper>
                                <SubmitButton tid={TIDs.registration_submit_button}>{t('Sign up')}</SubmitButton>
                            </FormButtonWrapper>
                        </FormContentWrapper>
                    </Form>
                </FormProvider>
            </VerticalStack>
        </Webline>
    );
};
