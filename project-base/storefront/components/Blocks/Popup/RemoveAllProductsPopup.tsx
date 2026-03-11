import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { TIDs } from 'cypress/tids';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type RemoveAllProductsPopupProps = {
    title: string;
    removeAllHandler: () => void;
};

export const RemoveAllProductsPopup: FC<RemoveAllProductsPopupProps> = ({ title, removeAllHandler }) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const restoreStoredFocus = useSessionStore((s) => s.restoreStoredFocus);

    const handleClose = () => {
        updatePortalContent(null);
        restoreStoredFocus();
    };

    const handleConfirm = () => {
        handleClose();
        removeAllHandler();
    };

    return (
        <Popup className="vl:w-auto w-11/12 lg:w-4/5" contentClassName="overflow-y-auto" title={title}>
            <div className="flex flex-row flex-nowrap justify-between">
                <Button variant="inverted" onClick={handleClose}>
                    {t('No')}
                </Button>
                <Button tid={TIDs.popup_confirm_button} onClick={handleConfirm}>
                    {t('Yes')}
                </Button>
            </div>
        </Popup>
    );
};
