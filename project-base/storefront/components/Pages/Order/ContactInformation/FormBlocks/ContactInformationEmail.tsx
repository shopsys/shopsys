import { Button } from 'components/Forms/Button/Button';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useIsCustomerUserRegisteredQuery } from 'graphql/requests/customer/queries/IsCustomerUserRegisteredQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useEffect } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';

const LoginPopup = dynamic(
    () => import('components/Blocks/Popup/LoginPopup').then((component) => component.LoginPopup),
    {
        ssr: false,
    },
);

export const ContactInformationEmail: FC = () => {
    const { t } = useTranslation();
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const isUserLoggedIn = useIsUserLoggedIn();

    const formProviderMethods = useFormContext<ContactInformation>();
    const { formState } = formProviderMethods;
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const emailValue = useWatch({ name: formMeta.fields.email.name, control: formProviderMethods.control });
    const isEmailFilledCorrectly = !!emailValue && !formState.errors.email;
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const [{ data: isCustomerUserRegisteredData }] = useIsCustomerUserRegisteredQuery({
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
                    onChange: (event) => updateContactInformation({ email: event.currentTarget.value }),
                }}
            />
            {isCustomerUserRegisteredData?.isCustomerUserRegistered && !isUserLoggedIn && (
                <Button className="mb-5" size="small" type="button" onClick={openLoginPopup}>
                    {t('User with this email is already registered. Do you want to sign in')}
                </Button>
            )}
        </>
    );
};
