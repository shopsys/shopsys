import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { TIDs } from 'cypress/tids';
import { useRef } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type MergeCartsPopupProps = {
    mergeOrderItemsWithCurrentCart: (
        orderUuid: string,
        orderUrlHash: string | null,
        shouldMerge?: boolean | undefined,
    ) => void;
    orderForPrefillingUuid: string;
    orderForPrefillingUrlHash: string | null;
};

export const MergeCartsPopup: FC<MergeCartsPopupProps> = ({
    mergeOrderItemsWithCurrentCart,
    orderForPrefillingUuid,
    orderForPrefillingUrlHash,
}) => {
    const { t } = useTranslation();
    const orderForPrefillingUuidRef = useRef(orderForPrefillingUuid);
    const orderForPrefillingUrlHashRef = useRef(orderForPrefillingUrlHash);

    return (
        <Popup title={t('Do you want to merge the current cart and items from the previous order?')}>
            <div className="flex justify-between">
                <Button
                    tid={TIDs.repeat_order_dont_merge_carts_button}
                    variant="secondary"
                    onClick={() =>
                        mergeOrderItemsWithCurrentCart(
                            orderForPrefillingUuidRef.current,
                            orderForPrefillingUrlHashRef.current,
                        )
                    }
                >
                    {t('No')}
                </Button>
                <Button
                    tid={TIDs.repeat_order_merge_carts_button}
                    onClick={() =>
                        mergeOrderItemsWithCurrentCart(
                            orderForPrefillingUuidRef.current,
                            orderForPrefillingUrlHashRef.current,
                            true,
                        )
                    }
                >
                    {t('Yes')}
                </Button>
            </div>
        </Popup>
    );
};
