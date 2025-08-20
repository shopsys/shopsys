import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type DeleteCustomerUserPopupProps = {
    deleteCustomerUserHandler: () => void;
};

export const DeleteCustomerUserPopup: FC<DeleteCustomerUserPopupProps> = ({ deleteCustomerUserHandler }) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    return (
        <Popup
            className="vl:w-auto w-11/12 lg:w-4/5"
            contentClassName="overflow-y-auto"
            title={t('Do you really want to delete this user?')}
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
                <Button onClick={deleteCustomerUserHandler}>{t('Yes')}</Button>
            </div>
        </Popup>
    );
};
