import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import useTranslation from 'next-translate/useTranslation';

type MergeCartsPopupProps = {
    mergeOrderItemsWithCurrentCart: (orderUuid: string, shouldMerge?: boolean | undefined) => void;
    orderForPrefillingUuid: string;
    onCloseCallback: () => void;
};

export const MergeCartsPopup: FC<MergeCartsPopupProps> = ({
    mergeOrderItemsWithCurrentCart,
    orderForPrefillingUuid,
    onCloseCallback,
}) => {
    const { t } = useTranslation();

    return (
        <Popup onCloseCallback={onCloseCallback}>
            <p className="mb-6 text-lg lg:text-2xl">
                {t('Do you want to merge the current cart and items from the previous order?')}
            </p>
            <div className="flex justify-between">
                <Button variant="secondary" onClick={() => mergeOrderItemsWithCurrentCart(orderForPrefillingUuid)}>
                    {t('No')}
                </Button>
                <Button onClick={() => mergeOrderItemsWithCurrentCart(orderForPrefillingUuid, true)}>{t('Yes')}</Button>
            </div>
        </Popup>
    );
};
