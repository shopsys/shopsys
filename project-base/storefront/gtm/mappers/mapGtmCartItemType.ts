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

    return mappedCartItem;
};
