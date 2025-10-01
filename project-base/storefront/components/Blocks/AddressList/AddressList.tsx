import { AddAddressAction } from './Actions/AddAddressAction';
import { AddressCard } from './AddressCard';
import { useSetDefaultDeliveryAddressMutation } from 'graphql/requests/customer/mutations/SetDefaultDeliveryAddressMutation.generated';
import { useEffect } from 'react';
import { DeliveryAddressType } from 'types/customer';

type AddressListProps = {
    defaultDeliveryAddress: DeliveryAddressType | undefined;
    deliveryAddresses: DeliveryAddressType[];
    orderSelectAddressHandler?: (addressUuid: string) => void;
    orderSelectedAddress?: string;
    hideAddAddressAction?: boolean;
};

export const AddressList: FC<AddressListProps> = ({
    defaultDeliveryAddress,
    deliveryAddresses,
    orderSelectAddressHandler,
    orderSelectedAddress,
    hideAddAddressAction = false,
}) => {
    const [, setDefaultDeliveryAddress] = useSetDefaultDeliveryAddressMutation();

    useEffect(() => {
        if (defaultDeliveryAddress === undefined && deliveryAddresses.length > 0) {
            setDefaultDeliveryAddress({ deliveryAddressUuid: deliveryAddresses[0].uuid });
        }
    }, [defaultDeliveryAddress, deliveryAddresses, setDefaultDeliveryAddress]);

    return (
        <div className="flex flex-col gap-5">
            {deliveryAddresses.length > 0 && (
                <div className="vl:grid-cols-2 grid w-full gap-4">
                    {deliveryAddresses.map((address) => (
                        <AddressCard
                            key={address.uuid}
                            address={address}
                            defaultDeliveryAddress={defaultDeliveryAddress}
                            hideActions={hideAddAddressAction}
                            orderSelectAddressHandler={orderSelectAddressHandler}
                            orderSelectedAddress={orderSelectedAddress === address.uuid}
                        />
                    ))}
                </div>
            )}

            {!hideAddAddressAction && <AddAddressAction />}
        </div>
    );
};
