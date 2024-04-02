import { Button } from 'components/Forms/Button/Button';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useIsCustomerUserRegisteredQuery } from 'graphql/requests/customer/queries/IsCustomerUserRegisteredQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { Dispatch, SetStateAction, useEffect } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';

type ContactInformationEmailProps = {
    setIsLoginPopupOpened: Dispatch<SetStateAction<boolean>>;
};

export const ContactInformationEmail: FC<ContactInformationEmailProps> = ({ setIsLoginPopupOpened }) => {
    const { t } = useTranslation();
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const isUserLoggedIn = useIsUserLoggedIn();

    const formProviderMethods = useFormContext<ContactInformation>();
    const { formState } = formProviderMethods;
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const emailValue = useWatch({ name: formMeta.fields.email.name, control: formProviderMethods.control });
    const isEmailFilledCorrectly = !!emailValue && !formState.errors.email;

    const [{ data: isCustomerUserRegisteredData }] = useIsCustomerUserRegisteredQuery({
        variables: {
            email: emailValue,
        },
        pause: !isEmailFilledCorrectly,
    });

    useEffect(() => {
        if (isUserLoggedIn) {
            setIsLoginPopupOpened(false);
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
                <Button className="mb-5" size="small" type="button" onClick={() => setIsLoginPopupOpened(true)}>
                    {t('User with this email is already registered. Do you want to sign in')}
                </Button>
            )}
        </>
    );
};
