import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import useTranslation from 'next-translate/useTranslation';
import { useSessionStore } from 'store/useSessionStore';

type DeleteCustomerUserPopupProps = {
    deleteCustomerUserHandler: () => void;
};

export const DeleteCustomerUserPopup: FC<DeleteCustomerUserPopupProps> = ({ deleteCustomerUserHandler }) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    return (
        <Popup className="w-11/12 lg:w-4/5 vl:w-auto" contentClassName="overflow-y-auto">
            <div className="flex flex-col">
                {t('Do you really want to delete this user?')}
                <div className="mt-4 flex flex-row flex-nowrap justify-between">
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
            </div>
        </Popup>
    );
};
