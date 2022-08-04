import { LastOrderFragmentApi, ListedStoreFragmentApi } from 'graphql/generated';
import { CartItemType } from 'types/cart';
import { GtmCartItemType, GtmListedProductType, GtmProductInterface, GtmShippingInfoType } from 'types/gtm';
import { PickupPlaceType } from 'types/pickupPlace';
import {
    ListedProductType,
    MainVariantDetailType,
    ProductDetailType,
    ProductInterfaceType,
    SimpleProductType,
} from 'types/product';

export const mapGtmCartItemType = (
    cartItem: CartItemType,
    listIndex: number,
    domainUrl: string,
    quantity?: number,
): GtmCartItemType => ({
    ...mapGtmProductInterface(cartItem.product, domainUrl),
    listIndex: listIndex + 1,
    quantity: quantity ?? cartItem.quantity,
});

export const mapGtmListedProductType = (
    product: ListedProductType | SimpleProductType,
    listIndex: number,
    domainUrl: string,
): GtmListedProductType => ({
    ...mapGtmProductInterface(product, domainUrl),
    listIndex: listIndex + 1,
});

export const mapGtmProductDetailType = (
    product: ProductDetailType | MainVariantDetailType,
    domainUrl: string,
): GtmProductInterface => mapGtmProductInterface(product, domainUrl);

const mapGtmProductInterface = (productInterface: ProductInterfaceType, domainUrl: string): GtmProductInterface => {
    let productUrl;

    if (domainUrl.endsWith('/')) {
        const domainUrlWithoutTrailingSlash = domainUrl.slice(0, domainUrl.length - 1);
        productUrl = domainUrlWithoutTrailingSlash + productInterface.slug;
    } else {
        productUrl = domainUrl + productInterface.slug;
    }

    return {
        id: productInterface.id,
        name: productInterface.fullName,
        availability: productInterface.availability.name,
        imageUrl: mapGtmProductInterfaceImageUrl(productInterface),
        labels: productInterface.flags.map((simpleFlagType) => simpleFlagType.name),
        uuid: productInterface.uuid,
        price: productInterface.price.priceWithoutVat,
        priceWithTax: productInterface.price.priceWithVat,
        tax: productInterface.price.vatAmount,
        url: productUrl,
        sku: productInterface.catalogNumber,
        brand: productInterface.brand?.name ?? '',
        categories: productInterface.categoryNames,
    };
};

const mapGtmProductInterfaceImageUrl = (productInterface: ProductInterfaceType): string | undefined => {
    if ('image' in productInterface) {
        return productInterface.image?.sizes?.find((size) => size.size === 'default')?.url;
    }

    return productInterface.images.length > 0
        ? productInterface.images[0].sizes?.find((size) => size.size === 'default')?.url
        : undefined;
};

export const mapGtmShippingInfo = (pickupPlace: PickupPlaceType | null): GtmShippingInfoType => {
    let shippingDetail = '';
    const shippingExtra = [];

    if (pickupPlace !== null) {
        shippingDetail = `${pickupPlace.name}, ${pickupPlace.street}, ${pickupPlace.city}, ${pickupPlace.country.name}, ${pickupPlace.postcode}`;
        shippingExtra.push(pickupPlace.openingHoursHtml);
    }

    return {
        shippingDetail,
        shippingExtra,
    };
};

export const getGtmPickupPlaceFromStore = (
    pickupPlaceIdentifier: string,
    store: ListedStoreFragmentApi,
): PickupPlaceType => ({
    identifier: pickupPlaceIdentifier,
    name: store.name,
    city: store.city,
    country: {
        name: store.country.name,
        code: store.country.code,
    },
    description: store.description ?? '',
    openingHoursHtml: store.openingHoursHtml ?? '',
    postcode: store.postcode,
    street: store.street,
});

export const getGtmPickupPlaceFromLastOrder = (
    pickupPlaceIdentifier: string,
    lastOrder: LastOrderFragmentApi,
): PickupPlaceType => ({
    identifier: pickupPlaceIdentifier,
    name: '',
    city: lastOrder.deliveryCity ?? '',
    country: {
        name: lastOrder.deliveryCountry?.name ?? '',
        code: lastOrder.deliveryCountry?.code ?? '',
    },
    description: '',
    openingHoursHtml: '',
    postcode: lastOrder.deliveryPostcode ?? '',
    street: lastOrder.deliveryStreet ?? '',
});
