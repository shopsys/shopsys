import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import {
    TypeAddToCartMutation,
    useAddToCartMutation,
} from 'graphql/requests/cart/mutations/AddToCartMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import { usePersistStore } from 'store/usePersistStore';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';

export type AddToCart = (
    productUuid: string,
    quantity: number,
    listIndex?: number,
    isAbsoluteQuantity?: boolean,
) => Promise<TypeAddToCartMutation['AddToCart'] | null>;

export const useAddToCart = (gtmMessageOrigin: GtmMessageOriginType, gtmProductListName: GtmProductListNameType) => {
    const [{ fetching: isAddingToCart }, addToCartMutation] = useAddToCartMutation();
    const { t } = useTranslation();
    const isUserLoggedIn = useIsUserLoggedIn();
    const { cart } = useCurrentCart();
    const domainConfig = useDomainConfig();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const { canSeePrices } = useAuthorization();

    const addToCart: AddToCart = async (productUuid, quantity, listIndex, isAbsoluteQuantity = false) => {
        const itemToBeAdded = cart?.items.find((item) => item.product.uuid === productUuid);
        const initialQuantity = itemToBeAdded?.quantity ?? 0;
        const addToCartActionResult = await addToCartMutation({
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
                !canSeePrices,
            );
        });

        return addToCartResult;
    };

    return { addToCart, isAddingToCart };
};
