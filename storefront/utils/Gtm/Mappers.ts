import { GtmCartItemType, GtmListedProductType, GtmProductInterface } from 'types/gtm';
import { ListedProductType, ProductInterfaceType, SimpleProductType } from 'types/product';
import { CartItemType } from 'types/cart';

export const mapGtmCartItemType = (cartItem: CartItemType, quantity?: number): GtmCartItemType => ({
    ...mapGtmProductInterface(cartItem.product),
    quantity: quantity ?? cartItem.quantity,
});

export const mapGtmListedProductType = (
    product: ListedProductType | SimpleProductType,
    listIndex: number,
): GtmListedProductType => mapGtmProductInterface(product, listIndex);

const mapGtmProductInterface = (productInterface: ProductInterfaceType): GtmProductInterface => ({
    id: productInterface.uuid,
    name: productInterface.fullName,
    availability: productInterface.availability.name,
    imageUrl: mapGtmProductInterfaceImageUrl(productInterface),
    labels: productInterface.flags.map((simpleFlagType) => simpleFlagType.name),
    uuid: productInterface.uuid,
    price: productInterface.price.priceWithoutVat,
    priceWithTax: productInterface.price.priceWithVat,
    tax: productInterface.price.vatAmount,
    url: productInterface.slug,
    sku: productInterface.catalogNumber,
    brand: productInterface.brand?.name ?? '',
    categories: productInterface.categoryNames,
});

const mapGtmProductInterfaceImageUrl = (productInterface: ProductInterfaceType): string | undefined => {
    if ('image' in productInterface) {
        return productInterface.image?.sizes?.find((size) => size.size === 'default')?.url;
    }

    return productInterface.images.length > 0
        ? productInterface.images[0].sizes?.find((size) => size.size === 'default')?.url
        : undefined;
};
