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
        <div className="flex w-full flex-col">
            {deliveryAddresses.map((address) => (
                <div
                    key={address.uuid}
                    className={twJoin(
                        'mb-5 flex w-full items-center justify-between rounded border border-skyBlue p-5',
                        defaultDeliveryAddress?.uuid === address.uuid
                            ? 'border-primary bg-whiteSnow'
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
                        className="w-7 shrink-0 cursor-pointer p-2 text-graySlate hover:text-red"
                        onClick={() => openDeleteAddressPopup(address.uuid)}
                    />
                </div>
            ))}
        </div>
    );
};
