import { AddToCartMutationApi, AddToCartMutationVariablesApi } from 'graphql/generated';
import { showErrorMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import { TFunction } from 'react-i18next';
import { UseMutationState } from 'urql';

export const showChangeCartItemQuantityMessages = (
    result: UseMutationState<AddToCartMutationApi, AddToCartMutationVariablesApi>,
    productUuid: string,
    productName: string,
    t: TFunction<string>,
): void => {
    if (result.error !== undefined) {
        showErrorMessage(t('Unable to add product to cart'));
        return;
    }

    if (result.data === undefined) {
        return;
    }

    const cartItem = result.data.AddToCart.items.find((cartItem) => cartItem.product.uuid === productUuid)!;

    if (result.data.AddToCart.addProductResult.isNew) {
        showSuccessMessage(
            t('Product {{ name }} ({{ quantity }} {{ unitName }}) added to the cart', {
                name: productName,
                quantity: result.data.AddToCart.addProductResult.addedQuantity,
                unitName: cartItem.product.unit.name,
            }),
        );
    } else if (result.data.AddToCart.addProductResult.notOnStockQuantity > 0) {
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
