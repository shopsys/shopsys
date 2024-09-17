import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { useRemoveFromCartMutation } from 'graphql/requests/cart/mutations/RemoveFromCartMutation.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { usePersistStore } from 'store/usePersistStore';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';

export type RemoveFromCart = (
    cartItem: TypeCartItemFragment,
    listIndex: number,
) => Promise<TypeCartFragment | undefined | null>;

export const useRemoveFromCart = (gtmProductListName: GtmProductListNameType) => {
    const [{ fetching: isRemovingFromCart }, removeItemFromCartMutation] = useRemoveFromCartMutation();
    const { url, currencyCode } = useDomainConfig();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { fetchCart } = useCurrentCart();
    const currentCustomerData = useCurrentCustomerData();

    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);

    const removeFromCart = async (cartItem: TypeCartItemFragment, listIndex: number) => {
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
                    !!currentCustomerData?.arePricesHidden,
                );
            });

            dispatchBroadcastChannel('refetchCart');
        }

        return removeItemFromCartActionResult.data?.RemoveFromCart ?? null;
    };

    return { removeFromCart, isRemovingFromCart };
};
