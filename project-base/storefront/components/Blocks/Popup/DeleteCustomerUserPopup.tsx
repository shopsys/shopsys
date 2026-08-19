import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { TIDs } from 'cypress/tids';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type DeleteCustomerUserPopupProps = {
    deleteCustomerUserHandler: () => void;
};

export const DeleteCustomerUserPopup: FC<DeleteCustomerUserPopupProps> = ({ deleteCustomerUserHandler }) => {
    const { t } = useTranslation();
    const closePortalContent = useSessionStore((s) => s.closePortalContent);

    return (
        <Popup
            className="vl:w-auto w-11/12 lg:w-4/5"
            contentClassName="overflow-y-auto"
            title={t('Do you really want to delete this user?')}
        >
            <div className="flex flex-row flex-nowrap justify-between">
                <Button
                    data-tid={TIDs.customer_users_delete_cancel_button}
                    variant="secondary"
                    onClick={closePortalContent}
                >
                    {t('No')}
                </Button>
                <Button
                    data-tid={TIDs.customer_users_delete_confirm_button}
                    variant="danger"
                    onClick={deleteCustomerUserHandler}
                >
                    {t('Yes')}
                </Button>
            </div>
        </Popup>
    );
};
