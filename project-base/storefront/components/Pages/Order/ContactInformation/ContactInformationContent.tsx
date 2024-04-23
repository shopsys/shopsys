import { ContactInformationFormWrapper } from './ContactInformationFormWrapper';
import { useContactInformationFormMeta } from './contactInformationFormMeta';
import { Link } from 'components/Basic/Link/Link';
import { Button } from 'components/Forms/Button/Button';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import {
    useIsCustomerUserRegisteredQueryApi,
    usePrivacyPolicyArticleUrlQueryApi,
    useTermsAndConditionsArticleUrlQueryApi,
} from 'graphql/generated';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useEffect } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';

const LoginPopup = dynamic(
    () => import('components/Blocks/Login/LoginPopup').then((component) => component.LoginPopup),
    {
        ssr: false,
    },
);

export const ContactInformationContent: FC = () => {
    const { t } = useTranslation();
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const formProviderMethods = useFormContext<ContactInformation>();
    const isUserLoggedIn = useIsUserLoggedIn();
    const { formState } = formProviderMethods;
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const emailValue = useWatch({ name: formMeta.fields.email.name, control: formProviderMethods.control });
    const [{ data: termsAndConditionsArticleUrlData }] = useTermsAndConditionsArticleUrlQueryApi();
    const [{ data: privacyPolicyArticleUrlData }] = usePrivacyPolicyArticleUrlQueryApi();

    const termsAndConditionsArticleUrl = termsAndConditionsArticleUrlData?.termsAndConditionsArticle?.slug;
    const privacyPolicyArticleUrl = privacyPolicyArticleUrlData?.privacyPolicyArticle?.slug;
    const isEmailFilledCorrectly = !!emailValue && !formState.errors.email;

    const [{ data: isCustomerUserRegisteredData }] = useIsCustomerUserRegisteredQueryApi({
        variables: {
            email: emailValue,
        },
        pause: !isEmailFilledCorrectly,
    });

    const openLoginPopup = () => {
        updatePortalContent(<LoginPopup shouldOverwriteCustomerUserCart defaultEmail={emailValue} />);
    };

    useEffect(() => {
        if (isUserLoggedIn) {
            updatePortalContent(null);
        }
    }, [isUserLoggedIn]);

    return (
        <>
            <TextInputControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.email.name}
                render={(textInput) => (
                    <FormLine bottomGap className="flex-none lg:w-[65%]">
                        {textInput}
                    </FormLine>
                )}
                textInputProps={{
                    label: formMeta.fields.email.label,
                    required: true,
                    type: 'email',
                    autoComplete: 'email',
                    onChange: () => updateContactInformation({ email: emailValue }),
                }}
            />

            {isCustomerUserRegisteredData?.isCustomerUserRegistered && !isUserLoggedIn && (
                <Button className="mb-5" size="small" type="button" onClick={openLoginPopup}>
                    {t('User with this email is already registered. Do you want to sign in')}
                </Button>
            )}

            {isEmailFilledCorrectly && <ContactInformationFormWrapper />}

            <div className={twJoin(!isEmailFilledCorrectly && 'pointer-events-none opacity-50')}>
                <p className="mb-4">
                    <Trans
                        defaultTrans="By clicking on the Send order button, you agree with <lnk1>terms and conditions</lnk1> of the e-shop and with the <lnk2>processing of privacy policy</lnk2>."
                        i18nKey="ContactInformationInfo"
                        components={{
                            lnk1:
                                termsAndConditionsArticleUrl !== undefined ? (
                                    <Link isExternal href={termsAndConditionsArticleUrl} target="_blank" />
                                ) : (
                                    <span />
                                ),
                            lnk2:
                                privacyPolicyArticleUrl !== undefined ? (
                                    <Link isExternal href={privacyPolicyArticleUrl} target="_blank" />
                                ) : (
                                    <span />
                                ),
                        }}
                    />
                </p>

                <CheckboxControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.newsletterSubscription.name}
                    render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                    checkboxProps={{
                        label: formMeta.fields.newsletterSubscription.label,
                    }}
                />
            </div>
        </>
    );
};
