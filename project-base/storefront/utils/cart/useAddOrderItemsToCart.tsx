import { useCurrentCart } from './useCurrentCart';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useAddOrderItemsToCartMutation } from 'graphql/requests/cart/mutations/AddOrderItemsToCartMutation.generated';
import { TypeAddOrderItemsToCartInput } from 'graphql/types';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { SkeletonEnum } from 'types/skeletons';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';

const NotAddedProductsPopup = dynamic(() =>
    import('components/Blocks/Popup/NotAddedProductsPopup').then((component) => component.NotAddedProductsPopup),
);
const MergeCartsPopup = dynamic(() =>
    import('components/Blocks/Popup/MergeCartsPopup').then((component) => component.MergeCartsPopup),
);

export const useAddOrderItemsToCart = () => {
    const { cart } = useCurrentCart();
    const router = useRouter();
    const domainConfig = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], domainConfig.url);
    const [, addOrderItemsToCart] = useAddOrderItemsToCartMutation();
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const updatePortalContent = useSessionStore((store) => store.updatePortalContent);
    const updatePageLoadingState = useSessionStore((store) => store.updatePageLoadingState);

    const handleAddingItemsToCart = async (input: TypeAddOrderItemsToCartInput) => {
        const addOrderItemsToCartResponse = await addOrderItemsToCart({ input });

        if (addOrderItemsToCartResponse.error) {
            return;
        }

        const newCart = addOrderItemsToCartResponse.data?.AddOrderItemsToCart;
        updateCartUuid(newCart?.uuid ?? null);

        dispatchBroadcastChannel('refetchCart', domainConfig.domainId);

        if (newCart) {
            const notAddedProducts = newCart.modifications.multipleAddedProductModifications.notAddedProducts;
            const addedAllProducts = notAddedProducts.length === 0;
            if (addedAllProducts) {
                updatePageLoadingState({ isPageLoading: true, redirectPageType: SkeletonEnum.Cart });
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
