import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { useRef } from 'react';

type MergeCartsPopupProps = {
    mergeOrderItemsWithCurrentCart: (orderUuid: string, shouldMerge?: boolean | undefined) => void;
    orderForPrefillingUuid: string;
};

export const MergeCartsPopup: FC<MergeCartsPopupProps> = ({
    mergeOrderItemsWithCurrentCart,
    orderForPrefillingUuid,
}) => {
    const { t } = useTranslation();
    const orderForPrefillingUuidRef = useRef(orderForPrefillingUuid);

    return (
        <Popup>
            <p className="mb-6 text-lg lg:text-2xl">
                {t('Do you want to merge the current cart and items from the previous order?')}
            </p>
            <div className="flex justify-between">
                <Button
                    tid={TIDs.repeat_order_dont_merge_carts_button}
                    variant="inverted"
                    onClick={() => mergeOrderItemsWithCurrentCart(orderForPrefillingUuidRef.current)}
                >
                    {t('No')}
                </Button>
                <Button
                    tid={TIDs.repeat_order_merge_carts_button}
                    onClick={() => mergeOrderItemsWithCurrentCart(orderForPrefillingUuidRef.current, true)}
                >
                    {t('Yes')}
                </Button>
            </div>
        </Popup>
    );
};
