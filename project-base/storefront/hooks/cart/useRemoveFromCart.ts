import { useCurrentCart } from 'connectors/cart/Cart';
import { CartFragmentApi, CartItemFragmentApi, useRemoveFromCartMutationApi } from 'graphql/generated';
import { onGtmRemoveFromCartEventHandler } from 'gtm/helpers/eventHandlers';
import { GtmProductListNameType } from 'gtm/types/enums';
import { mapPriceForCalculations } from 'helpers/mappers/price';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { usePersistStore } from 'store/usePersistStore';

export type RemoveFromCartHandler = (
    cartItem: CartItemFragmentApi,
    listIndex: number,
) => Promise<CartFragmentApi | undefined | null>;

export const useRemoveFromCart = (gtmProductListName: GtmProductListNameType): [RemoveFromCartHandler, boolean] => {
    const [{ fetching }, removeItemFromCart] = useRemoveFromCartMutationApi();
    const { url, currencyCode } = useDomainConfig();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { refetchCart } = useCurrentCart();

    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);

    const removeItemFromCartAction = async (cartItem: CartItemFragmentApi, listIndex: number) => {
        const removeItemFromCartActionResult = await removeItemFromCart({
            input: { cartUuid, cartItemUuid: cartItem.uuid },
        });

        if (removeItemFromCartActionResult.error) {
            refetchCart({ requestPolicy: 'network-only' });
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
        }

        return removeItemFromCartActionResult.data?.RemoveFromCart ?? null;
    };

    return [removeItemFromCartAction, fetching];
};
