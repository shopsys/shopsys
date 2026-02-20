import { ContactInformationDeliveryAddressForm } from './ContactInformationDeliveryAddressForm';
import { ContactInformationDeliveryPickUpAddress } from './ContactInformationDeliveryPickUpAddress';
import { ContactInformationDeliveryPickUpForm } from './ContactInformationDeliveryPickUpForm';
import { AnimateCollapseDiv } from 'components/Basic/Animations/AnimateCollapseDiv';
import { AddressList } from 'components/Blocks/AddressList/AddressList';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { FormBlockWrapper, FormHeading } from 'components/Forms/Form/Form';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { useContactInformationFormMeta } from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { AnimatePresence } from 'framer-motion';
import { useEffect } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const ContactInformationDeliveryAddress: FC = () => {
    const updateContactInformation = usePersistStore((store) => store.updateContactInformation);
    const { t } = useTranslation();
    const { transport, pickupPlace } = useCurrentCart();
    const user = useCurrentCustomerData();
    const formProviderMethods = useFormContext<ContactInformation>();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const [isDeliveryAddressDifferentFromBilling, deliveryAddressUuid] = useWatch({
        name: [formMeta.fields.isDeliveryAddressDifferentFromBilling.name, formMeta.fields.deliveryAddressUuid.name],
        control: formProviderMethods.control,
    });
    const isUserLoggedIn = useIsUserLoggedIn();
    const { canManagePersonalData } = useAuthorization();
    const { setValue } = formProviderMethods;
    const deliveryAddressUuidFieldName = formMeta.fields.deliveryAddressUuid.name;
    const defaultDeliveryAddressUuid = user?.defaultDeliveryAddress?.uuid;
    const firstDeliveryAddressUuid = user?.deliveryAddresses[0]?.uuid;

    const handleChangeDeliveryAddressForOrder = (value: string) => {
        setValue(deliveryAddressUuidFieldName, value);
        updateContactInformation({ deliveryAddressUuid: value });
    };

    useEffect(() => {
        if (deliveryAddressUuid) {
            return;
        }

        if (defaultDeliveryAddressUuid) {
            setValue(deliveryAddressUuidFieldName, defaultDeliveryAddressUuid);
        } else if (firstDeliveryAddressUuid) {
            setValue(deliveryAddressUuidFieldName, firstDeliveryAddressUuid);
        }
    }, [
        defaultDeliveryAddressUuid,
        firstDeliveryAddressUuid,
        deliveryAddressUuid,
        deliveryAddressUuidFieldName,
        setValue,
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
                onChange={(event) =>
                    updateContactInformation({ isDeliveryAddressDifferentFromBilling: event.target.checked })
                }
            />

            <AnimatePresence initial={false}>
                {isDeliveryAddressDifferentFromBilling && (
                    <AnimateCollapseDiv className="block!">
                        {!pickupPlace && isUserLoggedIn && (
                            <>
                                <p className="h4 mb-5">{t('Choose delivery address')}</p>

                                <AddressList
                                    defaultDeliveryAddress={user?.defaultDeliveryAddress}
                                    deliveryAddresses={user?.deliveryAddresses ?? []}
                                    orderSelectAddressHandler={handleChangeDeliveryAddressForOrder}
                                    orderSelectedAddress={deliveryAddressUuid}
                                />
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
