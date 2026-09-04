import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { useApplyCodeToCartMutation } from 'graphql/requests/cart/mutations/ApplyCodeToCartMutation.generated';
import { usePersistStore } from 'store/usePersistStore';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type ApplyCodeToCart = (code: string) => Promise<TypeCartFragment | undefined | null>;

export const useApplyCodeToCart = () => {
    const { t } = useTranslation();
    const [, applyCodeToCartMutation] = useApplyCodeToCartMutation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { giftVouchers } = useCurrentCart();

    const applyCodeToCart: ApplyCodeToCart = async (code) => {
        const applyCodeResult = await applyCodeToCartMutation({ input: { code, cartUuid } });

        if (applyCodeResult.error !== undefined) {
            return null;
        }

        const updatedCart = applyCodeResult.data?.ApplyCodeToCart;
        const appliedGiftVoucher = updatedCart?.giftVouchers.some((updatedGiftVoucher) =>
            giftVouchers.every((giftVoucher) => giftVoucher.code !== updatedGiftVoucher.code),
        );

        showSuccessMessage(
            appliedGiftVoucher ? t('Gift voucher was added to the order.') : t('Code was added to the order.'),
        );

        return updatedCart;
    };

    return { applyCodeToCart };
};
