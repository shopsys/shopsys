import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { TIDs } from 'cypress/tids';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type RemoveAllProductsPopupProps = {
    description: string;
    removeAllHandler: () => void;
};

export const RemoveAllProductsPopup: FC<RemoveAllProductsPopupProps> = ({ description, removeAllHandler }) => {
    const { t } = useTranslation();
    const closePortalContent = useSessionStore((s) => s.closePortalContent);

    const handleClose = () => {
        closePortalContent();
    };

    const handleConfirm = () => {
        handleClose();
        removeAllHandler();
    };

    return (
        <Popup
            ariaDescription={description}
            className="w-[calc(100vw-40px)] max-w-120"
            role="alertdialog"
            title={t('Remove all products?')}
        >
            <div className="flex flex-col gap-6">
                <p>{description}</p>

                <div className="flex flex-col justify-end gap-3 sm:flex-row">
                    <Button className="w-full sm:w-auto" variant="secondary" onClick={handleClose}>
                        {t('Cancel')}
                    </Button>
                    <Button
                        className="w-full sm:w-auto"
                        tid={TIDs.popup_confirm_button}
                        variant="danger"
                        onClick={handleConfirm}
                    >
                        {t('Remove all')}
                    </Button>
                </div>
            </div>
        </Popup>
    );
};
