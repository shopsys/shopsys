import { useCurrentCart } from './useCurrentCart';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useAddOrderItemsToCartMutation } from 'graphql/requests/cart/mutations/AddOrderItemsToCartMutation.generated';
import { TypeAddOrderItemsToCartInput } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';

const NotAddedProductsPopup = dynamic(() =>
    import('components/Blocks/Popup/NotAddedProductsPopup').then((component) => component.NotAddedProductsPopup),
);
const MergeCartsPopup = dynamic(() =>
    import('components/Blocks/Popup/MergeCartsPopup').then((component) => component.MergeCartsPopup),
);

export const useAddOrderItemsToCart = () => {
    const { cart } = useCurrentCart();
    const router = useRouter();
    const { url } = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], url);
    const [, addOrderItemsToCart] = useAddOrderItemsToCartMutation();
    const { t } = useTranslation();
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const updatePortalContent = useSessionStore((store) => store.updatePortalContent);

    const handleAddingItemsToCart = async (input: TypeAddOrderItemsToCartInput) => {
        const response = await addOrderItemsToCart({ input });

        if (response.error) {
            showErrorMessage(t('Could not prefill your cart'));

            return;
        }

        const newCart = response.data?.AddOrderItemsToCart;
        updateCartUuid(newCart?.uuid ?? null);

        if (newCart) {
            const notAddedProducts = newCart.modifications.multipleAddedProductModifications.notAddedProducts;
            const addedAllProducts = notAddedProducts.length === 0;
            if (addedAllProducts) {
                router.push(cartUrl);
            } else {
                updatePortalContent(
                    <NotAddedProductsPopup
                        notAddedProductNames={notAddedProducts.map((product) => product.fullName)}
                    />,
                );
            }
        }
    };

    const addOrderItemsToEmptyCart = async (orderUuid: string) => {
        if (cart?.items.length) {
            updatePortalContent(
                <MergeCartsPopup
                    mergeOrderItemsWithCurrentCart={mergeOrderItemsWithCurrentCart}
                    orderForPrefillingUuid={orderUuid}
                />,
            );

            return;
        }
        handleAddingItemsToCart({ orderUuid, cartUuid: null, shouldMerge: false });
    };

    const mergeOrderItemsWithCurrentCart = async (orderUuid: string, shouldMerge = false) => {
        const cartUuid = shouldMerge && cart?.uuid ? cart.uuid : null;
        handleAddingItemsToCart({ orderUuid, cartUuid, shouldMerge });
    };

    return addOrderItemsToEmptyCart;
};
