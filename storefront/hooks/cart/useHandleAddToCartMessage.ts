import { AddToCartMutationApi, AddToCartMutationVariablesApi } from 'graphql/generated';
import { Dispatch, SetStateAction, useEffect, useState } from 'react';
import { AddToCartPopupDataType } from 'types/cart';
import { getFirstImageSize } from 'connectors/image/Image';
import { mapProductPriceData } from 'connectors/price/Prices';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { UseMutationState } from 'urql';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const useHandleAddToCartMessage = (
    result: UseMutationState<AddToCartMutationApi, AddToCartMutationVariablesApi>,
    productUuid: string,
): [AddToCartPopupDataType | null, Dispatch<SetStateAction<AddToCartPopupDataType | null>>] => {
    const [popupData, setPopupData] = useState<AddToCartPopupDataType | null>(null);
    const t = useTypedTranslationFunction();
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    useEffect(() => {
        if (result.error !== undefined) {
            showErrorMessage(t('Unable to add product to cart'));
            setPopupData(null);
            return;
        }

        if (result.data === undefined) {
            setPopupData(null);
            return;
        }

        const cartItem = result.data.AddToCart.items.find((cartItem) => cartItem.product.uuid === productUuid);

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
            );
        } else {
            const mappedPopupData = {
                name: cartItem.product.fullName,
                slug: cartItem.product.slug,
                image: getFirstImageSize(cartItem.product.images),
                quantity: result.data.AddToCart.addProductResult.addedQuantity,
                unitName: cartItem.product.unit.name,
                price: mapProductPriceData(cartItem.product.price, currencyCode),
            };

            setPopupData(mappedPopupData);
        }
    }, [result.fetching]);

    return [popupData, setPopupData];
};
