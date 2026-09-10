import { TypeAdditionalServiceFragment } from 'graphql/requests/additionalServices/fragments/AdditionalServiceFragment.generated';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { GtmServiceCartItemType } from 'gtm/types/objects';
import { getGtmPriceBasedOnVisibility } from 'gtm/utils/getGtmPriceBasedOnVisibility';

export const mapGtmServiceCartItem = (
    additionalService: TypeAdditionalServiceFragment,
    sourceProductIds: number[],
    quantity: number,
): GtmServiceCartItemType => ({
    id: additionalService.id,
    sku: additionalService.catnum,
    productType: 'service',
    name: additionalService.name,
    sourceProductIds,
    priceWithoutVat: getGtmPriceBasedOnVisibility(additionalService.price.priceWithoutVat),
    priceWithVat: getGtmPriceBasedOnVisibility(additionalService.price.priceWithVat),
    quantity,
});

export const mapGtmServiceCartItems = (cartItems: TypeCartItemFragment[]): GtmServiceCartItemType[] => {
    const servicesByUuid = new Map<string, GtmServiceCartItemType>();

    for (const cartItem of cartItems) {
        for (const additionalService of cartItem.additionalServices) {
            const alreadyMappedService = servicesByUuid.get(additionalService.uuid);

            if (alreadyMappedService) {
                alreadyMappedService.quantity += cartItem.quantity;
                alreadyMappedService.sourceProductIds.push(cartItem.product.id);

                continue;
            }

            servicesByUuid.set(
                additionalService.uuid,
                mapGtmServiceCartItem(additionalService, [cartItem.product.id], cartItem.quantity),
            );
        }
    }

    return Array.from(servicesByUuid.values());
};
