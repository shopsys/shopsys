import { Button } from 'components/Forms/Button/Button';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';

const DeliveryAddressPopup = dynamic(
    () => import('components/Blocks/Popup/DeliveryAddressPopup').then((component) => component.DeliveryAddressPopup),
    {
        ssr: false,
    },
);

export const AddAddressAction: FC = () => {
    const { t } = useTranslation();
    const { canManagePersonalData } = useAuthorization();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const openAddDeliveryAddressPopup = (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
        e.stopPropagation();
        updatePortalContent(<DeliveryAddressPopup />);
    };

    if (!canManagePersonalData) {
        return null;
    }

    return (
        <Button
            aria-haspopup="dialog"
            className="self-start"
            size="small"
            variant="secondary"
            onClick={openAddDeliveryAddressPopup}
        >
            {t('Add new delivery address')}
        </Button>
    );
};
