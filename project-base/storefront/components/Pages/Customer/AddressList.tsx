import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { PhoneIcon } from 'components/Basic/Icon/PhoneIcon';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { Button } from 'components/Forms/Button/Button';
import { useDeleteDeliveryAddressMutation } from 'graphql/requests/customer/mutations/DeleteDeliveryAddressMutation.generated';
import { useSetDefaultDeliveryAddressMutation } from 'graphql/requests/customer/mutations/SetDefaultDeliveryAddressMutation.generated';
import { GtmMessageOriginType } from 'gtm/types/enums';
import { showErrorMessage, showSuccessMessage } from 'helpers/toasts';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { DeliveryAddressType } from 'types/customer';

const Popup = dynamic(() => import('components/Layout/Popup/Popup').then((component) => component.Popup));

type AddressListProps = {
    defaultDeliveryAddress: DeliveryAddressType | undefined;
    deliveryAddresses: DeliveryAddressType[];
};

export const AddressList: FC<AddressListProps> = ({ defaultDeliveryAddress, deliveryAddresses }) => {
    const [addressToBeDeleted, setAddressToBeDeleted] = useState<string | undefined>(undefined);
    const [, deleteDeliveryAddress] = useDeleteDeliveryAddressMutation();
    const [, setDefaultDeliveryAddress] = useSetDefaultDeliveryAddressMutation();
    const { t } = useTranslation();

    const deleteItemHandler = async (deliveryAddressUuid: string | undefined) => {
        if (deliveryAddressUuid === undefined) {
            return;
        }

        setAddressToBeDeleted(undefined);
        const deleteDeliveryAddressResult = await deleteDeliveryAddress({ deliveryAddressUuid });

        if (deleteDeliveryAddressResult.error !== undefined) {
            showErrorMessage(t('There was an error while deleting your delivery address'), GtmMessageOriginType.other);
            return;
        }

        showSuccessMessage(t('Your delivery address has been deleted'));
    };

    const setDefaultItemHandler = async (deliveryAddressUuid: string) => {
        if (defaultDeliveryAddress?.uuid === deliveryAddressUuid) {
            return;
        }

        const result = await setDefaultDeliveryAddress({ deliveryAddressUuid });

        if (result.error !== undefined) {
            showErrorMessage(
                t('There was an error while setting your delivery address as the default one'),
                GtmMessageOriginType.other,
            );
            return;
        }

        showSuccessMessage(t('Your delivery address has been set as default'));
    };

    return (
        <>
            <div className="flex w-full flex-col">
                {deliveryAddresses.map((address) => (
                    <div
                        key={address.uuid}
                        className={twJoin(
                            'mb-5 flex w-full items-center justify-between rounded border border-grey p-5',
                            defaultDeliveryAddress?.uuid === address.uuid
                                ? 'border-primary bg-greyVeryLight'
                                : 'cursor-pointer',
                        )}
                        onClick={() => setDefaultItemHandler(address.uuid)}
                    >
                        <div>
                            <strong className="mr-1">
                                {address.firstName} {address.lastName}
                            </strong>
                            {address.companyName}
                            <br />
                            {address.street}, {address.city}, {address.postcode}
                            <br />
                            {address.country}
                            <br />
                            {address.telephone && (
                                <>
                                    <PhoneIcon className="mr-1" />
                                    {address.telephone}
                                </>
                            )}
                        </div>

                        <RemoveIcon
                            className="w-7 shrink-0 cursor-pointer p-2 text-greyLight hover:text-red"
                            onClick={() => setAddressToBeDeleted(address.uuid)}
                        />
                    </div>
                ))}
            </div>
            {addressToBeDeleted && (
                <Popup className="w-11/12 lg:w-4/5 vl:w-auto" onCloseCallback={() => setAddressToBeDeleted(undefined)}>
                    <div className="flex flex-col">
                        {t('Do you really want to delete this delivery address?')}
                        <div className="mt-4 flex flex-row flex-nowrap justify-between">
                            <Button onClick={() => setAddressToBeDeleted(undefined)}>
                                <ArrowIcon className="relative mr-4 rotate-90 text-white" />
                                {t('No')}
                            </Button>
                            <Button onClick={() => deleteItemHandler(addressToBeDeleted)}>
                                {t('Yes')}
                                <ArrowIcon className="relative ml-4 -rotate-90" />
                            </Button>
                        </div>
                    </div>
                </Popup>
            )}
        </>
    );
};
