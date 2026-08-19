import { UserEditIcon } from 'components/Basic/Icon/UserEditIcon';
import { AddressActionButton } from 'components/Blocks/AddressList/AddressActionButton';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { DeliveryAddressType } from 'types/customer';
import useTranslation from 'utils/i18n/useTranslationWrapper';

const DeliveryAddressPopup = dynamic(
    () => import('components/Blocks/Popup/DeliveryAddressPopup').then((component) => component.DeliveryAddressPopup),
    {
        ssr: false,
    },
);

type EditAddressActionProps = {
    address: DeliveryAddressType;
};

export const EditAddressAction: FC<EditAddressActionProps> = ({ address }) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const handleEditDeliveryAddress = (e: React.MouseEvent<HTMLButtonElement>) => {
        e.stopPropagation();
        updatePortalContent(<DeliveryAddressPopup deliveryAddress={address} />);
    };

    return (
        <AddressActionButton
            ariaLabel={t('Edit this delivery address', { ns: 'accessibility' })}
            onClick={handleEditDeliveryAddress}
        >
            <UserEditIcon className="size-6" /> {t('Edit data')}
        </AddressActionButton>
    );
};
