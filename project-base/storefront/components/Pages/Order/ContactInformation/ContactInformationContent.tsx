import { ContactInformationFormWrapper } from './ContactInformationFormWrapper';
import { useContactInformationFormMeta } from './formMeta';
import { Heading } from 'components/Basic/Heading/Heading';
import { Link } from 'components/Basic/Link/Link';
import { Login } from 'components/Blocks/Popup/Login/Login';
import { Button } from 'components/Forms/Button/Button';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { Popup } from 'components/Layout/Popup/Popup';
import {
    useIsCustomerUserRegisteredQueryApi,
    usePrivacyPolicyArticleUrlQueryApi,
    useTermsAndConditionsArticleUrlQueryApi,
} from 'graphql/generated';

import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import Trans from 'next-translate/Trans';
import { useEffect, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/zustand/slices/createContactInformationSlice';
import { usePersistStore } from 'store/zustand/usePersistStore';
import { twJoin } from 'tailwind-merge';

export const ContactInformationContent: FC = () => {
    const t = useTypedTranslationFunction();
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const formProviderMethods = useFormContext<ContactInformation>();
    const { trigger, formState } = formProviderMethods;
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const { isUserLoggedIn } = useCurrentUserData();
    const emailValue = useWatch({ name: formMeta.fields.email.name, control: formProviderMethods.control });
    const [isEmailFilledCorrectly, setIsEmailFilledCorrectly] = useState(false);
    const [isEmailAlreadyRegistered, setIsEmailAlreadyRegistered] = useState(false);
    const [isLoginPopupOpened, setIsLoginPopupOpened] = useState(false);
    const [{ data: termsAndConditionsArticleUrlData }] = useTermsAndConditionsArticleUrlQueryApi();
    const termsAndConditionsArticleUrl = termsAndConditionsArticleUrlData?.termsAndConditionsArticle?.slug;
    const [{ data: privacyPolicyArticleUrlData }] = usePrivacyPolicyArticleUrlQueryApi();
    const privacyPolicyArticleUrl = privacyPolicyArticleUrlData?.privacyPolicyArticle?.slug;
    const [{ data: isCustomerUserRegisteredData }] = useIsCustomerUserRegisteredQueryApi({
        variables: {
            email: emailValue,
        },
        pause: !isEmailFilledCorrectly,
    });

    const loginHandler = () => {
        setIsLoginPopupOpened(true);
    };

    useEffect(() => {
        if (isUserLoggedIn === true) {
            setIsLoginPopupOpened(false);
        }
    }, [isUserLoggedIn]);

    const onCloseLoginPopupHandler = () => {
        setIsLoginPopupOpened(false);
    };

    useEffect(() => {
        if (formState.touchedFields.email !== undefined) {
            setIsEmailFilledCorrectly(formState.errors.email === undefined);
            return;
        }

        if (emailValue.length > 0) {
            trigger('email', { shouldFocus: true }).then((isEmailValid) => {
                setIsEmailFilledCorrectly(isEmailValid);
            });
        }
    }, [emailValue, trigger, formState.touchedFields, formState.errors]);

    useEffect(() => {
        setIsEmailAlreadyRegistered(!!isCustomerUserRegisteredData?.isCustomerUserRegistered);
    }, [isCustomerUserRegisteredData?.isCustomerUserRegistered]);

    return (
        <>
            <TextInputControlled
                control={formProviderMethods.control}
                name={formMeta.fields.email.name}
                render={(textInput) => (
                    <FormLine bottomGap className="flex-none lg:w-[65%]">
                        {textInput}
                    </FormLine>
                )}
                formName={formMeta.formName}
                textInputProps={{
                    label: formMeta.fields.email.label,
                    required: true,
                    type: 'email',
                    autoComplete: 'email',
                    onBlur: () => updateContactInformation({ email: emailValue }),
                }}
            />
            {isEmailAlreadyRegistered && !isUserLoggedIn && (
                <Button size="small" type="button" onClick={loginHandler} className="mb-5">
                    {t('User with this email is already registered. Do you want to sign in')}
                </Button>
            )}
            {isEmailFilledCorrectly && <ContactInformationFormWrapper />}
            <div className={twJoin(!isEmailFilledCorrectly && 'pointer-events-none opacity-50')}>
                <p className="mb-4">
                    <Trans
                        i18nKey="ContactInformationInfo"
                        defaultTrans="By clicking on the Send order button, you agree with <lnk1>terms and conditions</lnk1> of the e-shop and with the <lnk2>processing of privacy policy</lnk2>."
                        components={{
                            lnk1:
                                termsAndConditionsArticleUrl !== undefined ? (
                                    <Link href={termsAndConditionsArticleUrl} isExternal target="_blank" />
                                ) : (
                                    <span></span>
                                ),
                            lnk2:
                                privacyPolicyArticleUrl !== undefined ? (
                                    <Link href={privacyPolicyArticleUrl} isExternal target="_blank" />
                                ) : (
                                    <span></span>
                                ),
                        }}
                    />
                </p>
                <CheckboxControlled
                    name={formMeta.fields.newsletterSubscription.name}
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    render={(checkbox) => <ChoiceFormLine>{checkbox}</ChoiceFormLine>}
                    checkboxProps={{
                        label: formMeta.fields.newsletterSubscription.label,
                    }}
                />
            </div>
            <Popup isVisible={isLoginPopupOpened} onCloseCallback={onCloseLoginPopupHandler}>
                <Heading type="h2">{t('Login')}</Heading>
                <Login defaultEmail={emailValue} />
            </Popup>
        </>
    );
};
