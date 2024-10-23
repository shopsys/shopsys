import { Button } from 'components/Forms/Button/Button';
import { FormBlockWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TIDs } from 'cypress/tids';
import { useIsCustomerUserRegisteredQuery } from 'graphql/requests/customer/queries/IsCustomerUserRegisteredQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';

const LoginPopup = dynamic(
    () => import('components/Blocks/Popup/LoginPopup').then((component) => ({
        default: component.LoginPopup
    })),
    {
        ssr: false,
    },
);

export const ContactInformationEmail: FC = () => {
    const { t } = useTranslation();
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const isUserLoggedIn = useIsUserLoggedIn();
    const user = useCurrentCustomerData();

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

    return (
        <FormBlockWrapper>
            <FormHeading>{t('Customer information')}</FormHeading>
            <TextInputControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.email.name}
                render={(textInput) => <FormLine>{textInput}</FormLine>}
                textInputProps={{
                    label: formMeta.fields.email.label,
                    required: true,
                    disabled: user !== undefined,
                    type: 'email',
                    autoComplete: 'email',
                    onChange: (event) => updateContactInformation({ email: event.currentTarget.value }),
                }}
            />
            {isCustomerUserRegisteredData?.isCustomerUserRegistered && !isUserLoggedIn && (
                <div className="mt-4 flex flex-col gap-2">
                    <span>{t('User with this email is already registered')}</span>
                    <Button
                        className="w-fit"
                        size="small"
                        tid={TIDs.login_in_order_button}
                        type="button"
                        onClick={openLoginPopup}
                    >
                        {t('Sign in')}
                    </Button>
                </div>
            )}
        </FormBlockWrapper>
    );
};
