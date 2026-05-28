import { FormBlockWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { PhoneNumberInputControlled } from 'components/Forms/PhonePrefixSelect/PhoneNumberInputControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { TIDs } from 'cypress/tids';
import { useIsCustomerUserRegisteredQuery } from 'graphql/requests/customer/queries/IsCustomerUserRegisteredQuery.generated';
import dynamic from 'next/dynamic';
import { useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import useTranslation from 'utils/i18n/useTranslationWrapper';

const LoginPopup = dynamic(
    () => import('components/Blocks/Popup/LoginPopup').then((component) => component.LoginPopup),
    {
        ssr: false,
    },
);
export const ContactInformationPersonalInformation: FC = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const { t } = useTranslation();
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta();
    const { formState } = formProviderMethods;
    const isUserLoggedIn = useIsUserLoggedIn();
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

    const isEmailAlreadyRegistered =
        isCustomerUserRegisteredData?.isCustomerUserRegistered && !isUserLoggedIn && !formState.errors.email;

    return (
        <FormBlockWrapper>
            <FormHeading>{t('Customer information')}</FormHeading>

            <div>
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.email.name}
                    textInputProps={{
                        hasWarning: isEmailAlreadyRegistered,
                        label: formMeta.fields.email.label,
                        required: true,
                        type: 'email',
                        autoComplete: 'email',
                        onChange: (event) => updateContactInformation({ email: event.currentTarget.value }),
                        disabled: formMeta.fields.email.disabled,
                    }}
                />

                {isEmailAlreadyRegistered && (
                    <div className="-mt-2 rounded-b-md bg-input-warning-message-bg px-2.5 pt-3 pb-1.5 text-sm">
                        <span>{t('This email is already registered')}</span>{' '}
                        <button
                            aria-haspopup="dialog"
                            className="cursor-pointer text-sm underline hover:no-underline"
                            data-tid={TIDs.login_in_order_button}
                            tabIndex={0}
                            type="button"
                            onClick={openLoginPopup}
                        >
                            {t('Log in')}
                        </button>
                    </div>
                )}
            </div>

            <FormColumn>
                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.firstName.name}
                    width="half"
                    textInputProps={{
                        label: formMeta.fields.firstName.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'given-name',
                        onChange: (event) => updateContactInformation({ firstName: event.currentTarget.value }),
                    }}
                />

                <TextInputControlled
                    control={formProviderMethods.control}
                    formName={formMeta.formName}
                    name={formMeta.fields.lastName.name}
                    width="half"
                    textInputProps={{
                        label: formMeta.fields.lastName.label,
                        required: true,
                        type: 'text',
                        autoComplete: 'family-name',
                        onChange: (event) => updateContactInformation({ lastName: event.currentTarget.value }),
                    }}
                />
            </FormColumn>

            <PhoneNumberInputControlled
                formName={formMeta.formName}
                formProviderMethods={formProviderMethods}
                prefixCountryCodeName={formMeta.fields.telephonePrefixCountryCode.name}
                prefixName={formMeta.fields.telephonePrefix.name}
                telephoneLabel={formMeta.fields.telephone.label}
                telephoneName={formMeta.fields.telephone.name}
                telephoneOnChange={(event) => updateContactInformation({ telephone: event.currentTarget.value })}
                onPrefixChange={(dialCode, countryCode) =>
                    updateContactInformation({
                        telephonePrefix: dialCode,
                        telephonePrefixCountryCode: countryCode,
                    })
                }
            />
        </FormBlockWrapper>
    );
};
