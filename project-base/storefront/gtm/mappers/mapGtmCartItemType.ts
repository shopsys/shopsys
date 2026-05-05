import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { GtmCartItemType } from 'gtm/types/objects';
import { mapGtmProductInterface } from './mapGtmProductInterface';

export const mapGtmCartItemType = (
    cartItem: TypeCartItemFragment,
    domainUrl: string,
    listIndex?: number,
    quantity?: number,
): GtmCartItemType => {
    const mappedCartItem: GtmCartItemType = {
        ...mapGtmProductInterface(cartItem.product, domainUrl),
        quantity: quantity ?? cartItem.quantity,
    };

    if (listIndex !== undefined) {
        mappedCartItem.listIndex = listIndex + 1;
    }

    const variant = mapGtmCartItemVariant(cartItem.product);

    if (variant !== undefined) {
        mappedCartItem.variant = variant;
    }

    return mappedCartItem;
};

const mapGtmCartItemVariant = (product: TypeCartItemFragment['product']): string | undefined => {
    if (product.__typename !== 'Variant' || product.parameters.length === 0) {
        return undefined;
    }

    const variant = product.parameters
        .filter((parameter) => parameter.values.length > 0)
        .map((parameter) => `${parameter.name}: ${mapParameterValues(parameter)}`)
        .join('; ');

    return variant || undefined;
};

const mapParameterValues = (parameter: TypeCartItemFragment['product']['parameters'][number]): string => {
    const unitSuffix = parameter.unit?.name ? ` ${parameter.unit.name}` : '';

    return parameter.values.map((value) => `${value.text}${unitSuffix}`).join(', ');
};
