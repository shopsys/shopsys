import {
    CartItemFragmentApi,
    LastOrderFragmentApi,
    ListedProductFragmentApi,
    ListedStoreFragmentApi,
    MainVariantDetailFragmentApi,
    ProductDetailFragmentApi,
    SimpleProductFragmentApi,
} from 'graphql/generated';
import { getFirstImageOrNull } from 'helpers/mappers/image';
import { getStringWithoutTrailingSlash } from 'helpers/parsing/stringWIthoutSlash';
import { GtmCartItemType, GtmListedProductType, GtmProductInterface, GtmShippingInfoType } from 'types/gtm/objects';
import { ProductInterfaceType } from 'types/product';

export const mapGtmCartItemType = (
    cartItem: CartItemFragmentApi,
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

export const mapGtmListedProductType = (
    product: ListedProductFragmentApi | SimpleProductFragmentApi,
    listIndex: number,
    domainUrl: string,
): GtmListedProductType => ({
    ...mapGtmProductInterface(product, domainUrl),
    listIndex: listIndex + 1,
});

export const mapGtmProductDetailType = (
    product: ProductDetailFragmentApi | MainVariantDetailFragmentApi,
    domainUrl: string,
): GtmProductInterface => mapGtmProductInterface(product, domainUrl);

const mapGtmProductInterface = (productInterface: ProductInterfaceType, domainUrl: string): GtmProductInterface => {
    let productUrl;

    if (domainUrl.endsWith('/')) {
        productUrl = getStringWithoutTrailingSlash(domainUrl) + productInterface.slug;
    } else {
        productUrl = domainUrl + productInterface.slug;
    }

    return {
        id: productInterface.id,
        name: productInterface.fullName,
        availability: productInterface.availability.name,
        imageUrl: mapGtmProductInterfaceImageUrl(productInterface),
        flags: productInterface.flags.map((simpleFlagType) => simpleFlagType.name),
        priceWithoutVat: parseFloat(productInterface.price.priceWithoutVat),
        priceWithVat: parseFloat(productInterface.price.priceWithVat),
        vatAmount: parseFloat(productInterface.price.vatAmount),
        sku: productInterface.catalogNumber,
        url: productUrl,
        brand: productInterface.brand?.name ?? '',
        categories: productInterface.categories.map((category) => category.name),
    };
};

const mapGtmProductInterfaceImageUrl = (productInterface: ProductInterfaceType): string | undefined => {
    if ('mainImage' in productInterface) {
        return productInterface.mainImage?.sizes.find((size) => size.size === 'default')?.url;
    }

    if ('images' in productInterface && Array.isArray(productInterface.images)) {
        return getFirstImageOrNull(productInterface.images)?.sizes.find((size) => size.size === 'default')?.url;
    }

    return undefined;
};

export const mapGtmShippingInfo = (pickupPlace: ListedStoreFragmentApi | null): GtmShippingInfoType => {
    let transportDetail = '';
    const transportExtra = [];

    if (pickupPlace !== null) {
        transportDetail = `${pickupPlace.name}, ${pickupPlace.street}, ${pickupPlace.city}, ${pickupPlace.country.name}, ${pickupPlace.postcode}`;

        if (pickupPlace.openingHoursHtml !== null) {
            transportExtra.push(pickupPlace.openingHoursHtml);
        }
    }

    return {
        transportDetail,
        transportExtra,
    };
};

export const getGtmPickupPlaceFromStore = (
    pickupPlaceIdentifier: string,
    store: ListedStoreFragmentApi,
): ListedStoreFragmentApi => ({
    __typename: 'Store',
    locationLatitude: null,
    locationLongitude: null,
    slug: '',
    identifier: pickupPlaceIdentifier,
    name: store.name,
    city: store.city,
    country: {
        __typename: 'Country',
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
): ListedStoreFragmentApi => ({
    __typename: 'Store',
    locationLatitude: null,
    locationLongitude: null,
    slug: '',
    identifier: pickupPlaceIdentifier,
    name: '',
    city: lastOrder.deliveryCity ?? '',
    country: {
        __typename: 'Country',
        name: lastOrder.deliveryCountry?.name ?? '',
        code: lastOrder.deliveryCountry?.code ?? '',
    },
    description: null,
    openingHoursHtml: null,
    postcode: lastOrder.deliveryPostcode ?? '',
    street: lastOrder.deliveryStreet ?? '',
});
