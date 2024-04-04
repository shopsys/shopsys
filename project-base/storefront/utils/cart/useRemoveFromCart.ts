import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { CartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { CartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { useRemoveFromCartMutation } from 'graphql/requests/cart/mutations/RemoveFromCartMutation.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { usePersistStore } from 'store/usePersistStore';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';

export type RemoveFromCartHandler = (
    cartItem: CartItemFragment,
    listIndex: number,
) => Promise<CartFragment | undefined | null>;

export const useRemoveFromCart = (gtmProductListName: GtmProductListNameType): [RemoveFromCartHandler, boolean] => {
    const [{ fetching }, removeItemFromCart] = useRemoveFromCartMutation();
    const { url, currencyCode } = useDomainConfig();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { fetchCart } = useCurrentCart();

    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);

    const removeItemFromCartAction = async (cartItem: CartItemFragment, listIndex: number) => {
        const removeItemFromCartActionResult = await removeItemFromCart({
            input: { cartUuid, cartItemUuid: cartItem.uuid },
        });

        if (removeItemFromCartActionResult.error) {
            fetchCart({ requestPolicy: 'network-only' });
        }

        if (removeItemFromCartActionResult.data?.RemoveFromCart.uuid !== undefined) {
            updateCartUuid(removeItemFromCartActionResult.data.RemoveFromCart.uuid);

            import('gtm/handlers/onGtmRemoveFromCartEventHandler').then(({ onGtmRemoveFromCartEventHandler }) => {
                onGtmRemoveFromCartEventHandler(cartItem, currencyCode, listIndex, gtmProductListName, url);
            });

            dispatchBroadcastChannel('refetchCart');
        }

        return removeItemFromCartActionResult.data?.RemoveFromCart ?? null;
    };

    return [removeItemFromCartAction, fetching];
};
