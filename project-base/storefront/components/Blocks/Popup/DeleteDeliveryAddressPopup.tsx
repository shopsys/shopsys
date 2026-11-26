import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { PopupActions } from 'components/Layout/Popup/PopupActions';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type DeleteDeliveryAddressPopupProps = {
    deleteDeliveryAddressHandler: () => void;
};

export const DeleteDeliveryAddressPopup: FC<DeleteDeliveryAddressPopupProps> = ({ deleteDeliveryAddressHandler }) => {
    const { t } = useTranslation();
    const closePortalContent = useSessionStore((s) => s.closePortalContent);

    return (
        <Popup
            size="small"
            contentClassName="overflow-y-auto"
            title={t('Do you really want to delete this delivery address?')}
        >
            <PopupActions>
                <Button className="w-full sm:w-auto" variant="outline" onClick={closePortalContent}>
                    {t('No')}
                </Button>
                <Button className="w-full sm:w-auto" variant="danger" onClick={deleteDeliveryAddressHandler}>
                    {t('Yes')}
                </Button>
            </PopupActions>
        </Popup>
    );
};
