import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { TIDs } from 'cypress/tids';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type DeleteCustomerUserPopupProps = {
    customerUserName: string;
    customerUserEmail: string;
    deleteCustomerUserHandler: () => void;
};

export const DeleteCustomerUserPopup: FC<DeleteCustomerUserPopupProps> = ({
    customerUserName,
    customerUserEmail,
    deleteCustomerUserHandler,
}) => {
    const { t } = useTranslation();
    const closePortalContent = useSessionStore((s) => s.closePortalContent);

    const description = t('User {{name}} ({{email}}) will lose access to your company account.', {
        name: customerUserName,
        email: customerUserEmail,
    });

    return (
        <Popup
            size="small"
            ariaDescription={description}
            contentClassName="overflow-y-auto"
            role="alertdialog"
            title={t('Delete user?')}
        >
            <div className="flex flex-col gap-6">
                <p className="wrap-anywhere">{description}</p>

                <div className="flex flex-col justify-end gap-3 sm:flex-row">
                    <Button
                        className="w-full sm:w-auto"
                        tid={TIDs.customer_users_delete_cancel_button}
                        variant="secondary"
                        onClick={closePortalContent}
                    >
                        {t('Cancel')}
                    </Button>
                    <Button
                        className="w-full sm:w-auto"
                        tid={TIDs.customer_users_delete_confirm_button}
                        variant="danger"
                        onClick={deleteCustomerUserHandler}
                    >
                        {t('Delete user')}
                    </Button>
                </div>
            </div>
        </Popup>
    );
};
