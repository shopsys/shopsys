'use client';

import { addToCartAction } from 'app/_actions/addToCartAction';
import { dispatchBroadcastChannel } from 'app/_hooks/useBroadcastChannel';
import { useTranslation } from 'components/providers/TranslationProvider';
import { TypeAddToCartMutation } from 'graphql/requests/cart/mutations/AddToCartMutation.ssr';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';

export type AddToCart = (
    productUuid: string,
    quantity: number,
    listIndex?: number,
    isAbsoluteQuantity?: boolean,
) => Promise<TypeAddToCartMutation['AddToCart'] | null>;

export const useAddToCart = (gtmMessageOrigin: GtmMessageOriginType, gtmProductListName: GtmProductListNameType) => {
    const { t } = useTranslation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);

    const [isAddingToCart, setIsAddingToCart] = useState(false);

    // const domainConfig = useDomainConfig();
    // const currentCustomerData = useCurrentCustomerData();
    // const { cart } = useCurrentCart();

    const addToCart: AddToCart = async (productUuid, quantity, listIndex, isAbsoluteQuantity = false) => {
        // const itemToBeAdded = cart?.items.find((item) => item.product.uuid === productUuid);
        // const initialQuantity = itemToBeAdded?.quantity ?? 0;

        setIsAddingToCart(true);

        const addToCartActionResult = await addToCartAction({
            input: { cartUuid, productUuid, quantity, isAbsoluteQuantity },
        });

        if (!cartUuid) {
            updateCartUuid(addToCartActionResult.data?.AddToCart.cart.uuid ?? null);
        }

        setIsAddingToCart(false);

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

        // TODO: GTM
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
        //         !!currentCustomerData,
        //         !canSeePrices,
        //     );
        // });

        return addToCartResult;
    };

    return { addToCart, isAddingToCart };
};
