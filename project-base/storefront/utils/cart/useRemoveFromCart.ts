import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { useRemoveFromCartMutation } from 'graphql/requests/cart/mutations/RemoveFromCartMutation.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useRef } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';

export const useRemoveFromCart = (gtmProductListName: GtmProductListNameType) => {
    const [{ fetching: isRemovingFromCart }, removeItemFromCartMutation] = useRemoveFromCartMutation();
    const domainConfig = useDomainConfig();
    const { url, currencyCode } = domainConfig;
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { fetchCart } = useCurrentCart();
    const { canSeePrices } = useAuthorization();

    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const removingCartItemUuidsRef = useRef(new Set<string>());

    const removeFromCart = async (cartItem: TypeCartItemFragment, listIndex?: number) => {
        if (removingCartItemUuidsRef.current.has(cartItem.uuid)) {
            return null;
        }

        removingCartItemUuidsRef.current.add(cartItem.uuid);

        try {
            const removeItemFromCartActionResult = await removeItemFromCartMutation({
                input: { cartUuid, cartItemUuid: cartItem.uuid },
            });

            if (removeItemFromCartActionResult.error) {
                fetchCart({ requestPolicy: 'network-only' });
            }

            if (removeItemFromCartActionResult.data?.RemoveFromCart.uuid !== undefined) {
                updateCartUuid(removeItemFromCartActionResult.data.RemoveFromCart.uuid);

                import('gtm/handlers/onGtmRemoveFromCartEventHandler').then(({ onGtmRemoveFromCartEventHandler }) => {
                    onGtmRemoveFromCartEventHandler(
                        cartItem,
                        currencyCode,
                        listIndex,
                        gtmProductListName,
                        url,
                        !canSeePrices,
                    );
                });

                dispatchBroadcastChannel('refetchCart', domainConfig.domainId);
            }

            return removeItemFromCartActionResult.data?.RemoveFromCart ?? null;
        } finally {
            removingCartItemUuidsRef.current.delete(cartItem.uuid);
        }
    };

    return { removeFromCart, isRemovingFromCart };
};
