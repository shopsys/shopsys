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
import { GtmCartItemType, GtmListedProductFragmentApi, GtmProductInterface, GtmShippingInfoType } from 'types/gtm';
import { ProductInterfaceType } from 'types/product';

export const mapGtmCartItemType = (
    cartItem: CartItemFragmentApi,
    domainUrl: string,
    listIndex?: number,
    quantity?: number,
): GtmCartItemType => {
    const gtmCartItem = {
        ...mapGtmProductInterface(cartItem.product, domainUrl),
        quantity: quantity ?? cartItem.quantity,
    };

    if (listIndex !== undefined) {
        gtmCartItem.listIndex = listIndex + 1;
    }

    return gtmCartItem;
};

export const mapGtmListedProductFragmentApi = (
    product: ListedProductFragmentApi | SimpleProductFragmentApi,
    listIndex: number,
    domainUrl: string,
): GtmListedProductFragmentApi => ({
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
        price: parseFloat(productInterface.price.priceWithoutVat),
        priceWithTax: parseFloat(productInterface.price.priceWithVat),
        tax: parseFloat(productInterface.price.vatAmount),
        url: productUrl,
        sku: productInterface.catalogNumber,
        brand: productInterface.brand?.name ?? '',
        categories: productInterface.categories.map((category) => category.name),
    };
};

const mapGtmProductInterfaceImageUrl = (productInterface: ProductInterfaceType): string | undefined => {
    if ('image' in productInterface) {
        return productInterface.image?.sizes.find((size) => size.size === 'default')?.url;
    }

    if ('images' in productInterface && Array.isArray(productInterface.images)) {
        return getFirstImageOrNull(productInterface.images)?.sizes.find((size) => size.size === 'default')?.url;
    }

    return undefined;
};

export const mapGtmShippingInfo = (pickupPlace: ListedStoreFragmentApi | null): GtmShippingInfoType => {
    let shippingDetail = '';
    const shippingExtra = [];

    if (pickupPlace !== null) {
        shippingDetail = `${pickupPlace.name}, ${pickupPlace.street}, ${pickupPlace.city}, ${pickupPlace.country.name}, ${pickupPlace.postcode}`;

        if (pickupPlace.openingHoursHtml !== null) {
            shippingExtra.push(pickupPlace.openingHoursHtml);
        }
    }

    return {
        shippingDetail,
        shippingExtra,
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
