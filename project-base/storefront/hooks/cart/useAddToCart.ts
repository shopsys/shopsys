import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { AddToCartMutation, useAddToCartMutation } from 'graphql/requests/cart/mutations/AddToCartMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { showErrorMessage } from 'helpers/toasts/showErrorMessage';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import { useCurrentCart } from 'hooks/cart/useCurrentCart';
import { dispatchBroadcastChannel } from 'hooks/useBroadcastChannel';
import useTranslation from 'next-translate/useTranslation';
import { usePersistStore } from 'store/usePersistStore';

export type AddToCartAction = (
    productUuid: string,
    quantity: number,
    listIndex?: number,
    isAbsoluteQuantity?: boolean,
) => Promise<AddToCartMutation['AddToCart'] | null>;

export const useAddToCart = (
    gtmMessageOrigin: GtmMessageOriginType,
    gtmProductListName: GtmProductListNameType,
): [AddToCartAction, boolean] => {
    const [{ fetching }, addToCart] = useAddToCartMutation();
    const { t } = useTranslation();
    const isUserLoggedIn = useIsUserLoggedIn();
    const { cart } = useCurrentCart();
    const domainConfig = useDomainConfig();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);

    const addToCartAction: AddToCartAction = async (productUuid, quantity, listIndex, isAbsoluteQuantity = false) => {
        const itemToBeAdded = cart?.items.find((item) => item.product.uuid === productUuid);
        const initialQuantity = itemToBeAdded?.quantity ?? 0;
        const addToCartActionResult = await addToCart({
            input: { cartUuid, productUuid, quantity, isAbsoluteQuantity },
        });

        if (!cartUuid) {
            updateCartUuid(addToCartActionResult.data?.AddToCart.cart.uuid ?? null);
        }

        // EXTEND ADDING TO CART HERE

        if (addToCartActionResult.error) {
            showErrorMessage(t('Unable to add product to cart'), gtmMessageOrigin);

            return null;
        }

        const addToCartResult = addToCartActionResult.data?.AddToCart;

        if (!addToCartResult) {
            return null;
        }

        dispatchBroadcastChannel('refetchCart');

        const addedCartItem = addToCartResult.addProductResult.cartItem;
        const notOnStockQuantity = addToCartResult.addProductResult.notOnStockQuantity;

        if (notOnStockQuantity > 0) {
            showErrorMessage(
                t(
                    'You have the maximum available amount in your cart, you cannot add more (total {{ quantity }} {{ unitName }})',
                    {
                        quantity: addedCartItem.quantity,
                        unitName: addedCartItem.product.unit.name,
                    },
                ),
                gtmMessageOrigin,
            );
        }

        import('gtm/handlers/onGtmChangeCartItemEventHandler').then(({ onGtmChangeCartItemEventHandler }) => {
            onGtmChangeCartItemEventHandler(
                initialQuantity,
                isAbsoluteQuantity,
                addToCartResult,
                addedCartItem,
                domainConfig,
                listIndex,
                gtmProductListName,
                isUserLoggedIn,
            );
        });

        return addToCartResult;
    };

    return [addToCartAction, fetching];
};
