import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { useRemoveCodeFromCartMutation } from 'graphql/requests/cart/mutations/RemoveCodeFromCartMutation.generated';
import { usePersistStore } from 'store/usePersistStore';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type RemoveCodeFromCart = (codeToBeRemoved: string) => Promise<TypeCartFragment | undefined | null>;

export const useRemoveCodeFromCart = () => {
    const { t } = useTranslation();
    const [{ fetching: isRemovingCodeFromCart }, removeCodeFromCartMutation] = useRemoveCodeFromCartMutation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { giftVouchers } = useCurrentCart();

    const removeCodeFromCart: RemoveCodeFromCart = async (codeToBeRemoved) => {
        const removeCodeResult = await removeCodeFromCartMutation({
            input: { code: codeToBeRemoved, cartUuid },
        });

        if (removeCodeResult.error !== undefined) {
            return null;
        }

        const removedGiftVoucher = giftVouchers.some((giftVoucher) => giftVoucher.code === codeToBeRemoved);

        showSuccessMessage(
            removedGiftVoucher ? t('Gift voucher was removed from the order.') : t('Code was removed from the order.'),
        );

        return removeCodeResult.data?.RemoveCodeFromCart;
    };

    return { removeCodeFromCart, isRemovingCodeFromCart };
};
