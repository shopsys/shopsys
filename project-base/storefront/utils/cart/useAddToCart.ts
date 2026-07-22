import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeCartItemWithGiftsFragment } from 'graphql/requests/cart/fragments/CartItemWithGiftsFragment.generated';
import {
    TypeAddToCartMutation,
    useAddToCartMutation,
} from 'graphql/requests/cart/mutations/AddToCartMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useEffect, useRef } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showInfoMessage } from 'utils/toasts/showInfoMessage';
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';

export type OnProductAddedToCart = (addedCartItem: TypeCartItemWithGiftsFragment) => Promise<TypeCartFragment | null>;

export type AddToCart = (
    productUuid: string,
    quantity: number,
    listIndex?: number,
    isAbsoluteQuantity?: boolean,
    onProductAddedToCart?: OnProductAddedToCart,
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
    const addingToCartProductUuidsRef = useRef(new Set<string>());
    const cartItemQuantitiesRef = useRef(new Map<string, number>());

    useEffect(() => {
        cartItemQuantitiesRef.current = new Map(
            cart?.items.map((item) => [item.product.uuid, item.quantity] as const) ?? [],
        );
    }, [cart]);

    const addToCart: AddToCart = async (
        productUuid,
        quantity,
        listIndex,
        isAbsoluteQuantity = false,
        onProductAddedToCart,
    ) => {
        if (addingToCartProductUuidsRef.current.has(productUuid)) {
            return null;
        }

        addingToCartProductUuidsRef.current.add(productUuid);

        try {
            const itemToBeAdded = cart?.items.find((item) => item.product.uuid === productUuid);
            const initialQuantity = cartItemQuantitiesRef.current.get(productUuid) ?? itemToBeAdded?.quantity ?? 0;
            const addToCartActionResult = await addToCartMutation({
                input: { cartUuid, productUuid, quantity, isAbsoluteQuantity },
            });

            if (!cartUuid) {
                updateCartUuid(addToCartActionResult.data?.AddToCart.cart.uuid ?? null);
            }

            const addProductResult = addToCartActionResult.data?.AddToCart.addProductResult;

            if (addProductResult && addProductResult.notOnStockQuantity > 0) {
                const actualQuantity = addProductResult.cartItem.quantity;
                const requestedQuantity = isAbsoluteQuantity ? quantity : initialQuantity + quantity;

                if (actualQuantity < requestedQuantity) {
                    showInfoMessage(
                        t('Product quantity was adjusted to available stock ({{ quantity }})', {
                            quantity: actualQuantity,
                        }),
                        gtmMessageOrigin,
                    );
                }
            }

            if (addToCartActionResult.error) {
                return null;
            }

            const addToCartResult = addToCartActionResult.data?.AddToCart;

            if (!addToCartResult) {
                return null;
            }

            dispatchBroadcastChannel('refetchCart', domainConfig.domainId);

            const addedCartItem = addToCartResult.addProductResult.cartItem;
            cartItemQuantitiesRef.current.set(productUuid, addedCartItem.quantity);

            const cartWithAdditionalServices = (await onProductAddedToCart?.(addedCartItem)) ?? null;
            const updatedCart = cartWithAdditionalServices ?? addToCartResult.cart;
            const updatedCartItem =
                cartWithAdditionalServices?.items.find((cartItem) => cartItem.uuid === addedCartItem.uuid) ??
                addedCartItem;

            import('gtm/handlers/onGtmChangeCartItemEventHandler').then(({ onGtmChangeCartItemEventHandler }) => {
                onGtmChangeCartItemEventHandler(
                    initialQuantity,
                    isAbsoluteQuantity,
                    addToCartResult,
                    updatedCartItem,
                    updatedCart,
                    domainConfig,
                    listIndex,
                    gtmProductListName,
                    isUserLoggedIn,
                    !canSeePrices,
                );
            });

            return addToCartResult;
        } finally {
            addingToCartProductUuidsRef.current.delete(productUuid);
        }
    };

    return { addToCart, isAddingToCart };
};
