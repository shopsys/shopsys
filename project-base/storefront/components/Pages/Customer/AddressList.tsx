import { EditIcon } from 'components/Basic/Icon/EditIcon';
import { PhoneIcon } from 'components/Basic/Icon/PhoneIcon';
import { RemoveIcon } from 'components/Basic/Icon/RemoveIcon';
import { DeliveryAddressPopup } from 'components/Blocks/Popup/DeliveryAddressPopup';
import { Button } from 'components/Forms/Button/Button';
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
    const { t } = useTranslation();
    const [, deleteDeliveryAddress] = useDeleteDeliveryAddressMutation();
    const [, setDefaultDeliveryAddress] = useSetDefaultDeliveryAddressMutation();
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

    const openDeleteAddressPopup = (
        e: React.MouseEvent<HTMLButtonElement, MouseEvent>,
        addressToBeDeletedUuid: string,
    ) => {
        e.stopPropagation();
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

    const openDeliveryAddressPopup = (
        e: React.MouseEvent<HTMLButtonElement, MouseEvent>,
        address: DeliveryAddressType,
    ) => {
        e.stopPropagation();
        updatePortalContent(<DeliveryAddressPopup deliveryAddress={address} />);
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
                    <div className="flex flex-col w-full">
                        <strong className="mr-1">
                            {address.firstName} {address.lastName}
                        </strong>
                        <span>{address.companyName}</span>
                        <span>
                            {address.street}, {address.city}, {address.postcode}
                        </span>
                        <span>{address.country.name}</span>
                        {address.telephone && (
                            <div className="flex gap-2 items-center">
                                {address.telephone}
                                <PhoneIcon className="w-4" />
                            </div>
                        )}
                        <div className="flex space-between gap-2 mt-2">
                            <Button
                                className="flex-1"
                                size="small"
                                variant="secondaryOutlined"
                                onClick={(e) => openDeleteAddressPopup(e, address.uuid)}
                            >
                                <RemoveIcon className="size-4" /> {t('Delete')}
                            </Button>
                            <Button
                                className="flex-1"
                                size="small"
                                variant="secondary"
                                onClick={(e) => openDeliveryAddressPopup(e, address)}
                            >
                                <EditIcon className="size-4" /> {t('Edit')}
                            </Button>
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
};
