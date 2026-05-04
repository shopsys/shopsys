import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { AddressList } from 'components/Blocks/AddressList/AddressList';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { FormBlockWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { AnimatePresence } from 'framer-motion';
import { useEffect } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { DeliveryAddressType } from 'types/customer';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ContactInformationDeliveryAddressForm } from './ContactInformationDeliveryAddressForm';
import { ContactInformationDeliveryPickUpAddress } from './ContactInformationDeliveryPickUpAddress';
import { ContactInformationDeliveryPickUpForm } from './ContactInformationDeliveryPickUpForm';

export const ContactInformationDeliveryAddress: FC = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const { t } = useTranslation();
    const { transport, pickupPlace } = useCurrentCart();
    const user = useCurrentCustomerData();
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta();
    const [isDeliveryAddressDifferentFromBilling, deliveryAddressUuid] = useWatch({
        name: [formMeta.fields.isDeliveryAddressDifferentFromBilling.name, formMeta.fields.deliveryAddressUuid.name],
        control: formProviderMethods.control,
    });
    const isUserLoggedIn = useIsUserLoggedIn();
    const { canManagePersonalData } = useAuthorization();
    const { getValues, reset, setValue } = formProviderMethods;
    const deliveryAddressUuidFieldName = formMeta.fields.deliveryAddressUuid.name;
    const deliveryAddressUuidError = formProviderMethods.formState.errors.deliveryAddressUuid;
    const defaultDeliveryAddressUuid = user?.defaultDeliveryAddress?.uuid;
    const firstDeliveryAddressUuid = user?.deliveryAddresses[0]?.uuid;

    const handleChangeDeliveryAddressForOrder = (deliveryAddressUuid: string) => {
        const deliveryAddress = user?.deliveryAddresses.find((address) => address.uuid === deliveryAddressUuid);

        if (!deliveryAddress) {
            setValue(deliveryAddressUuidFieldName, deliveryAddressUuid, { shouldValidate: true });
            updateContactInformation({
                deliveryAddressUuid,
                isDeliveryAddressDifferentFromBilling: true,
            });

            return;
        }

        const deliveryAddressContactInformation = mapSelectedDeliveryAddressToContactInformation(deliveryAddress);

        reset({ ...getValues(), ...deliveryAddressContactInformation }, { keepErrors: true, keepDirty: true });
        updateContactInformation(deliveryAddressContactInformation);
    };

    useEffect(() => {
        const deliveryAddressUuidToSelect = defaultDeliveryAddressUuid ?? firstDeliveryAddressUuid ?? '';

        if (deliveryAddressUuid) {
            if (!isUserLoggedIn || pickupPlace || user === undefined || user === null) {
                return;
            }

            const isSelectedDeliveryAddressAvailable = user.deliveryAddresses.some((deliveryAddress) => {
                return deliveryAddress.uuid === deliveryAddressUuid;
            });

            if (isSelectedDeliveryAddressAvailable) {
                return;
            }

            setValue(deliveryAddressUuidFieldName, deliveryAddressUuidToSelect, {
                shouldValidate: isDeliveryAddressDifferentFromBilling,
            });
            updateContactInformation({ deliveryAddressUuid: deliveryAddressUuidToSelect });

            return;
        }

        if (deliveryAddressUuidToSelect) {
            setValue(deliveryAddressUuidFieldName, deliveryAddressUuidToSelect, { shouldValidate: true });
            updateContactInformation({ deliveryAddressUuid: deliveryAddressUuidToSelect });
        }
    }, [
        defaultDeliveryAddressUuid,
        firstDeliveryAddressUuid,
        deliveryAddressUuid,
        deliveryAddressUuidFieldName,
        isDeliveryAddressDifferentFromBilling,
        isUserLoggedIn,
        pickupPlace,
        setValue,
        updateContactInformation,
        user,
    ]);

    return (
        <FormBlockWrapper>
            <FormHeading>
                {pickupPlace ? `${t('Personal pickup')} - ${transport?.name}: ${pickupPlace.name}` : ''}
            </FormHeading>

            <ContactInformationDeliveryPickUpAddress />

            <CheckboxControlled
                control={formProviderMethods.control}
                formName={formMeta.formName}
                name={formMeta.fields.isDeliveryAddressDifferentFromBilling.name}
                render={(checkbox) => <FormLine>{checkbox}</FormLine>}
                checkboxProps={{
                    label: formMeta.fields.isDeliveryAddressDifferentFromBilling.label,
                }}
                onChange={(event) => {
                    updateContactInformation({ isDeliveryAddressDifferentFromBilling: event.target.checked });

                    if (!event.target.checked) {
                        formProviderMethods.clearErrors(deliveryAddressUuidFieldName);
                    }
                }}
            />

            <AnimatePresence initial={false}>
                {isDeliveryAddressDifferentFromBilling && (
                    <AnimateCollapseDiv className="block!">
                        {!pickupPlace && isUserLoggedIn && (
                            <>
                                <p className="h4 mb-5">{t('Choose delivery address')}</p>

                                <div
                                    className="flex flex-col gap-2"
                                    id={`${formMeta.formName}-${deliveryAddressUuidFieldName}`}
                                >
                                    <AddressList
                                        defaultDeliveryAddress={user?.defaultDeliveryAddress}
                                        deliveryAddresses={user?.deliveryAddresses ?? []}
                                        orderSelectAddressHandler={handleChangeDeliveryAddressForOrder}
                                        orderSelectedAddress={deliveryAddressUuid}
                                    />

                                    {deliveryAddressUuidError?.message !== undefined && (
                                        <FormLineError error={deliveryAddressUuidError} inputType="text-input" />
                                    )}
                                </div>
                            </>
                        )}

                        {!pickupPlace && !isUserLoggedIn && canManagePersonalData && (
                            <ContactInformationDeliveryAddressForm />
                        )}

                        {pickupPlace && canManagePersonalData && <ContactInformationDeliveryPickUpForm />}
                    </AnimateCollapseDiv>
                )}
            </AnimatePresence>
        </FormBlockWrapper>
    );
};

const mapSelectedDeliveryAddressToContactInformation = (
    deliveryAddress: DeliveryAddressType,
): Partial<ContactInformation> => ({
    isDeliveryAddressDifferentFromBilling: true,
    deliveryAddressUuid: deliveryAddress.uuid,
    deliveryFirstName: deliveryAddress.firstName,
    deliveryLastName: deliveryAddress.lastName,
    deliveryCompanyName: deliveryAddress.companyName,
    deliveryTelephonePrefix: deliveryAddress.telephonePrefix,
    deliveryTelephonePrefixCountryCode: deliveryAddress.telephonePrefixCountryCode,
    deliveryTelephone: deliveryAddress.telephoneNumber,
    deliveryStreet: deliveryAddress.street,
    deliveryCity: deliveryAddress.city,
    deliveryPostcode: deliveryAddress.postcode,
    deliveryCountry: {
        label: deliveryAddress.country.name,
        value: deliveryAddress.country.code,
    },
});
