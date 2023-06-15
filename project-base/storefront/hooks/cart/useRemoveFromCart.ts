import { CartFragmentApi, CartItemFragmentApi, useRemoveFromCartMutationApi } from 'graphql/generated';
import { onGtmRemoveFromCartEventHandler } from 'helpers/gtm/eventHandlers';
import { mapPriceForCalculations } from 'helpers/mappers/price';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { usePersistStore } from 'store/zustand/usePersistStore';
import { GtmProductListNameType } from 'types/gtm/enums';

export type RemoveFromCartHandler = (
    cartItem: CartItemFragmentApi,
    listIndex: number,
) => Promise<CartFragmentApi | undefined | null>;

export const useRemoveFromCart = (gtmProductListName: GtmProductListNameType): [RemoveFromCartHandler, boolean] => {
    const [{ fetching }, removeItemFromCart] = useRemoveFromCartMutationApi();
    const { url, currencyCode } = useDomainConfig();
    const cartUuid = usePersistStore((s) => s.cartUuid);
    const updateUserState = usePersistStore((s) => s.updateUserState);

    const removeItemFromCartAction = async (cartItem: CartItemFragmentApi, listIndex: number) => {
        const removeItemFromCartActionResult = await removeItemFromCart({
            input: { cartUuid, cartItemUuid: cartItem.uuid },
        });

        if (removeItemFromCartActionResult.data?.RemoveFromCart.uuid !== undefined) {
            updateUserState({ cartUuid: removeItemFromCartActionResult.data.RemoveFromCart.uuid });

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
