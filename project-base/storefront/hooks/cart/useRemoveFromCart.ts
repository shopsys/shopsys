import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { CartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { CartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { useRemoveFromCartMutation } from 'graphql/requests/cart/mutations/RemoveFromCartMutation.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { onGtmRemoveFromCartEventHandler } from 'gtm/handlers/onGtmRemoveFromCartEventHandler';
import { mapPriceForCalculations } from 'helpers/mappers/price';
import { useCurrentCart } from 'hooks/cart/useCurrentCart';
import { dispatchBroadcastChannel } from 'hooks/useBroadcastChannel';
import { usePersistStore } from 'store/usePersistStore';

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

            const absoluteEventValueWithoutVat =
                mapPriceForCalculations(cartItem.product.price.priceWithoutVat) * cartItem.quantity;
            const absoluteEventValueWithVat =
                mapPriceForCalculations(cartItem.product.price.priceWithVat) * cartItem.quantity;

            onGtmRemoveFromCartEventHandler(
                cartItem,
                currencyCode,
                absoluteEventValueWithoutVat,
                absoluteEventValueWithVat,
                listIndex,
                gtmProductListName,
                url,
            );

            dispatchBroadcastChannel('refetchCart');
        }

        return removeItemFromCartActionResult.data?.RemoveFromCart ?? null;
    };

    return [removeItemFromCartAction, fetching];
};
