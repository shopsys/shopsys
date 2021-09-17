import { showErrorMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import { AddToCartResultType } from 'connectors/cart/types';
import { CombinedError } from '@urql/core';
import { TFunction } from 'react-i18next';

export const useHandleChangeCartItemQuantity = (
    data: { AddToCart: AddToCartResultType },
    error: CombinedError | undefined,
    productUuid: string,
    productName: string,
    t: TFunction<string>,
): void => {
    if (error !== undefined) {
        showErrorMessage(t('Unable to add product to cart'));
        return;
    }

    const cartItem = data.AddToCart.items.find((cartItem) => cartItem.product.uuid === productUuid)!;

    if (data.AddToCart.addProductResult.isNew) {
        showSuccessMessage(
            t('Product {{ name }} ({{ quantity }} {{ unitName }}) added to the cart', {
                name: productName,
                quantity: data.AddToCart.addProductResult.addedQuantity,
                unitName: cartItem.product.unit.name,
            }),
        );
    } else if (data.AddToCart.addProductResult.notOnStockQuantity > 0) {
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
        showSuccessMessage(
            t('Product {{ name }} added to the cart (total amount {{ quantity }} {{ unitName }})', {
                name: productName,
                quantity: cartItem.quantity,
                unitName: cartItem.product.unit.name,
            }),
        );
    }
};
