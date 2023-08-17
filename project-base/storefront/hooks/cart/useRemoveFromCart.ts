import { onGtmRemoveFromCartEventHandler } from 'gtm/helpers/eventHandlers';
import { mapPriceForCalculations } from 'helpers/mappers/price';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { usePersistStore } from 'store/usePersistStore';
import { GtmProductListNameType } from 'gtm/types/enums';
import { CartFragmentApi } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { CartItemFragmentApi } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { useRemoveFromCartMutationApi } from 'graphql/requests/cart/mutations/RemoveFromCartMutation.generated';

export type RemoveFromCartHandler = (
    cartItem: CartItemFragmentApi,
    listIndex: number,
) => Promise<CartFragmentApi | undefined | null>;

export const useRemoveFromCart = (gtmProductListName: GtmProductListNameType): [RemoveFromCartHandler, boolean] => {
    const [{ fetching }, removeItemFromCart] = useRemoveFromCartMutationApi();
    const { url, currencyCode } = useDomainConfig();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const updateUserState = usePersistStore((store) => store.updateUserState);

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
