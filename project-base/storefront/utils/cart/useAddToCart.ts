'use client';

import { useAppConfig } from 'components/providers/AppConfigProvider';
import {
    TypeAddToCartMutation,
    useAddToCartMutation,
} from 'graphql/requests/cart/mutations/AddToCartMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { usePersistStore } from 'store/usePersistStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
// import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showInfoMessage } from 'utils/toasts/showInfoMessage';
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';

export type AddToCart = (
    productUuid: string,
    quantity: number,
    listIndex?: number,
    isAbsoluteQuantity?: boolean,
) => Promise<TypeAddToCartMutation['AddToCart'] | null>;

export const useAddToCart = (gtmMessageOrigin: GtmMessageOriginType, _gtmProductListName: GtmProductListNameType) => {
    const [{ fetching: isAddingToCart }, addToCartMutation] = useAddToCartMutation();
    const { domainId } = useAppConfig((appConfig) => appConfig.domainConfig);
    const { t } = useTranslation();
    // const { cart } = useCurrentCart();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);

    void _gtmProductListName;

    const addToCart: AddToCart = async (productUuid, quantity, listIndex, isAbsoluteQuantity = false) => {
        // const itemToBeAdded = cart?.items.find((item) => item.product.uuid === productUuid);
        // const initialQuantity = itemToBeAdded?.quantity ?? 0;
        const addToCartActionResult = await addToCartMutation({
            input: { cartUuid, productUuid, quantity, isAbsoluteQuantity },
        });

        if (!cartUuid) {
            updateCartUuid(addToCartActionResult.data?.AddToCart.cart.uuid ?? null);
        }

        // EXTEND ADDING TO CART HERE

        const addProductResult = addToCartActionResult.data?.AddToCart.addProductResult;

        if (addProductResult && addProductResult.notOnStockQuantity > 0) {
            const actualQuantity = addProductResult.cartItem.quantity;
            const requestedQuantity = isAbsoluteQuantity ? quantity : actualQuantity + quantity;

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
            showErrorMessage(t('Unable to add product to cart'), gtmMessageOrigin);

            return null;
        }

        const addToCartResult = addToCartActionResult.data?.AddToCart;

        if (!addToCartResult) {
            return null;
        }

        dispatchBroadcastChannel('refetchCart', domainId);

        // const addedCartItem = addToCartResult.addProductResult.cartItem;

        // import('gtm/handlers/onGtmChangeCartItemEventHandler').then(({ onGtmChangeCartItemEventHandler }) => {
        //     onGtmChangeCartItemEventHandler(
        //         initialQuantity,
        //         isAbsoluteQuantity,
        //         addToCartResult,
        //         addedCartItem,
        //         domainConfig,
        //         listIndex,
        //         gtmProductListName,
        //         isUserLoggedIn,
        //         !!currentCustomerData?.arePricesHidden,
        //     );
        // });

        return addToCartResult;
    };

    return { addToCart, isAddingToCart };
};
