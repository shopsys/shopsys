import { Button } from 'components/Forms/Button/Button';
import { Popup } from 'components/Layout/Popup/Popup';
import { PopupActions } from 'components/Layout/Popup/PopupActions';
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
        <Popup size="small" title={t('Do you want to merge the current cart and items from the previous order?')}>
            <PopupActions>
                <Button
                    className="w-full sm:w-auto"
                    tid={TIDs.repeat_order_dont_merge_carts_button}
                    variant="outline"
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
                    className="w-full sm:w-auto"
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
            </PopupActions>
        </Popup>
    );
};
