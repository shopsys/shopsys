import { Icon } from 'components/Basic/Icon/Icon';
import { Button } from 'components/Forms/Button/Button';
import { showErrorMessage, showSuccessMessage } from 'helpers/visual/toasts';
import { useDeleteDeliveryAddressMutationApi, useSetDefaultDeliveryAddressMutationApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import dynamic from 'next/dynamic';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { DeliveryAddressType } from 'types/customer';
import { GtmMessageOriginType } from 'types/gtm/enums';

const Popup = dynamic(() => import('components/Layout/Popup/Popup').then((component) => component.Popup));

type AddressListProps = {
    defaultDeliveryAddress: DeliveryAddressType | undefined;
    deliveryAddresses: DeliveryAddressType[];
};

const TEST_IDENTIFIER = 'list-addresses';

export const AddressList: FC<AddressListProps> = ({ defaultDeliveryAddress, deliveryAddresses }) => {
    const [addressToBeDeleted, setAddressToBeDeleted] = useState<string | undefined>(undefined);
    const [, deleteDeliveryAddress] = useDeleteDeliveryAddressMutationApi();
    const [, setDefaultDeliveryAddress] = useSetDefaultDeliveryAddressMutationApi();
    const t = useTypedTranslationFunction();

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
                {deliveryAddresses.map((address, index) => (
                    <div
                        key={address.uuid}
                        className={twJoin(
                            'relative mb-5 flex w-full flex-row flex-wrap rounded-xl border border-grey p-5',
                            defaultDeliveryAddress?.uuid === address.uuid
                                ? 'border-primary bg-greyVeryLight'
                                : 'cursor-pointer',
                        )}
                        data-testid={TEST_IDENTIFIER + '-item-' + index}
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
                                    <Icon iconType="icon" icon="Phone" className="relative top-[2px] mr-1" />
                                    {address.telephone}
                                </>
                            )}
                        </div>

                        <Icon
                            icon="Remove"
                            iconType="icon"
                            onClick={() => setAddressToBeDeleted(address.uuid)}
                            className="absolute right-5 top-5 w-3 cursor-pointer text-greyLight hover:text-red"
                        />
                    </div>
                ))}
            </div>
            {addressToBeDeleted && (
                <Popup onCloseCallback={() => setAddressToBeDeleted(undefined)} className="w-11/12 lg:w-4/5 vl:w-auto">
                    <div className="flex flex-col">
                        {t('Do you really want to delete this delivery address?')}
                        <div className="mt-4 flex flex-row flex-nowrap justify-between">
                            <Button onClick={() => setAddressToBeDeleted(undefined)}>
                                <Icon iconType="icon" icon="Arrow" className="relative mr-4 rotate-90 text-white" />
                                {t('No')}
                            </Button>
                            <Button onClick={() => deleteItemHandler(addressToBeDeleted)}>
                                {t('Yes')}
                                <Icon iconType="icon" icon="Arrow" className="relative ml-4 -rotate-90" />
                            </Button>
                        </div>
                    </div>
                </Popup>
            )}
        </>
    );
};
