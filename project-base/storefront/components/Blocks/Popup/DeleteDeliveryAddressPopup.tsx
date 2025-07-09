import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import useTranslation from 'next-translate/useTranslation';
import { useSessionStore } from 'store/useSessionStore';

type DeleteDeliveryAddressPopupProps = {
    deleteDeliveryAddressHandler: () => void;
};

export const DeleteDeliveryAddressPopup: FC<DeleteDeliveryAddressPopupProps> = ({ deleteDeliveryAddressHandler }) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    return (
        <Popup
            className="vl:w-auto w-11/12 lg:w-4/5"
            contentClassName="overflow-y-auto"
            title={t('Do you really want to delete this delivery address?')}
        >
            <div className="flex flex-row flex-nowrap justify-between">
                <Button
                    variant="inverted"
                    onClick={() => {
                        updatePortalContent(null);
                    }}
                >
                    {t('No')}
                </Button>
                <Button onClick={deleteDeliveryAddressHandler}>{t('Yes')}</Button>
            </div>
        </Popup>
    );
};
