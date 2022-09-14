import { showErrorMessage } from 'components/Helpers/Toasts';
import { mapSimpleProductApiData } from 'connectors/products/SimpleProduct';
import { AddToCartMutationApi, AddToCartMutationVariablesApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { Dispatch, SetStateAction, useEffect, useState } from 'react';
import { useShopsysSelector } from 'redux/main';
import { AddToCartPopupDataType } from 'types/cart';
import { GtmMessageOriginType } from 'types/gtm';
import { UseMutationState } from 'urql';

// TODO:
export const useHandleAddToCartMessage = (
    result: UseMutationState<AddToCartMutationApi, AddToCartMutationVariablesApi>,
    productUuid: string,
    origin: GtmMessageOriginType,
): [AddToCartPopupDataType | null, Dispatch<SetStateAction<AddToCartPopupDataType | null>>] => {
    const [popupData, setPopupData] = useState<AddToCartPopupDataType | null>(null);
    const t = useTypedTranslationFunction();
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    useEffect(() => {
        if (result.error !== undefined) {
            showErrorMessage(t('Unable to add product to cart'), origin);
            setPopupData(null);
            return;
        }

        if (result.data === undefined) {
            setPopupData(null);
            return;
        }

        const cartItem = result.data.AddToCart.cart.items.find((cartItem) => cartItem.product.uuid === productUuid);

        if (cartItem === undefined) {
            return;
        }

        if (result.data.AddToCart.addProductResult.notOnStockQuantity > 0) {
            showErrorMessage(
                t(
                    'You have the maximum available amount in your cart, you cannot add more (total {{ quantity }} {{ unitName }})',
                    {
                        quantity: cartItem.quantity,
                        unitName: cartItem.product.unit.name,
                    },
                ),
                origin,
            );
        } else {
            const mappedPopupData = {
                ...mapSimpleProductApiData(cartItem.product, currencyCode),
                quantity: result.data.AddToCart.addProductResult.addedQuantity,
            };

            setPopupData(mappedPopupData);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [result.fetching]);

    return [popupData, setPopupData];
};
