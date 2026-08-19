import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
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
            className="vl:w-auto w-11/12 lg:w-4/5"
            contentClassName="overflow-y-auto"
            title={t('Do you really want to delete this delivery address?')}
        >
            <div className="flex flex-row flex-nowrap justify-between">
                <Button variant="secondary" onClick={closePortalContent}>
                    {t('No')}
                </Button>
                <Button variant="danger" onClick={deleteDeliveryAddressHandler}>
                    {t('Yes')}
                </Button>
            </div>
        </Popup>
    );
};
