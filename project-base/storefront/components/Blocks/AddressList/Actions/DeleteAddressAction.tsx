import { TrashCanIcon } from 'components/Basic/Icon/TrashCanIcon';
import { AddressActionButton } from 'components/Blocks/AddressList/AddressActionButton';
import { useDeleteDeliveryAddressMutation } from 'graphql/requests/customer/mutations/DeleteDeliveryAddressMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { DeliveryAddressType } from 'types/customer';
import useTranslation from 'utils/i18n/useTranslationWrapper';
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

type DeleteAddressActionProps = {
    address: DeliveryAddressType;
};

export const DeleteAddressAction: FC<DeleteAddressActionProps> = ({ address }) => {
    const { t } = useTranslation();
    const [, deleteDeliveryAddress] = useDeleteDeliveryAddressMutation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const closePortalContent = useSessionStore((s) => s.closePortalContent);

    const deleteItemHandler = async (deliveryAddressUuid: string) => {
        closePortalContent();
        const deleteDeliveryAddressResult = await deleteDeliveryAddress({ deliveryAddressUuid });

        if (deleteDeliveryAddressResult.error !== undefined) {
            showErrorMessage(t('There was an error while deleting your delivery address'), GtmMessageOriginType.other);
            return;
        }

        showSuccessMessage(t('Your delivery address has been deleted'));
    };

    const handleDeleteDeliveryAddress = (e: React.MouseEvent<HTMLButtonElement>) => {
        e.stopPropagation();
        updatePortalContent(
            <DeleteDeliveryAddressPopup deleteDeliveryAddressHandler={() => deleteItemHandler(address.uuid)} />,
        );
    };

    return (
        <AddressActionButton
            ariaLabel={t('Delete this delivery address', { ns: 'accessibility' })}
            onClick={handleDeleteDeliveryAddress}
        >
            <TrashCanIcon className="size-6" /> {t('Delete address')}
        </AddressActionButton>
    );
};
