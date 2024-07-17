import { PhoneIcon } from 'components/Basic/Icon/PhoneIcon';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { useDeleteDeliveryAddressMutation } from 'graphql/requests/customer/mutations/DeleteDeliveryAddressMutation.generated';
import { useSetDefaultDeliveryAddressMutation } from 'graphql/requests/customer/mutations/SetDefaultDeliveryAddressMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import { DeliveryAddressType } from 'types/customer';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

const DeleteDeliveryAddressPopup = dynamic(
    () =>
        import('components/Blocks/Popup/DeleteDeliveryAddressPopup').then(
            (component) => component.DeleteDeliveryAddressPopup,
        ),
    {
        ssr: false,
    },
);

type AddressListProps = {
    defaultDeliveryAddress: DeliveryAddressType | undefined;
    deliveryAddresses: DeliveryAddressType[];
};

export const AddressList: FC<AddressListProps> = ({ defaultDeliveryAddress, deliveryAddresses }) => {
    const [, deleteDeliveryAddress] = useDeleteDeliveryAddressMutation();
    const [, setDefaultDeliveryAddress] = useSetDefaultDeliveryAddressMutation();
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const deleteItemHandler = async (deliveryAddressUuid: string | undefined) => {
        if (deliveryAddressUuid === undefined) {
            return;
        }

        updatePortalContent(null);
        const deleteDeliveryAddressResult = await deleteDeliveryAddress({ deliveryAddressUuid });

        if (deleteDeliveryAddressResult.error !== undefined) {
            showErrorMessage(t('There was an error while deleting your delivery address'), GtmMessageOriginType.other);
            return;
        }

        showSuccessMessage(t('Your delivery address has been deleted'));
    };

    const openDeleteAddressPopup = (addressToBeDeletedUuid: string) => {
        updatePortalContent(
            <DeleteDeliveryAddressPopup
                deleteDeliveryAddressHandler={() => deleteItemHandler(addressToBeDeletedUuid)}
            />,
        );
    };

    const setDefaultItemHandler = async (deliveryAddressUuid: string) => {
        if (defaultDeliveryAddress?.uuid === deliveryAddressUuid) {
            return;
        }

        const setDefaultDeliveryAddressResult = await setDefaultDeliveryAddress({ deliveryAddressUuid });

        if (setDefaultDeliveryAddressResult.error !== undefined) {
            showErrorMessage(
                t('There was an error while setting your delivery address as the default one'),
                GtmMessageOriginType.other,
            );
            return;
        }

        showSuccessMessage(t('Your delivery address has been set as default'));
    };

    return (
        <div className="grid vl:grid-cols-2 w-full gap-4">
            {deliveryAddresses.map((address) => (
                <div
                    key={address.uuid}
                    className={twJoin(
                        'relative flex w-full justify-between rounded-md bg-white border-2 border-skyBlue p-4',
                        defaultDeliveryAddress?.uuid === address.uuid
                            ? 'border-primary bg-whiteSnow'
                            : 'cursor-pointer',
                    )}
                    onClick={() => setDefaultItemHandler(address.uuid)}
                >
                    <div className="flex flex-col">
                        <strong className="mr-1">
                            {address.firstName} {address.lastName}
                        </strong>
                        <span>{address.companyName}</span>
                        <span>
                            {address.street}, {address.city}, {address.postcode}
                        </span>
                        <span>{address.country}</span>
                        {address.telephone && (
                            <div className="flex gap-2 items-center">
                                {address.telephone}
                                <PhoneIcon className="w-4" />
                            </div>
                        )}
                    </div>

                    <button className="px-2 h-fit absolute right-1 top-1">
                        <RemoveIcon
                            className="w-3 h-3 shrink-0 cursor-pointer text-red"
                            onClick={() => openDeleteAddressPopup(address.uuid)}
                        />
                    </button>
                </div>
            ))}
        </div>
    );
};
