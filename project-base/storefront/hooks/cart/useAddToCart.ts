import { showErrorMessage } from 'helpers/toasts';
import { useCurrentCart } from 'connectors/cart/Cart';
import { AddToCartMutationApi, useAddToCartMutationApi } from 'graphql/generated';
import { onGtmChangeCartItemEventHandler } from 'gtm/helpers/eventHandlers';
import { getGtmMappedCart } from 'gtm/helpers/gtm';
import { mapPriceForCalculations } from 'helpers/mappers/price';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { usePersistStore } from 'store/usePersistStore';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';

export type AddToCartAction = (
    productUuid: string,
    quantity: number,
    listIndex?: number,
    isAbsoluteQuantity?: boolean,
) => Promise<AddToCartMutationApi['AddToCart'] | null>;

export const useAddToCart = (
    gtmMessageOrigin: GtmMessageOriginType,
    gtmProductListName: GtmProductListNameType,
): [AddToCartAction, boolean] => {
    const [{ fetching }, addToCart] = useAddToCartMutationApi();
    const t = useTypedTranslationFunction();
    const isUserLoggedIn = !!useCurrentCustomerData();
    const { cart } = useCurrentCart();
    const domainConfig = useDomainConfig();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const updateUserState = usePersistStore((store) => store.updateUserState);

    const addToCartAction: AddToCartAction = async (productUuid, quantity, listIndex, isAbsoluteQuantity = false) => {
        const itemToBeAdded = cart?.items.find((item) => item.product.uuid === productUuid);
        const initialQuantity = itemToBeAdded?.quantity ?? 0;
        const addToCartActionResult = await addToCart({
            input: { cartUuid, productUuid, quantity, isAbsoluteQuantity },
        });

        if (!cartUuid) {
            updateUserState({ cartUuid: addToCartActionResult.data?.AddToCart.cart.uuid ?? null });
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

        const quantityDifference = isAbsoluteQuantity
            ? addToCartResult.addProductResult.addedQuantity - initialQuantity
            : addToCartResult.addProductResult.addedQuantity;
        const absoluteEventValueWithoutVat =
            mapPriceForCalculations(addedCartItem.product.price.priceWithoutVat) * Math.abs(quantityDifference);
        const absoluteEventValueWithVat =
            mapPriceForCalculations(addedCartItem.product.price.priceWithVat) * Math.abs(quantityDifference);

        onGtmChangeCartItemEventHandler(
            addedCartItem,
            domainConfig.currencyCode,
            absoluteEventValueWithoutVat,
            absoluteEventValueWithVat,
            listIndex,
            quantityDifference,
            gtmProductListName,
            domainConfig.url,
            getGtmMappedCart(
                addToCartResult.cart,
                addToCartResult.cart.promoCode,
                isUserLoggedIn,
                domainConfig,
                cartUuid,
            ),
        );

        return addToCartResult;
    };

    return [addToCartAction, fetching];
};
